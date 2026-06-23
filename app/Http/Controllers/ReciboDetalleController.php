<?php

namespace App\Http\Controllers;

use App\Models\ReciboDetalle;
use App\Models\Recibo;
use Illuminate\Http\Request;

class ReciboDetalleController extends Controller
{

    public function index()
    {
        $titulo = "Detalle de Recibos";

        $detalles = ReciboDetalle::with('recibo')
            ->latest()
            ->paginate(20);

        return view('modules.recibo_detalles.index',
            compact('titulo','detalles'));
    }


    public function create()
    {
        $titulo = "Crear Detalle";

        $recibos = Recibo::orderBy('id','desc')->get();

        return view('modules.recibo_detalles.create',
            compact('titulo','recibos'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'recibo_id' => 'required',
            'concepto' => 'required',
            'valor' => 'required|numeric|min:0'
        ]);

        // 🔴 VALIDACIÓN: evitar crear si ya fue exportado
        $recibo = Recibo::find($request->recibo_id);
        if ($recibo && $recibo->export_batch_id) {
            return back()->with('error','No se puede crear, el recibo ya fue exportado');
        }

        ReciboDetalle::create([
            'recibo_id' => $request->recibo_id,
            'concepto' => $request->concepto,
            'valor' => $request->valor
        ]);

        return redirect()->route('recibo_detalles.index')
            ->with('success','Detalle creado correctamente');
    }


    public function edit(ReciboDetalle $recibo_detalle)
    {
        $titulo = "Editar Detalle";

        $recibos = Recibo::orderBy('id','desc')->get();

        return view('modules.recibo_detalles.edit',
            compact('titulo','recibo_detalle','recibos'));
    }


    public function update(Request $request, ReciboDetalle $recibo_detalle)
    {
        $request->validate([
            'recibo_id' => 'required',
            'concepto' => 'required',
            'valor' => 'required|numeric|min:0'
        ]);

        // 🔴 VALIDACIÓN: evitar editar si ya fue exportado
        if ($recibo_detalle->recibo && $recibo_detalle->recibo->export_batch_id) {
            return back()->with('error','No se puede editar, ya fue exportado');
        }

        $recibo_detalle->update([
            'recibo_id' => $request->recibo_id,
            'concepto' => $request->concepto,
            'valor' => $request->valor
        ]);

        return redirect()->route('recibo_detalles.index')
            ->with('success','Detalle actualizado');
    }


    public function destroy(ReciboDetalle $recibo_detalle)
    {
        // 🔴 (opcional pero recomendado)
        if ($recibo_detalle->recibo && $recibo_detalle->recibo->export_batch_id) {
            return back()->with('error','No se puede eliminar, ya fue exportado');
        }

        $recibo_detalle->delete();

        return redirect()->route('recibo_detalles.index')
            ->with('success','Detalle eliminado');
            
    }
}