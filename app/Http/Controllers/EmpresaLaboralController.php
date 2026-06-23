<?php

namespace App\Http\Controllers;

use App\Models\EmpresaLaboral;
use App\Models\Documento;
use Illuminate\Validation\Rule;
use App\Http\Requests\StoreEmpresaLaboralRequest;
use App\Http\Requests\UpdateEmpresaLaboralRequest;

class EmpresaLaboralController extends Controller
{
    public function index()
    {
        $titulo = "Empresas Laborales";

        // 🔒 filtrado automático por empresa
        $empresas = EmpresaLaboral::with('documento')
            ->orderBy('nombre')
            ->get();

        return view('modules.empresas_laborales.index',
            compact('titulo','empresas'));
    }

    public function create()
    {
        $titulo = "Crear Empresa Laboral";

        // ❌ quitamos empresas
        $documentos = Documento::orderBy('nombre')->get();

        return view('modules.empresas_laborales.create',
            compact('titulo','documentos'));
    }

    public function store(StoreEmpresaLaboralRequest $request)
{
    $data = $request->validated();

    EmpresaLaboral::create($data);

    return redirect()->route('empresas_laborales.index')
        ->with('success','Empresa laboral creada');
}

    public function edit(EmpresaLaboral $empresa_laboral)
    {
        $titulo = "Editar Empresa Laboral";

        // 🔒 protegido por scope
        $documentos = Documento::orderBy('nombre')->get();

        return view('modules.empresas_laborales.edit',
            compact('titulo','empresa_laboral','documentos'));
    }

    public function update(UpdateEmpresaLaboralRequest $request, EmpresaLaboral $empresa_laboral)
{
    $data = $request->validated();

    $empresa_laboral->update($data);

    return redirect()->route('empresas_laborales.index')
        ->with('success','Empresa laboral actualizada');
}

    public function destroy(EmpresaLaboral $empresa_laboral)
    {
        $empresa_laboral->delete();

        return redirect()->route('empresas_laborales.index')
            ->with('success','Empresa laboral eliminada');
    }
}