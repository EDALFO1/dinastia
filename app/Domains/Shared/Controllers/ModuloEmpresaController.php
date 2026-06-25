<?php

namespace App\Domains\Shared\Controllers;

use App\Http\Controllers\Controller;

use App\Models\Empresa;
use App\Models\Modulo;
use Illuminate\Http\Request;

class ModuloEmpresaController extends Controller
{
    public function index()
    {
        $empresas = Empresa::orderBy('nombre')->get();

        return view('modules.modulos_empresa.index', [
            'titulo'   => 'MÃ³dulos por Empresa',
            'empresas' => $empresas,
        ]);
    }

    public function edit(Empresa $empresa)
    {
        $modulos   = Modulo::where('activo', true)->orderBy('grupo')->orderBy('orden')->get()->groupBy('grupo');
        $asignados = $empresa->modulos()->pluck('modulos.id')->toArray();

        return view('modules.modulos_empresa.edit', [
            'titulo'    => "MÃ³dulos â€” {$empresa->nombre}",
            'empresa'   => $empresa,
            'modulos'   => $modulos,
            'asignados' => $asignados,
        ]);
    }

    public function update(Request $request, Empresa $empresa)
    {
        $empresa->modulos()->sync($request->input('modulos', []));

        return redirect()->route('modulos-empresa.index')
            ->with('success', "MÃ³dulos de Â«{$empresa->nombre}Â» actualizados correctamente.");
    }
}


