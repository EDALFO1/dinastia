<?php

namespace App\Domains\Payroll\Controllers;

use App\Http\Controllers\Controller;

use App\Models\RemisionDetalle;
use App\Models\Remision;
use Illuminate\Http\Request;

class RemisionDetalleController extends Controller
{

    public function index()
    {

        $titulo = "Detalle de Remisiones";

        $detalles = RemisionDetalle::with('remision')
            ->latest()
            ->paginate(20);

        return view('modules.remision_detalles.index',
            compact('titulo','detalles'));
    }


    public function create()
    {

        $titulo = "Crear Detalle";

        $remisiones = Remision::orderBy('id','desc')->get();

        return view('modules.remision_detalles.create',
            compact('titulo','remisiones'));
    }


    public function store(Request $request)
{
    RemisionDetalle::create([
        'remision_id' => $request->remision_id,
        'concepto' => $request->concepto,
        'valor' => $request->valor
    ]);

    return redirect()->route('remision_detalles.index')
        ->with('success','Detalle creado correctamente');
}


    public function edit(RemisionDetalle $remision_detalle)
    {

        $titulo = "Editar Detalle";

        $remisiones = Remision::orderBy('id','desc')->get();

        return view('modules.remision_detalles.edit',
            compact('titulo','remision_detalle','remisiones'));
    }


    public function update(Request $request, RemisionDetalle $remision_detalle)
{
    $remision_detalle->update([
        'remision_id' => $request->remision_id,
        'concepto' => $request->concepto,
        'valor' => $request->valor
    ]);

    return redirect()->route('remision_detalles.index')
        ->with('success','Detalle actualizado');
}


    public function destroy(RemisionDetalle $remision_detalle)
    {

        $remision_detalle->delete();

        return redirect()->route('remision_detalles.index')
            ->with('success','Detalle eliminado');
    }

}

