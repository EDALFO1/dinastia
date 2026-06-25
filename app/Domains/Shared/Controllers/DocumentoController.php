<?php

namespace App\Domains\Shared\Controllers;

use App\Http\Controllers\Controller;

use App\Models\Documento;
use Illuminate\Http\Request;

class DocumentoController extends Controller
{
    public function index()
    {
        $titulo = "Tipos de Documento";

        $documentos = Documento::orderBy('nombre')->get();

        return view('modules.documentos.index', compact('titulo','documentos'));
    }

    public function create()
    {
        $titulo = "Crear Documento";

        return view('modules.documentos.create', compact('titulo'));
    }

    public function store(Request $request)
{
    $request->validate(
        Documento::rules(),
        [
            'nombre.unique' => 'Este nombre ya estÃ¡ registrado.',
            'codigo.unique' => 'Este cÃ³digo ya estÃ¡ registrado.',
        ]
    );

    Documento::create($request->all());

    return redirect()->route('documentos.index')
        ->with('success','Documento creado correctamente');
}

    public function edit(Documento $documento)
    {
        $titulo = "Editar Documento";

        return view('modules.documentos.edit', compact('titulo','documento'));
    }

    public function update(Request $request, Documento $documento)
{
    $request->validate(
        Documento::rules($documento->id),
        [
            'nombre.unique' => 'Este nombre ya estÃ¡ registrado.',
            'codigo.unique' => 'Este cÃ³digo ya estÃ¡ registrado.',
        ]
    );

    $documento->update($request->all());

    return redirect()->route('documentos.index')
        ->with('success','Documento actualizado correctamente');
}

    public function destroy(Documento $documento)
    {
        $documento->delete();

        return redirect()->route('documentos.index')
            ->with('success','Documento eliminado correctamente');
    }
}

