<?php

namespace App\Http\Controllers;

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
        $titulo = "Crear Pensión";

        return view('modules.pensions.create', compact('titulo'));
    }

    public function store(Request $request)
{
    $request->validate(
        Pension::rules(),
        [
            'nombre.unique' => 'Este nombre ya está registrado.',
            'codigo.unique' => 'Este código ya está registrado.',
        ]
    );

    Pension::create($request->all());

    return redirect()->route('pensions.index')
        ->with('success','Pensión creada correctamente');
}

    public function edit(Pension $pension)
    {
        $titulo = "Editar Pensión";

        return view('modules.pensions.edit', compact('titulo','pension'));
    }

    public function update(Request $request, Pension $pension)
{
    $request->validate(
        Pension::rules($pension->id),
        [
            'nombre.unique' => 'Este nombre ya está registrado.',
            'codigo.unique' => 'Este código ya está registrado.',
        ]
    );

    $pension->update($request->all());

    return redirect()->route('pensions.index')
        ->with('success','Pensión actualizada correctamente');
}

    public function destroy(Pension $pension)
    {
        $pension->delete();

        return redirect()->route('pensions.index')
            ->with('success','Pensión eliminada correctamente');
    }
}