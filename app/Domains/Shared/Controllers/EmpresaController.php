<?php

namespace App\Domains\Shared\Controllers;

use App\Http\Controllers\Controller;

use App\Models\Empresa;
use App\Http\Requests\StoreEmpresaRequest;
use App\Http\Requests\UpdateEmpresaRequest;


class EmpresaController extends Controller
{
    public function index()
    {
        $titulo = "Empresas";

        if (auth()->user()->tipo === 'admin') {
            // ðŸ”¥ ADMIN VE TODAS
            $empresas = Empresa::orderBy('nombre')->get();
        } else {
            // ðŸ”’ USUARIO SOLO SUS EMPRESAS
            $empresas = auth()->user()
                ->empresas()
                ->orderBy('nombre')
                ->get();
        }

        return view('modules.empresas.index', compact('titulo','empresas'));
    }

    public function create()
    {
        $titulo = "Crear Empresa";

        return view('modules.empresas.create', compact('titulo'));
    }

    public function store(StoreEmpresaRequest $request)
{
    $empresa = Empresa::create($request->all());

    $empresa->usuarios()->syncWithoutDetaching([auth()->id()]);

    return redirect()->route('empresas.index')
        ->with('success','Empresa guardada correctamente');
}

    public function edit($id)
    {
        $titulo = "Editar Empresa";

        if (auth()->user()->tipo === 'admin') {
            $empresa = Empresa::findOrFail($id);
        } else {
            $empresa = auth()->user()
                ->empresas()
                ->where('empresas.id', $id)
                ->firstOrFail();
        }

        return view('modules.empresas.edit', compact('titulo','empresa'));
    }

    public function update(UpdateEmpresaRequest $request, $id)
{
    if (auth()->user()->tipo === 'admin') {
        $empresa = Empresa::findOrFail($id);
    } else {
        $empresa = auth()->user()
            ->empresas()
            ->where('empresas.id', $id)
            ->firstOrFail();
    }

    $empresa->update($request->all());

    return redirect()->route('empresas.index')
        ->with('success','Empresa actualizada correctamente');
}

    public function destroy($id)
    {
        if (auth()->user()->tipo === 'admin') {
            $empresa = Empresa::findOrFail($id);
        } else {
            $empresa = auth()->user()
                ->empresas()
                ->where('empresas.id', $id)
                ->firstOrFail();
        }

        $empresa->delete();

        return redirect()->route('empresas.index')
            ->with('success','Empresa eliminada correctamente');
    }
}

