<?php

namespace App\Domains\Shared\Controllers;

use App\Http\Controllers\Controller;

use App\Models\ParametroAnual;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ParametroAnualController extends Controller
{
    public function index()
    {
        $titulo = "ParÃ¡metros Anuales";

        // ðŸ”’ filtrado automÃ¡tico por empresa
        $parametros = ParametroAnual::orderBy('anio','desc')->get();

        return view('modules.parametros_anuales.index',
            compact('titulo','parametros'));
    }

    public function create()
    {
        $titulo = "Crear ParÃ¡metro";

        // âŒ ya NO se envÃ­an empresas

        return view('modules.parametros_anuales.create',
            compact('titulo'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'anio' => [
                'required',
                'integer',
                'min:2000',
                'max:2100',
                Rule::unique('parametros_anuales')
                    ->where(fn ($q) => $q->where('empresa_id', session('empresa_id')))
            ],
            'salario_minimo' => 'required|numeric|min:0',
            'administracion' => 'required|numeric|min:0',
        ], [
            'anio.unique' => 'Ya existe un parÃ¡metro para este aÃ±o en esta empresa.'
        ]);

        $data = $request->except('empresa_id');

        ParametroAnual::create($data);

        return redirect()->route('parametros_anuales.index')
            ->with('success','ParÃ¡metro creado correctamente');
    }

    public function edit(ParametroAnual $parametro_anual)
    {
        $titulo = "Editar ParÃ¡metro";

        // ðŸ”’ protegido por scope

        return view('modules.parametros_anuales.edit',
            compact('titulo','parametro_anual'));
    }

    public function update(Request $request, ParametroAnual $parametro_anual)
    {
        $request->validate([
            'anio' => [
                'required',
                'integer',
                'min:2000',
                'max:2100',
                Rule::unique('parametros_anuales')
                    ->where(fn ($q) => $q->where('empresa_id', session('empresa_id')))
                    ->ignore($parametro_anual->id)
            ],
            'salario_minimo' => 'required|numeric|min:0',
            'administracion' => 'required|numeric|min:0',
        ], [
            'anio.unique' => 'Ya existe un parÃ¡metro para este aÃ±o en esta empresa.'
        ]);

        $data = $request->except('empresa_id');

        $parametro_anual->update($data);

        return redirect()->route('parametros_anuales.index')
            ->with('success','ParÃ¡metro actualizado');
    }

    public function destroy(ParametroAnual $parametro_anual)
    {
        $parametro_anual->delete();

        return redirect()->route('parametros_anuales.index')
            ->with('success','ParÃ¡metro eliminado');
    }
}

