<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Persona;
use App\Models\Contrato;
use App\Models\Pago;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Barryvdh\DomPDF\Facade\Pdf; // Asegúrate de tener instalado barryvdh/laravel-dompdf

class ReporteGerencialController extends Controller
{
    public function index(Request $request)
    {
        $mes = (int) $request->get('mes', now()->month);
        $anio = (int) $request->get('anio', now()->year);

        $inicioMes = Carbon::createFromDate($anio, $mes, 1)->startOfMonth();
        $finMes = Carbon::createFromDate($anio, $mes, 1)->endOfMonth();

        // KPIs
        $totalPactado = Contrato::where('fecha_inicio', '<=', $finMes)
            ->where(function($q) use ($inicioMes) {
                $q->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', $inicioMes);
            })->sum('monto_mensual');

        $totalPagadoMes = Pago::whereMonth('fecha_operacion', $mes)
            ->whereYear('fecha_operacion', $anio)
            ->whereIn('tipo_pago', ['Sueldo', 'Anticipo Sueldo'])
            ->sum('debe_equivalente');

        // Obtener y procesar datos
        $datosProcesados = Persona::whereHas('contratos', function($q) use ($inicioMes, $finMes) {
                $q->where('fecha_inicio', '<=', $finMes)
                  ->where(function($sub) use ($inicioMes) {
                      $sub->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', $inicioMes);
                  });
            })
            ->with(['contratos', 'cargo'])
            ->get()
            ->map(function($persona) use ($mes, $anio) {
                $contrato = $persona->contratos->sortByDesc('fecha_inicio')->first();
                $pagado = Pago::where('persona_id', $persona->id)
                    ->whereMonth('fecha_operacion', $mes)
                    ->whereYear('fecha_operacion', $anio)
                    ->whereIn('tipo_pago', ['Sueldo', 'Anticipo Sueldo'])
                    ->sum('debe_equivalente');
                
                $sueldo = $contrato ? $contrato->monto_mensual : 0;
                return (object)[
                    'nombre' => $persona->nombre,
                    'cargo'  => $persona->cargo->nombre ?? 'Sin Cargo',
                    'sueldo' => $sueldo,
                    'pagado' => $pagado,
                    'saldo'  => $sueldo - $pagado,
                    'moneda' => $contrato ? $contrato->moneda : 'Bs.'
                ];
            });

        // PAGINACIÓN MANUAL (5 registros)
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 5;
        $currentItems = $datosProcesados->slice(($currentPage - 1) * $perPage, $perPage)->all();
        $reporteJugadores = new LengthAwarePaginator($currentItems, $datosProcesados->count(), $perPage, $currentPage, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
            'query' => $request->query(),
        ]);

        return view('reportes.gerencial', compact('totalPactado', 'totalPagadoMes', 'reporteJugadores', 'mes', 'anio'));
    }

    public function exportarPDF(Request $request)
    {
        $mes = (int) $request->get('mes', now()->month);
        $anio = (int) $request->get('anio', now()->year);

        $inicioMes = \Carbon\Carbon::createFromDate($anio, $mes, 1)->startOfMonth();
        $finMes = \Carbon\Carbon::createFromDate($anio, $mes, 1)->endOfMonth();

        // Obtenemos TODOS los datos sin paginación para el reporte completo
        $datosProcesados = \App\Models\Persona::whereHas('contratos', function($q) use ($inicioMes, $finMes) {
                $q->where('fecha_inicio', '<=', $finMes)
                ->where(function($sub) use ($inicioMes) {
                    $sub->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', $inicioMes);
                });
            })
            ->with(['contratos', 'cargo'])
            ->get()
            ->map(function($persona) use ($mes, $anio) {
                $contrato = $persona->contratos->sortByDesc('fecha_inicio')->first();
                $pagado = \App\Models\Pago::where('persona_id', $persona->id)
                    ->whereMonth('fecha_operacion', $mes)
                    ->whereYear('fecha_operacion', $anio)
                    ->whereIn('tipo_pago', ['Sueldo', 'Anticipo Sueldo'])
                    ->sum('debe_equivalente');
                
                return (object)[
                    'nombre' => $persona->nombre,
                    'cargo'  => $persona->cargo->nombre ?? 'Sin Cargo',
                    'sueldo' => $contrato ? $contrato->monto_mensual : 0,
                    'pagado' => $pagado,
                    'saldo'  => ($contrato ? $contrato->monto_mensual : 0) - $pagado
                ];
            });

        // Usamos Barryvdh\DomPDF\Facade\Pdf
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reportes.pdf_planilla', compact('datosProcesados', 'mes', 'anio'));
        
        // Configuración opcional: tamaño carta
        $pdf->setPaper('letter', 'portrait');

        return $pdf->stream("Planilla_{$mes}_{$anio}.pdf");
    }
}