<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use Illuminate\Http\Request;

class CajaController extends Controller
{
    public function index()
    {
        $titulo = "Cajas de Compensación";

        $cajas = Caja::orderBy('nombre')->get();

        return view('modules.cajas.index', compact('titulo','cajas'));
    }

    public function create()
    {
        $titulo = "Crear Caja";

        return view('modules.cajas.create', compact('titulo'));
    }

   public function store(Request $request)
{
    $request->validate(
        Caja::rules(),
        [
            'nombre.unique' => 'Este nombre ya está registrado.',
            'codigo.unique' => 'Este código ya está registrado.',
        ]
    );

    Caja::create($request->all());

    return redirect()->route('cajas.index')
        ->with('success','Caja creada correctamente');
}

    public function edit(Caja $caja)
    {
        $titulo = "Editar Caja";

        return view('modules.cajas.edit', compact('titulo','caja'));
    }

    public function update(Request $request, Caja $caja)
{
    $request->validate(
        Caja::rules($caja->id),
        [
            'nombre.unique' => 'Este nombre ya está registrado.',
            'codigo.unique' => 'Este código ya está registrado.',
        ]
    );

    $caja->update($request->all());

    return redirect()->route('cajas.index')
        ->with('success','Caja actualizada correctamente');
}

    public function destroy(Caja $caja)
    {
        $caja->delete();

        return redirect()->route('cajas.index')
            ->with('success','Caja eliminada correctamente');
    }
}