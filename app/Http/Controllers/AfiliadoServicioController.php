<?php

namespace App\Http\Controllers;

use App\Models\AfiliadoServicio;
use App\Models\Afiliado;
use App\Models\Servicio;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AfiliadoServicioController extends Controller
{
    public function index()
    {
        $titulo = "Servicios por Afiliado";

        $registros = AfiliadoServicio::with(['afiliado', 'servicio'])
            ->whereHas('afiliado')
            ->whereHas('servicio')
            ->orderBy('id', 'desc')
            ->get();

        return view('modules.afiliado_servicios.index',
            compact('titulo','registros'));
    }

    public function create()
    {
        $titulo = "Asignar Servicio";

        // 🔒 filtrado automático por empresa
        $afiliados = Afiliado::orderBy('primer_apellido')->get();

        // ⚠️ decidir si servicios son globales (por ahora sí)
        $servicios = Servicio::orderBy('nombre')->get();

        return view('modules.afiliado_servicios.create',
            compact('titulo','afiliados','servicios'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'afiliado_id' => 'required|exists:afiliados,id',
            'servicio_id' => 'required|exists:servicios,id',
            'valor' => 'nullable|numeric|min:0',
            'estado' => 'nullable|boolean',

            // 🔥 evitar duplicados por empresa
            'servicio_id' => [
                'required',
                Rule::unique('afiliado_servicios')
                    ->where(fn ($q) => $q
                        ->where('empresa_id', session('empresa_id'))
                        ->where('afiliado_id', $request->afiliado_id)
                    )
            ]
        ], [
            'servicio_id.unique' => 'Este afiliado ya tiene asignado este servicio.'
        ]);

        $data = $request->except('empresa_id');

        AfiliadoServicio::create($data);

        return redirect()->route('afiliado_servicios.index')
            ->with('success','Servicio asignado correctamente');
    }

    public function edit(AfiliadoServicio $afiliado_servicio)
    {
        $titulo = "Editar Servicio";

        $afiliados = Afiliado::orderBy('primer_apellido')->get();
        $servicios = Servicio::orderBy('nombre')->get();

        return view('modules.afiliado_servicios.edit',
            compact(
                'titulo',
                'afiliado_servicio',
                'afiliados',
                'servicios'
            ));
    }

    public function update(Request $request, AfiliadoServicio $afiliado_servicio)
    {
        $request->validate([
            'afiliado_id' => 'required|exists:afiliados,id',
            'servicio_id' => [
                'required',
                Rule::unique('afiliado_servicios')
                    ->where(fn ($q) => $q
                        ->where('empresa_id', session('empresa_id'))
                        ->where('afiliado_id', $request->afiliado_id)
                    )
                    ->ignore($afiliado_servicio->id)
            ],
            'valor' => 'nullable|numeric|min:0',
            'estado' => 'nullable|boolean',
        ], [
            'servicio_id.unique' => 'Este afiliado ya tiene asignado este servicio.'
        ]);

        $data = $request->except('empresa_id');

        $afiliado_servicio->update($data);

        return redirect()->route('afiliado_servicios.index')
            ->with('success','Servicio actualizado');
    }

    public function destroy(AfiliadoServicio $afiliado_servicio)
    {
        $afiliado_servicio->delete();

        return redirect()->route('afiliado_servicios.index')
            ->with('success','Registro eliminado');
    }
}