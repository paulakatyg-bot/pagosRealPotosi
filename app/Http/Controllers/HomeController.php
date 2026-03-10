<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $hoy = now();
        $inicioMes = now()->startOfMonth();
        $finMes = now()->endOfMonth();
        $mesActualStr = $hoy->format('Y-m');

        // KPIs (Se mantienen globales)
        $cantJugadores = \App\Models\Persona::whereHas('cargo', function($q) {
                $q->where('nombre', 'Jugador');
            })
            ->whereHas('contratos', function($q) use ($hoy) {
                $q->where('fecha_inicio', '<=', $hoy)
                ->where(function($sub) use ($hoy) {
                    $sub->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', $hoy);
                });
            })->count();

        $totalPlanillaMes = \App\Models\Contrato::where('fecha_inicio', '<=', $finMes)
            ->where(function($q) use ($inicioMes) {
                $q->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', $inicioMes);
            })
            ->sum('monto_mensual');

        $totalPagadoMes = \App\Models\Pago::where('mes_correspondiente', $mesActualStr)
            ->whereIn('tipo_pago', ['Sueldo', 'Anticipo Sueldo'])
            ->sum('debe_equivalente');

        $saldoPendiente = $totalPlanillaMes - $totalPagadoMes;

        // 5. Lista de Personas con Paginación
        $pendientesCobro = \App\Models\Contrato::where('fecha_inicio', '<=', $finMes)
            ->where(function($q) use ($inicioMes) {
                $q->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', $inicioMes);
            })
            ->with('persona.pagos', 'persona.cargo')
            ->paginate(10); // <--- PAGINACIÓN A 10

        // Agregamos el cálculo del saldo a cada elemento paginado
        $pendientesCobro->getCollection()->transform(function($contrato) use ($mesActualStr) {
            $pagado = $contrato->persona->pagos
                ->where('mes_correspondiente', $mesActualStr)
                ->whereIn('tipo_pago', ['Sueldo', 'Anticipo Sueldo'])
                ->sum('debe_equivalente');

            $contrato->saldo_mes = $contrato->monto_mensual - $pagado;
            return $contrato;
        });

        return view('home', compact(
            'cantJugadores', 
            'totalPlanillaMes', 
            'totalPagadoMes', 
            'saldoPendiente',
            'pendientesCobro'
        ));
    }
}
