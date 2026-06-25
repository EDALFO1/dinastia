<?php

namespace App\Domains\Shared\Controllers;

use App\Http\Controllers\Controller;

use App\Models\Arl;
use Illuminate\Http\Request;

class ArlController extends Controller
{
    public function index()
    {
        $titulo = "ARL";

        $arls = Arl::orderBy('nombre')->get();

        return view('modules.arls.index', compact('titulo','arls'));
    }

    public function create()
    {
        $titulo = "Crear ARL";

        return view('modules.arls.create', compact('titulo'));
    }

    public function store(Request $request)
{
    $request->validate(
        Arl::rules(),
       
    );

    Arl::create($request->all());

    return redirect()->route('arls.index')
        ->with('success','ARL creada correctamente');
}

    public function edit(Arl $arl)
    {
        $titulo = "Editar ARL";

        return view('modules.arls.edit', compact('titulo','arl'));
    }

    public function update(Request $request, Arl $arl)
{
    $request->validate(
        Arl::rules($arl->id),
        
    );

    $arl->update($request->all());

    return redirect()->route('arls.index')
        ->with('success','ARL actualizada correctamente');
}

    public function destroy(Arl $arl)
    {
        $arl->delete();

        return redirect()->route('arls.index')
            ->with('success','ARL eliminada correctamente');
    }
}

