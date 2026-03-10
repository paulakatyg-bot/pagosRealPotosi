<?php
namespace App\Http\Controllers;

use App\Models\CuentaFinanciera;
use App\Models\Banco;
use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CuentaFinancieraController extends Controller
{
    public function index(Request $request)
    {
        $busqueda = strtolower($request->get('busqueda'));
        $perPage = $request->get('per_page', 10);

        $cuentas = CuentaFinanciera::with(['persona', 'banco'])
            ->where(function($query) use ($busqueda) {
                if ($busqueda) {
                    $query->where(DB::raw('LOWER(identificador_cuenta)'), 'LIKE', "%{$busqueda}%")
                          ->orWhereHas('persona', function($q) use ($busqueda) {
                              $q->where(DB::raw('LOWER(nombre)'), 'LIKE', "%{$busqueda}%");
                          })
                          ->orWhereHas('banco', function($q) use ($busqueda) {
                              $q->where(DB::raw('LOWER(nombre)'), 'LIKE', "%{$busqueda}%");
                          });
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $bancos = Banco::orderBy('nombre', 'asc')->get();
        $personas = Persona::orderBy('nombre', 'asc')->get();

        return view('cuentas.index', compact('cuentas', 'bancos', 'personas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'persona_id'           => 'required|exists:personas,id',
            'banco_id'             => 'required|exists:bancos,id',
            'identificador_cuenta' => 'required|string|max:255',
            'observacion_cuenta'   => 'nullable|string|max:500',
        ]);

        CuentaFinanciera::create($request->all());

        return redirect()->back()->with('success', 'Cuenta financiera registrada con éxito.');
    }

    public function update(Request $request, CuentaFinanciera $cuenta)
    {
        $request->validate([
            'persona_id'           => 'required|exists:personas,id',
            'banco_id'             => 'required|exists:bancos,id',
            'identificador_cuenta' => 'required|string|max:255',
            'observacion_cuenta'   => 'nullable|string|max:500',
        ]);

        $cuenta->update($request->all());

        return redirect()->back()->with('success', 'Cuenta actualizada correctamente.');
    }

    // AJAX para crear Banco rápido
    public function storeBanco(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|unique:bancos,nombre',
            'tipo'   => 'required|in:tradicional,cripto'
        ]);

        $banco = Banco::create([
            'nombre' => mb_strtoupper($request->nombre, 'UTF-8'),
            'tipo'   => $request->tipo
        ]);

        return response()->json($banco, 201);
    }
}