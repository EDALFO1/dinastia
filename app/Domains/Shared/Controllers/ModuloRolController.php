<?php

namespace App\Domains\Shared\Controllers;

use App\Http\Controllers\Controller;

use App\Models\Rol;
use App\Models\Modulo;
use Illuminate\Http\Request;

class ModuloRolController extends Controller
{
    public function index()
    {
        $roles = Rol::orderBy('nombre')->get();

        return view('modules.modulos_rol.index', [
            'titulo' => 'MÃ³dulos por Rol',
            'roles'  => $roles,
        ]);
    }

    public function edit(Rol $rol)
    {
        $modulos   = Modulo::where('activo', true)->orderBy('grupo')->orderBy('orden')->get()->groupBy('grupo');
        $asignados = $rol->modulos()->pluck('modulos.id')->toArray();

        return view('modules.modulos_rol.edit', [
            'titulo'    => "MÃ³dulos â€” {$rol->nombre}",
            'rol'       => $rol,
            'modulos'   => $modulos,
            'asignados' => $asignados,
        ]);
    }

    public function update(Request $request, Rol $rol)
    {
        $rol->modulos()->sync($request->input('modulos', []));

        return redirect()->route('modulos-rol.index')
            ->with('success', "MÃ³dulos del rol Â«{$rol->nombre}Â» actualizados correctamente.");
    }
}


