<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use Illuminate\Http\Request;

class RolController extends Controller
{

    public function index()
    {
        $titulo = "Roles";

        $roles = Rol::orderBy('nombre')->get();

        return view('modules.roles.index', compact('titulo','roles'));
    }

    public function create()
    {
        $titulo = "Crear Rol";

        return view('modules.roles.create', compact('titulo'));
    }

    public function store(Request $request)
{
    $request->validate(
        Rol::rules(),
        [
            'nombre.unique' => 'Este rol ya está registrado.',
        ]
    );

    Rol::create($request->all());

    return redirect()->route('roles.index')
        ->with('success','Rol creado correctamente');
}

    public function edit(Rol $role)
    {
        $titulo = "Editar Rol";

        return view('modules.roles.edit', compact('titulo','role'));
    }

    public function update(Request $request, Rol $role)
{
    $request->validate(
        Rol::rules($role->id),
        [
            'nombre.unique' => 'Este rol ya está registrado.',
        ]
    );

    $role->update($request->all());

    return redirect()->route('roles.index')
        ->with('success','Rol actualizado correctamente');
}

    public function destroy(Rol $role)
    {
        $role->delete();

        return redirect()->route('roles.index')
            ->with('success','Rol eliminado correctamente');
    }

}