<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use App\Models\Cargo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PersonaController extends Controller
{
    public function index(Request $request)
    {
        $busqueda = strtolower($request->get('busqueda'));
        $perPage = $request->get('per_page', 10);

        $personas = Persona::with('cargo')
            ->where(function($query) use ($busqueda) {
                if ($busqueda) {
                    $query->where(DB::raw('LOWER(nombre)'), 'LIKE', "%{$busqueda}%")
                          ->orWhere('ci', 'LIKE', "%{$busqueda}%")
                          // Agregamos búsqueda por nacionalidad o posición si lo deseas
                          ->orWhere(DB::raw('LOWER(nacionalidad)'), 'LIKE', "%{$busqueda}%")
                          ->orWhereHas('cargo', function($q) use ($busqueda) {
                              $q->where(DB::raw('LOWER(nombre)'), 'LIKE', "%{$busqueda}%");
                          });
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $cargos = Cargo::orderBy('nombre', 'asc')->get();

        return view('personas.index', compact('personas', 'cargos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'       => 'required|string|max:255',
            'ci'           => 'required|unique:personas,ci',
            'cargo_id'     => 'required|exists:cargos,id',
            'telefono'     => 'nullable|string|max:20',
            'posicion'     => 'nullable|string|max:100',
            'nacionalidad' => 'nullable|string|max:100',
        ]);

        // Al usar HasUuids, Eloquent se encarga de generar el ID automáticamente
        Persona::create($request->all());

        return redirect()->route('personas.index')->with('success', 'Persona registrada correctamente.');
    }

    public function update(Request $request, Persona $persona)
    {
        // Nota: Laravel Route Model Binding funciona con UUID automáticamente 
        // siempre que el modelo tenga la configuración correcta de clave primaria.
        
        $request->validate([
            'nombre'       => 'required|string|max:255',
            'ci'           => 'required|unique:personas,ci,' . $persona->id, 
            'cargo_id'     => 'required|exists:cargos,id',
            'telefono'     => 'nullable|string|max:20',
            'posicion'     => 'nullable|string|max:100',
            'nacionalidad' => 'required|in:NACIONAL,EXTRANJERO',
        ]);

        $persona->update($request->all());

        return redirect()->route('personas.index')->with('success', 'Datos actualizados correctamente.');
    }

    public function storeCargo(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100|unique:cargos,nombre'
        ]);

        try {
            $cargo = Cargo::create([
                'nombre' => mb_strtoupper($request->nombre, 'UTF-8')
            ]);

            return response()->json([
                'id' => $cargo->id,
                'nombre' => $cargo->nombre,
                'message' => 'Cargo creado con éxito'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear el cargo.'
            ], 500);
        }
    }
}