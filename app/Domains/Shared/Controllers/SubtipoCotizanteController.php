<?php

namespace App\Domains\Shared\Controllers;

use App\Http\Controllers\Controller;

use App\Models\SubtipoCotizante;
use Illuminate\Http\Request;

class SubtipoCotizanteController extends Controller
{

    public function index()
    {
        $titulo = "Subtipos de Cotizantes";

        $subtipos = SubtipoCotizante::orderBy('codigo')->get();

        return view('modules.subtipo_cotizantes.index', compact('titulo','subtipos'));
    }

    public function create()
    {
        $titulo = "Crear Subtipo de Cotizante";

        return view('modules.subtipo_cotizantes.create', compact('titulo'));
    }

    public function store(Request $request)
{
    $request->validate(
        SubtipoCotizante::rules(),
        [
            'codigo.unique' => 'Este cÃ³digo ya estÃ¡ registrado.',
            'nombre.unique' => 'Este nombre ya estÃ¡ registrado.',
        ]
    );

    SubtipoCotizante::create($request->all());

    return redirect()->route('subtipo_cotizantes.index')
        ->with('success','Subtipo creado correctamente');
}

    public function edit(SubtipoCotizante $subtipo_cotizante)
    {
        $titulo = "Editar Subtipo de Cotizante";

        return view('modules.subtipo_cotizantes.edit',
            compact('titulo','subtipo_cotizante'));
    }

    public function update(Request $request, SubtipoCotizante $subtipo_cotizante)
{
    $request->validate(
        SubtipoCotizante::rules($subtipo_cotizante->id),
        [
            'codigo.unique' => 'Este cÃ³digo ya estÃ¡ registrado.',
            'nombre.unique' => 'Este nombre ya estÃ¡ registrado.',
        ]
    );

    $subtipo_cotizante->update($request->all());

    return redirect()->route('subtipo_cotizantes.index')
        ->with('success','Subtipo actualizado correctamente');
}

    public function destroy(SubtipoCotizante $subtipo_cotizante)
    {
        $subtipo_cotizante->delete();

        return redirect()->route('subtipo_cotizantes.index')
            ->with('success','Subtipo eliminado correctamente');
    }

}

