<?php

namespace App\Http\Controllers;

use App\Models\Eps;
use Illuminate\Http\Request;

class EpsController extends Controller
{
    public function index()
    {
        $titulo = "Eps";
        $eps = Eps::all();

        return view('modules.eps.index', compact('titulo','eps'));
    }

    public function create()
    {
        $titulo = 'Crear Eps';

        return view('modules.eps.create', compact('titulo'));
    }

    public function store(Request $request)
{
    $request->validate(
        Eps::rules(),
        [
            'nombre.unique' => 'Este nombre ya está registrado.',
            'codigo.unique' => 'Este código ya está registrado.',
        ]
    );

    Eps::create($request->all());

    return redirect()->route('eps.index')
        ->with('success','EPS creada correctamente');
}

    public function show(Eps $eps)
    {
        return view('modules.eps.show', compact('eps'));
    }

    public function edit(Eps $eps)
    {
        $titulo = 'Editar Eps';

        return view('modules.eps.edit', compact('titulo','eps'));
    }

    public function update(Request $request, Eps $eps)
{
    $request->validate(
        Eps::rules($eps->id),
        [
            'nombre.unique' => 'Este nombre ya está registrado.',
            'codigo.unique' => 'Este código ya está registrado.',
        ]
    );

    $eps->update($request->all());

    return redirect()->route('eps.index')
        ->with('success','EPS actualizada correctamente');
}

    public function destroy(Eps $eps)
    {
        $eps->delete();

        return redirect()->route('eps.index')
            ->with('success','EPS eliminada correctamente');
    }
}