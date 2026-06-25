<?php

namespace App\Domains\Payroll\Controllers;

use App\Http\Controllers\Controller;

use App\Models\Afiliado;
use App\Models\Arl;
use App\Models\Eps;
use App\Models\EmpresaLaboral;
use App\Models\Incapacidad;
use App\Models\IncapacidadObservacion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IncapacidadController extends Controller
{
    public function index(Request $request)
    {
        $titulo = 'Control de Incapacidades';

        $query = Incapacidad::with(['afiliado', 'empresaLaboral', 'eps', 'arl'])->latest();

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('entidad_tipo')) {
            $query->where('entidad_tipo', $request->entidad_tipo);
        }

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('documento', 'like', "%{$buscar}%");
            });
        }

        $incapacidades = $query->paginate(25)->withQueryString();

        $estados = Incapacidad::estados();

        $stats = [];
        foreach ($estados as $key => $info) {
            $stats[$key] = Incapacidad::where('estado', $key)->count();
        }

        return view('modules.incapacidades.index',
            compact('titulo', 'incapacidades', 'estados', 'stats'));
    }

    public function create()
    {
        $titulo         = 'Nueva Incapacidad';
        $afiliados      = Afiliado::orderBy('primer_apellido')->orderBy('primer_nombre')->get();
        $empresasLaboral = EmpresaLaboral::orderBy('nombre')->get();
        $epsList        = Eps::orderBy('nombre')->get();
        $arlList        = Arl::orderBy('nombre')->get();
        $estados        = Incapacidad::estados();
        $incapacidad    = new Incapacidad();

        return view('modules.incapacidades.create',
            compact('titulo', 'incapacidad', 'afiliados', 'empresasLaboral', 'epsList', 'arlList', 'estados'));
    }

    public function store(Request $request)
    {
        $request->validate($this->rules());

        $data = $this->prepareData($request);

        Incapacidad::create($data);

        return redirect()->route('incapacidades.index')
            ->with('success', 'Incapacidad registrada correctamente.');
    }

    public function show(Incapacidad $incapacidad)
    {
        $titulo = 'Detalle de Incapacidad';
        $incapacidad->load(['afiliado', 'empresaLaboral', 'eps', 'arl', 'observaciones.usuario']);
        $estados = Incapacidad::estados();

        return view('modules.incapacidades.show',
            compact('titulo', 'incapacidad', 'estados'));
    }

    public function edit(Incapacidad $incapacidad)
    {
        $titulo         = 'Editar Incapacidad';
        $afiliados      = Afiliado::orderBy('primer_apellido')->orderBy('primer_nombre')->get();
        $empresasLaboral = EmpresaLaboral::orderBy('nombre')->get();
        $epsList        = Eps::orderBy('nombre')->get();
        $arlList        = Arl::orderBy('nombre')->get();
        $estados        = Incapacidad::estados();

        return view('modules.incapacidades.edit',
            compact('titulo', 'incapacidad', 'afiliados', 'empresasLaboral', 'epsList', 'arlList', 'estados'));
    }

    public function update(Request $request, Incapacidad $incapacidad)
    {
        $request->validate($this->rules());

        $incapacidad->update($this->prepareData($request));

        return redirect()->route('incapacidades.show', $incapacidad)
            ->with('success', 'Incapacidad actualizada correctamente.');
    }

    public function destroy(Incapacidad $incapacidad)
    {
        $incapacidad->delete();

        return redirect()->route('incapacidades.index')
            ->with('success', 'Incapacidad eliminada.');
    }

    public function agregarObservacion(Request $request, Incapacidad $incapacidad)
    {
        $request->validate([
            'nota' => ['required', 'string', 'max:1000'],
        ]);

        IncapacidadObservacion::create([
            'incapacidad_id' => $incapacidad->id,
            'nota'           => $request->nota,
        ]);

        return back()->with('success', 'ObservaciÃ³n registrada.');
    }

    // â”€â”€ Privados â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    private function rules(): array
    {
        return [
            'afiliado_id'       => ['nullable', 'exists:afiliados,id'],
            'documento'         => ['required', 'string', 'max:50'],
            'nombre'            => ['required', 'string', 'max:255'],
            'empresa_laboral_id'=> ['nullable', 'exists:empresas_laborales,id'],
            'entidad_tipo'      => ['required', Rule::in(['EPS', 'ARL'])],
            'eps_id'            => ['nullable', 'exists:eps,id'],
            'arl_id'            => ['nullable', 'exists:arls,id'],
            'fecha_inicio'      => ['required', 'date'],
            'fecha_fin'         => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'fecha_radicacion'  => ['nullable', 'date'],
            'estado'            => ['required', Rule::in(array_keys(Incapacidad::estados()))],
            'fecha_pago'        => ['nullable', 'date'],
        ];
    }

    private function prepareData(Request $request): array
    {
        $esEps = $request->entidad_tipo === 'EPS';

        $entidadNombre = $esEps
            ? (Eps::find($request->eps_id)?->nombre ?? '')
            : (Arl::find($request->arl_id)?->nombre ?? '');

        return [
            'afiliado_id'        => $request->afiliado_id,
            'documento'          => $request->documento,
            'nombre'             => $request->nombre,
            'empresa_laboral_id' => $request->empresa_laboral_id,
            'entidad_tipo'       => $request->entidad_tipo,
            'eps_id'             => $esEps ? $request->eps_id : null,
            'arl_id'             => !$esEps ? $request->arl_id : null,
            'entidad_nombre'     => $entidadNombre,
            'fecha_inicio'       => $request->fecha_inicio,
            'fecha_fin'          => $request->fecha_fin,
            'dias_solicitados'   => Incapacidad::calcularDias($request->fecha_inicio, $request->fecha_fin),
            'fecha_radicacion'   => $request->fecha_radicacion,
            'estado'             => $request->estado,
            'fecha_pago'         => $request->estado === 'pagada' ? $request->fecha_pago : null,
        ];
    }
}


