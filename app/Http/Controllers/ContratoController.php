<?php

namespace App\Http\Controllers;

use App\Models\Contrato;
use App\Models\Persona;
use App\Models\Prima;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContratoController extends Controller
{
    public function index(Request $request)
    {
        // Cargamos persona y también las primas para mostrarlas en la tabla
        $query = Contrato::with(['persona', 'primas']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('persona', function($q) use ($search) {
                $q->where('nombre', 'LIKE', "%{$search}%");
            })->orWhere('modalidad', 'LIKE', "%{$search}%");
        }

        $contratos = $query->latest()->paginate(10);
        $personas = Persona::orderBy('nombre')->get();

        return view('contratos.index', compact('contratos', 'personas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'persona_id'    => 'required|exists:personas,id',
            'monto_mensual' => 'required|numeric|min:0',
            'moneda'        => 'required|string|max:5',
            'fecha_inicio'  => 'required|date',
            'fecha_fin'     => 'required|date|after_or_equal:fecha_inicio',
            'modalidad'     => 'required|string',
            // Validaciones para primas (opcionales)
            'primas.*.descripcion'    => 'required_with:primas|string',
            'primas.*.monto_total'    => 'required_with:primas|numeric|min:0',
            'primas.*.fecha_pactada'  => 'required_with:primas|date',
        ]);

        try {
            DB::beginTransaction();

            // 1. Crear el Contrato
            $contrato = Contrato::create([
                'persona_id'    => $request->persona_id,
                'monto_mensual' => $request->monto_mensual,
                'moneda'        => $request->moneda,
                'fecha_inicio'  => $request->fecha_inicio,
                'fecha_fin'     => $request->fecha_fin,
                'modalidad'     => $request->modalidad,
            ]);

            // 2. Crear las Primas si existen
            if ($request->has('primas')) {
                foreach ($request->primas as $primaData) {
                    $contrato->primas()->create($primaData);
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Contrato y primas registrados correctamente.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error al registrar: ' . $e->getMessage());
        }
    }
    public function update(Request $request, Contrato $contrato)
    {
        $request->validate([
            'persona_id'    => 'required|exists:personas,id',
            'monto_mensual' => 'required|numeric|min:0',
            'moneda'        => 'required|string|max:5',
            'fecha_inicio'  => 'required|date',
            'fecha_fin'     => 'required|date|after_or_equal:fecha_inicio',
            'modalidad'     => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            // 1. Actualizar datos básicos del contrato
            $contrato->update($request->only([
                'persona_id', 'monto_mensual', 'moneda', 'fecha_inicio', 'fecha_fin', 'modalidad'
            ]));

            // 2. Sincronizar Primas
            // Para simplificar: eliminamos las primas actuales y creamos las nuevas que vienen del form
            $contrato->primas()->delete(); 

            if ($request->has('primas')) {
                foreach ($request->primas as $primaData) {
                    if (!empty($primaData['descripcion']) && !empty($primaData['monto_total'])) {
                        $contrato->primas()->create($primaData);
                    }
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Contrato actualizado correctamente.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }
}