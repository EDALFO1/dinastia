<?php

namespace App\Http\Controllers;

use App\Models\ServicioExterno;
use Illuminate\Http\Request;

class ServicioExternoController extends Controller
{
    /**
     * LISTADO
     */
    public function index()
    {
        $servicios = ServicioExterno::latest()
            ->paginate(15);

        return view(
            'modules.servicios_externos.index',
            compact('servicios')
        );
    }

    /**
     * FORM CREAR
     */
    public function create()
    {
        return view(
            'modules.servicios_externos.create'
        );
    }

    /**
     * GUARDAR
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|max:255',
            'url' => 'nullable|url|max:255',
        ]);

        ServicioExterno::create([
            'nombre' => $request->nombre,
            'url' => $request->url,
            'activo' => $request->activo ?? 1,
        ]);

        return redirect()
            ->route('servicios-externos.index')
            ->with('success', 'Servicio creado correctamente.');
    }

    /**
     * FORM EDITAR
     */
    public function edit(ServicioExterno $serviciosExterno)
    {
        return view(
            'modules.servicios_externos.edit',
            compact('serviciosExterno')
        );
    }

    /**
     * ACTUALIZAR
     */
    public function update(Request $request, ServicioExterno $serviciosExterno)
    {
        $request->validate([
            'nombre' => 'required|max:255',
            'url' => 'nullable|url|max:255',
        ]);

        $serviciosExterno->update([
            'nombre' => $request->nombre,
            'url' => $request->url,
            'activo' => $request->activo ?? 1,
        ]);

        return redirect()
            ->route('servicios-externos.index')
            ->with('success', 'Servicio actualizado.');
    }

    /**
     * ELIMINAR
     */
    public function destroy(ServicioExterno $serviciosExterno)
    {
        $serviciosExterno->delete();

        return back()->with(
            'success',
            'Servicio eliminado.'
        );
    }
}