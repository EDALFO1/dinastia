<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\Modulo;
use Illuminate\Http\Request;

class ModuloRolController extends Controller
{
    public function index()
    {
        $roles = Rol::orderBy('nombre')->get();

        return view('modules.modulos_rol.index', [
            'titulo' => 'Módulos por Rol',
            'roles'  => $roles,
        ]);
    }

    public function edit(Rol $rol)
    {
        $modulos   = Modulo::where('activo', true)->orderBy('grupo')->orderBy('orden')->get()->groupBy('grupo');
        $asignados = $rol->modulos()->pluck('modulos.id')->toArray();

        return view('modules.modulos_rol.edit', [
            'titulo'    => "Módulos — {$rol->nombre}",
            'rol'       => $rol,
            'modulos'   => $modulos,
            'asignados' => $asignados,
        ]);
    }

    public function update(Request $request, Rol $rol)
    {
        $rol->modulos()->sync($request->input('modulos', []));

        return redirect()->route('modulos-rol.index')
            ->with('success', "Módulos del rol «{$rol->nombre}» actualizados correctamente.");
    }
}
