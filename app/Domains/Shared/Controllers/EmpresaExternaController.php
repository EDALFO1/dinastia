<?php

namespace App\Domains\Shared\Controllers;

use App\Http\Controllers\Controller;

use App\Models\EmpresaExterna;
use Illuminate\Http\Request;

class EmpresaExternaController extends Controller
{
    public function index()
    {
        $empresas = EmpresaExterna::paginate(20);

        return view('modules.empresas_externas.index', compact('empresas'));
    }

    public function create()
    {
        return view('modules.empresas_externas.create');
    }

    public function store(Request $request)
    {
        EmpresaExterna::create($request->all());

        return redirect()->route('empresas_externas.index');
    }

    public function edit(EmpresaExterna $empresa_externa)
    {
        return view('modules.empresas_externas.edit', compact('empresa_externa'));
    }

    public function update(Request $request, EmpresaExterna $empresa_externa)
    {
        $empresa_externa->update($request->all());

        return redirect()->route('empresas_externas.index');
    }

    public function destroy(EmpresaExterna $empresa_externa)
    {
        $empresa_externa->delete();

        return back();
    }
}

