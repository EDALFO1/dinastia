<?php

namespace App\Http\Controllers;

use App\Models\Asesor;
use App\Models\Documento;
use Illuminate\Validation\Rule;
use App\Http\Requests\StoreAsesorRequest;
use App\Http\Requests\UpdateAsesorRequest;

class AsesorController extends Controller
{
    public function index()
    {
        $titulo = "Asesores";

        // 🔒 ya filtrado por empresa automáticamente
        $asesores = Asesor::with(['documento'])
            ->orderBy('nombre')
            ->get();

        return view('modules.asesores.index',
            compact('titulo','asesores'));
    }

    public function create()
    {
        $titulo = "Crear Asesor";

        // ❌ quitamos empresas
        $documentos = Documento::orderBy('nombre')->get();

        return view('modules.asesores.create',
            compact('titulo','documentos'));
    }

    public function store(StoreAsesorRequest $request)
{
    $data = $request->except('empresa_id');

    Asesor::create($data);

    return redirect()->route('asesores.index')
        ->with('success','Asesor creado correctamente');
}

    public function edit(Asesor $asesor)
    {
        $titulo = "Editar Asesor";

        // 🔒 protegido por Route Model Binding + GlobalScope
        $documentos = Documento::orderBy('nombre')->get();

        return view('modules.asesores.edit',
            compact('titulo','asesor','documentos'));
    }

    public function update(UpdateAsesorRequest $request, Asesor $asesor)
{
    $data = $request->except('empresa_id');

    $asesor->update($data);

    return redirect()->route('asesores.index')
        ->with('success','Asesor actualizado');
}

    public function destroy(Asesor $asesor)
    {
        $asesor->delete();

        return redirect()->route('asesores.index')
            ->with('success','Asesor eliminado');
    }
}