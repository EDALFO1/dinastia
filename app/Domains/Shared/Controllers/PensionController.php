<?php

namespace App\Domains\Shared\Controllers;

use App\Http\Controllers\Controller;

use App\Models\Pension;
use Illuminate\Http\Request;

class PensionController extends Controller
{
    public function index()
    {
        $titulo = "Pensiones";

        $pensions = Pension::orderBy('nombre')->get();

        return view('modules.pensions.index', compact('titulo','pensions'));
    }

    public function create()
    {
        $titulo = "Crear PensiÃ³n";

        return view('modules.pensions.create', compact('titulo'));
    }

    public function store(Request $request)
{
    $request->validate(
        Pension::rules(),
        [
            'nombre.unique' => 'Este nombre ya estÃ¡ registrado.',
            'codigo.unique' => 'Este cÃ³digo ya estÃ¡ registrado.',
        ]
    );

    Pension::create($request->all());

    return redirect()->route('pensions.index')
        ->with('success','PensiÃ³n creada correctamente');
}

    public function edit(Pension $pension)
    {
        $titulo = "Editar PensiÃ³n";

        return view('modules.pensions.edit', compact('titulo','pension'));
    }

    public function update(Request $request, Pension $pension)
{
    $request->validate(
        Pension::rules($pension->id),
        [
            'nombre.unique' => 'Este nombre ya estÃ¡ registrado.',
            'codigo.unique' => 'Este cÃ³digo ya estÃ¡ registrado.',
        ]
    );

    $pension->update($request->all());

    return redirect()->route('pensions.index')
        ->with('success','PensiÃ³n actualizada correctamente');
}

    public function destroy(Pension $pension)
    {
        $pension->delete();

        return redirect()->route('pensions.index')
            ->with('success','PensiÃ³n eliminada correctamente');
    }
}

