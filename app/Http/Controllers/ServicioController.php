<?php

namespace App\Http\Controllers;

use App\Models\Servicio;
use Illuminate\Http\Request;

class ServicioController extends Controller
{

    public function index()
    {

        $titulo = "Servicios";

        $servicios = Servicio::orderBy('nombre')->get();

        return view('modules.servicios.index',
            compact('titulo','servicios'));
    }


    public function create()
    {

        $titulo = "Crear Servicio";

        return view('modules.servicios.create',
            compact('titulo'));
    }


    public function store(Request $request)
    {

        $request->validate(Servicio::rules());

        Servicio::create($request->all());

        return redirect()->route('servicios.index')
            ->with('success','Servicio creado correctamente');
    }


    public function edit(Servicio $servicio)
    {

        $titulo = "Editar Servicio";

        return view('modules.servicios.edit',
            compact('titulo','servicio'));
    }


    public function update(Request $request, Servicio $servicio)
    {

        $request->validate(Servicio::rules());

        $servicio->update($request->all());

        return redirect()->route('servicios.index')
            ->with('success','Servicio actualizado');
    }


    public function destroy(Servicio $servicio)
    {

        $servicio->delete();

        return redirect()->route('servicios.index')
            ->with('success','Servicio eliminado');
    }

}