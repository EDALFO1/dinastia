<?php

namespace App\Domains\Shared\Controllers;

use App\Http\Controllers\Controller;

use App\Models\ParametroAnual;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index(Request $request)
    {
        $titulo = 'Planes de Servicio';

        $aniosDisponibles = ParametroAnual::orderBy('anio', 'desc')->pluck('anio');

        $anio = (int) $request->get('anio', $aniosDisponibles->first() ?? date('Y'));

        $parametro = ParametroAnual::where('anio', $anio)->first();

        $planes = Plan::orderBy('orden')->orderBy('nombre')->get()
            ->map(fn($plan) => [
                'plan'    => $plan,
                'calculo' => $parametro ? $plan->calcularValor($parametro) : null,
            ]);

        return view('modules.planes.index', compact(
            'titulo', 'planes', 'anio', 'aniosDisponibles', 'parametro'
        ));
    }

    public function create()
    {
        $titulo  = 'Nuevo Plan';
        $plan    = new Plan();
        $niveles = Plan::nivelesArl();

        return view('modules.planes.create', compact('titulo', 'plan', 'niveles'));
    }

    public function store(Request $request)
    {
        $request->validate(Plan::rules());

        Plan::create($this->prepareData($request));

        return redirect()->route('planes.index')
            ->with('success', 'Plan creado correctamente.');
    }

    public function edit(Plan $plan)
    {
        $titulo  = 'Editar Plan';
        $niveles = Plan::nivelesArl();

        return view('modules.planes.edit', compact('titulo', 'plan', 'niveles'));
    }

    public function update(Request $request, Plan $plan)
    {
        $request->validate(Plan::rules());

        $plan->update($this->prepareData($request));

        return redirect()->route('planes.index')
            ->with('success', 'Plan actualizado correctamente.');
    }

    public function destroy(Plan $plan)
    {
        $plan->delete();

        return redirect()->route('planes.index')
            ->with('success', 'Plan eliminado.');
    }

    private function prepareData(Request $request): array
    {
        $incluyeEps     = (bool) $request->input('incluye_eps', false);
        $incluyePension = (bool) $request->input('incluye_pension', false);
        $incluyeCaja    = (bool) $request->input('incluye_caja', false);
        $incluyeArl     = (bool) $request->input('incluye_arl', false);
        $usaAdminFijo   = (bool) $request->input('usa_admin_fijo', false);

        return [
            'nombre'             => $request->nombre,
            'descripcion'        => $request->descripcion,
            'incluye_eps'        => $incluyeEps,
            'porcentaje_eps'     => $incluyeEps ? (float) $request->porcentaje_eps : 0,
            'incluye_pension'    => $incluyePension,
            'porcentaje_pension' => $incluyePension ? (float) $request->porcentaje_pension : 0,
            'incluye_caja'       => $incluyeCaja,
            'porcentaje_caja'    => $incluyeCaja ? (float) $request->porcentaje_caja : 0,
            'incluye_arl'        => $incluyeArl,
            'nivel_arl'          => $incluyeArl ? $request->nivel_arl : null,
            'porcentaje_arl'     => $incluyeArl ? (float) $request->porcentaje_arl : 0,
            'usa_admin_fijo'     => $usaAdminFijo,
            'valor_admin_fijo'   => $usaAdminFijo ? (float) $request->valor_admin_fijo : 0,
            'orden'              => (int) ($request->orden ?? 0),
            'estado'             => (bool) $request->input('estado', false),
        ];
    }
}


