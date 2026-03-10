<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Contrato;
use App\Models\Persona;
use App\Models\CuentaFinanciera; // Importado para Cuentas Destino
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class PagoController extends Controller
{
    public function index(Request $request)
    {
        // Cargamos pagos con sus relaciones, incluyendo la cuenta destino del trabajador
        $pagos = Pago::with(['persona', 'contrato', 'prima', 'cuentaFinanciera.banco'])
            ->orderBy('fecha_operacion', 'desc')
            ->paginate(10);

        $personas = Persona::all(); 
        
        // Obtenemos todas las cuentas bancarias registradas de los trabajadores
        $cuentas = CuentaFinanciera::with(['banco', 'persona'])->get();

        return view('pagos.index', compact('pagos', 'personas', 'cuentas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'persona_id' => 'required|exists:personas,id',
            'contrato_id' => 'required|exists:contratos,id',
            'tipo_pago' => 'required|in:Sueldo,Anticipo Sueldo,Prima,Anticipo Prima',
            'monto_pagado' => 'required|numeric|min:0.01',
            'moneda_pago' => 'required|string|max:5',
            'tipo_cambio' => 'required|numeric|min:0.000001',
            'fecha_operacion' => 'required|date',
            'prima_id' => 'nullable|exists:primas,id',
            'cuenta_financiera_id' => 'nullable|exists:cuentas_financieras,id',
            'comprobante_fise' => 'nullable|string|max:50',
            'mes_correspondiente' => 'required_if:tipo_pago,Sueldo,Anticipo Sueldo',
        ]);

        try {
            DB::beginTransaction();

            $contrato = Contrato::findOrFail($request->contrato_id);
            
            // --- 1. LÓGICA DE CONVERSIÓN ---
            $montoEquivalente = (float) $request->monto_pagado;

            if ($contrato->moneda !== $request->moneda_pago) {
                if ($request->moneda_pago === 'BS' && $contrato->moneda === 'USD') {
                    $montoEquivalente = $request->monto_pagado / $request->tipo_cambio;
                } elseif ($request->moneda_pago === 'USD' && $contrato->moneda === 'BS') {
                    $montoEquivalente = $request->monto_pagado * $request->tipo_cambio;
                }
            }

            $montoEquivalente = round($montoEquivalente, 2);

            // --- 2. VALIDACIÓN DE SALDO SEGÚN TIPO ---
            if (in_array($request->tipo_pago, ['Sueldo', 'Anticipo Sueldo'])) {
                $yaPagado = Pago::where('persona_id', $request->persona_id)
                    ->where('mes_correspondiente', $request->mes_correspondiente)
                    ->whereIn('tipo_pago', ['Sueldo', 'Anticipo Sueldo'])
                    ->sum('debe_equivalente');

                $saldoDisponible = $contrato->monto_mensual - $yaPagado;

                if ($montoEquivalente > ($saldoDisponible + 0.05)) {
                    throw new \Exception("El monto ({$montoEquivalente}) excede el saldo mensual disponible ({$saldoDisponible}).");
                }
            }

            if (in_array($request->tipo_pago, ['Prima', 'Anticipo Prima']) && $request->prima_id) {
                $prima = \App\Models\Prima::findOrFail($request->prima_id);
                $yaPagadoPrima = Pago::where('prima_id', $request->prima_id)->sum('debe_equivalente');
                $saldoPrima = $prima->monto_total - $yaPagadoPrima;

                if ($montoEquivalente > ($saldoPrima + 0.05)) {
                    throw new \Exception("El monto excede el saldo pendiente de la prima seleccionada.");
                }
            }

            // --- 3. CREACIÓN DEL REGISTRO (Se guarda en variable $pago) ---
            $pago = Pago::create([
                'persona_id' => $request->persona_id,
                'contrato_id' => $request->contrato_id,
                'prima_id' => $request->prima_id,
                'cuenta_financiera_id' => $request->cuenta_financiera_id,
                'comprobante_fise' => $request->comprobante_fise,
                'tipo_pago' => $request->tipo_pago,
                'monto_pagado' => $request->monto_pagado,
                'moneda_pago' => $request->moneda_pago,
                'tipo_cambio' => $request->tipo_cambio,
                'debe_equivalente' => $montoEquivalente,
                'haber_equivalente' => $montoEquivalente,
                'fecha_operacion' => $request->fecha_operacion,
                'mes_correspondiente' => $request->mes_correspondiente,
                'observacion' => $request->observacion,
            ]);

            DB::commit();

            // --- 4. REDIRECCIÓN CON SEÑAL PARA ABRIR PDF ---
            return redirect()->back()->with([
                'success' => 'Pago registrado correctamente.',
                'open_pdf' => route('pagos.pdf', $pago->id) // Ajusta 'asistencias.pdf' a tu nombre de ruta real
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function getDatosContrato($persona_id)
    {
        $contrato = Contrato::where('persona_id', $persona_id)
            ->with(['primas'])
            ->where('estado', 'ACTIVO') // Asumiendo que usas 'estado' o validación de fecha
            ->orderBy('fecha_inicio', 'desc')
            ->first();

        if (!$contrato) {
            return response()->json(['error' => 'No se encontró contrato vigente'], 404);
        }

        return response()->json($contrato);
    }

    public function show($id)
    {
        $persona = Persona::with([
            'cargo',
            'cuentasFinancieras.banco', 
            'contratos' => fn($q) => $q->where('estado', 'ACTIVO'),
            'contratos.pagos' => fn($q) => $q->orderBy('fecha_operacion', 'asc'), 
            'contratos.primas.pagos' => fn($q) => $q->orderBy('fecha_operacion', 'asc')
        ])->findOrFail($id);

        $contratoActivo = $persona->contratos->first();

        if (!$contratoActivo) {
            return redirect()->back()->with('error', 'El trabajador no posee un contrato activo.');
        }

        // --- GENERACIÓN DEL AUXILIAR POR MESES ---
        $mesesDetalle = [];
        $inicio = Carbon::parse($contratoActivo->fecha_inicio)->startOfMonth();
        $fin = now()->startOfMonth(); 
        $tempFecha = $inicio->copy();

        while ($tempFecha <= $fin) {
            $mesKey = $tempFecha->format('Y-m');
            
            // Filtramos pagos usando el objeto ya cargado en memoria (sin nuevas consultas a DB)
            $pagosDelMes = $contratoActivo->pagos
                ->where('mes_correspondiente', $mesKey)
                ->whereIn('tipo_pago', ['Sueldo', 'Anticipo Sueldo']);

            $totalPagado = $pagosDelMes->sum('debe_equivalente');

            $mesesDetalle[] = [
                'periodo' => $mesKey,
                'nombre'  => ucfirst($tempFecha->translatedFormat('F Y')),
                'pactado' => $contratoActivo->monto_mensual,
                'pagado'  => $totalPagado,
                'saldo'   => $contratoActivo->monto_mensual - $totalPagado,
                'moneda'  => $contratoActivo->moneda,
                'tickets' => $pagosDelMes
            ];
            $tempFecha->addMonth();
        }

        // --- DETALLE DE PRIMAS ---
        $primasDetalle = $contratoActivo->primas->map(function($prima) {
            $pagado = $prima->pagos->sum('debe_equivalente');
            return [
                'objeto'      => $prima,
                'descripcion' => $prima->descripcion,
                'monto_total' => $prima->monto_total,
                'pagado'      => $pagado,
                'saldo'       => $prima->monto_total - $pagado,
                'movimientos' => $prima->pagos
            ];
        });

        // --- CORRECCIÓN PARA EL MODAL ---
        // Traemos solo ID y Nombre para el select de personas (optimiza memoria)
        $personas = Persona::select('id', 'nombre')->get();

        // IMPORTANTE: Traer persona_id para que el JS de filtrarCuentas funcione
        $cuentas = CuentaFinanciera::with(['banco', 'persona:id,nombre'])
                    ->get(['id', 'banco_id', 'persona_id', 'identificador_cuenta']);

        return view('pagos.show', compact(
            'persona', 
            'contratoActivo', 
            'mesesDetalle', 
            'primasDetalle', 
            'personas', 
            'cuentas'
        ));
    }
    public function getSaldoMes($persona_id, $mes)
    {
        $contrato = Contrato::where('persona_id', $persona_id)
            ->where('estado', 'ACTIVO')
            ->first();

        if (!$contrato) return response()->json(['saldo' => 0, 'pactado' => 0]);

        // Sumamos lo que ya se pagó en ese mes (Sueldos y Anticipos)
        $pagado = Pago::where('persona_id', $persona_id)
            ->where('mes_correspondiente', $mes)
            ->whereIn('tipo_pago', ['Sueldo', 'Anticipo Sueldo'])
            ->sum('debe_equivalente');

        $saldo = $contrato->monto_mensual - $pagado;

        return response()->json([
            'pactado' => $contrato->monto_mensual,
            'pagado'  => $pagado,
            'saldo'   => $saldo,
            'moneda'  => $contrato->moneda
        ]);
    }
    public function generarPDF($id)
    {
        $pago = Pago::with(['persona', 'contrato', 'cuentaFinanciera.banco'])->findOrFail($id);

        $pdf = Pdf::loadView('pagos.pdf', compact('pago'));
        
        // Opcional: Configurar papel (ej. Media carta o A4)
        return $pdf->setPaper('letter', 'portrait')->stream('Comprobante_Pago_' . $pago->id . '.pdf');
    }
}