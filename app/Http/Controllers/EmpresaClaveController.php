<?php

namespace App\Http\Controllers;

use App\Models\EmpresaClave;
use App\Models\ServicioExterno;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;



class EmpresaClaveController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
{
    $empresaId = session('empresa_id');

    abort_if(!$empresaId, 403, 'Debe seleccionar una empresa antes de continuar.');

    $claves = EmpresaClave::with('servicio')
        ->where('empresa_id', $empresaId)
        ->latest()
        ->paginate(20);

    return view('modules.empresa_claves.index', compact('claves'));
}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
{
    abort_if(!session('empresa_id'), 403, 'Debe seleccionar una empresa antes de continuar.');

    $servicios = ServicioExterno::orderBy('nombre')
        ->pluck('nombre', 'id');

    return view('modules.empresa_claves.create', compact('servicios'));
}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    $request->validate([
        'servicio_externo_id' => 'required|exists:servicios_externos,id',
        'usuario' => 'nullable|string|max:255',
        'correo_registrado' => 'nullable|string|max:255',
        'password' => 'nullable|string|max:255',
    ]);

    EmpresaClave::create([
        'empresa_id' => session('empresa_id'),
        'servicio_externo_id' => $request->servicio_externo_id,
        'usuario' => $request->usuario,
        'correo_registrado' => $request->correo_registrado,
        'password' => $request->password  ? Crypt::encryptString($request->password)  : null,
    ]);

    return redirect()
        ->route('empresa-claves.index')
        ->with('success', 'Clave registrada correctamente.');
}

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EmpresaClave $empresaClave)
{
    if ($empresaClave->empresa_id != session('empresa_id')) {
        abort(403);
    }

    $servicios = ServicioExterno::orderBy('nombre')
        ->pluck('nombre', 'id');

    return view('modules.empresa_claves.edit', compact(
        'empresaClave',
        'servicios'
    ));
}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmpresaClave $empresaClave)
{
    if ($empresaClave->empresa_id != session('empresa_id')) {
        abort(403);
    }

    $request->validate([
        'servicio_externo_id' => 'required|exists:servicios_externos,id',
        'usuario' => 'nullable|string|max:255',
        'correo_registrado' => 'nullable|string|max:255',
        'password' => 'nullable|string|max:255',
    ]);

    $empresaClave->update([
        'servicio_externo_id' => $request->servicio_externo_id,
        'usuario' => $request->usuario,
        'correo_registrado' => $request->correo_registrado,
        'password' => $request->password
    ? Crypt::encryptString($request->password)
    : $empresaClave->password,
    ]);

    return redirect()
        ->route('empresa-claves.index')
        ->with('success', 'Clave actualizada correctamente.');
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmpresaClave $empresaClave)
{
    if ($empresaClave->empresa_id != session('empresa_id')) {
        abort(403);
    }

    $empresaClave->delete();

    return back()->with('success', 'Clave eliminada.');
}
}
