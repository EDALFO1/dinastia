<?php

namespace App\Http\Controllers;

use App\Models\Nota;
use Illuminate\Http\Request;

class NotaController extends Controller
{
    public function index(Request $request)
    {
        $titulo = 'Notas y Tareas';

        $query = Nota::with('creadoPor')->latest();

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        $notas = $query->paginate(25)->withQueryString();

        $stats = [
            'pendientes'  => Nota::where('estado', 'pendiente')->count(),
            'en_proceso'  => Nota::where('estado', 'en_proceso')->count(),
            'vencidas'    => Nota::whereNotIn('estado', ['resuelto', 'cancelado'])
                                 ->whereNotNull('fecha_vencimiento')
                                 ->whereDate('fecha_vencimiento', '<', today())
                                 ->count(),
            'resueltas'   => Nota::where('estado', 'resuelto')
                                 ->whereDate('fecha_resuelto', '>=', today()->startOfWeek())
                                 ->count(),
        ];

        $tipos   = Nota::tipos();
        $estados = Nota::estados();

        return view('modules.notas.index',
            compact('titulo', 'notas', 'stats', 'tipos', 'estados'));
    }

    public function create()
    {
        $titulo  = 'Nueva Nota';
        $nota    = new Nota();
        $tipos   = Nota::tipos();
        $estados = Nota::estados();

        return view('modules.notas.create', compact('titulo', 'nota', 'tipos', 'estados'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo'           => ['required', 'string', 'max:200'],
            'descripcion'      => ['nullable', 'string'],
            'tipo'             => ['required', 'in:' . implode(',', array_keys(Nota::tipos()))],
            'estado'           => ['required', 'in:' . implode(',', array_keys(Nota::estados()))],
            'fecha_vencimiento'=> ['nullable', 'date'],
        ]);

        Nota::create($request->only(
            'titulo', 'descripcion', 'tipo', 'estado', 'fecha_vencimiento'
        ));

        return redirect()->route('notas.index')
            ->with('success', 'Nota creada correctamente.');
    }

    public function edit(Nota $nota)
    {
        $titulo  = 'Editar Nota';
        $tipos   = Nota::tipos();
        $estados = Nota::estados();

        return view('modules.notas.edit', compact('titulo', 'nota', 'tipos', 'estados'));
    }

    public function update(Request $request, Nota $nota)
    {
        $request->validate([
            'titulo'           => ['required', 'string', 'max:200'],
            'descripcion'      => ['nullable', 'string'],
            'tipo'             => ['required', 'in:' . implode(',', array_keys(Nota::tipos()))],
            'estado'           => ['required', 'in:' . implode(',', array_keys(Nota::estados()))],
            'fecha_vencimiento'=> ['nullable', 'date'],
        ]);

        $data = $request->only('titulo', 'descripcion', 'tipo', 'estado', 'fecha_vencimiento');

        // Registrar quién y cuándo resolvió
        if ($nota->estado !== 'resuelto' && $request->estado === 'resuelto') {
            $data['fecha_resuelto']  = now();
            $data['resuelto_por_id'] = auth()->id();
        }

        // Limpiar campo al reabrir
        if (in_array($request->estado, ['pendiente', 'en_proceso'])) {
            $data['fecha_resuelto']  = null;
            $data['resuelto_por_id'] = null;
        }

        $nota->update($data);

        return redirect()->route('notas.index')
            ->with('success', 'Nota actualizada correctamente.');
    }

    public function destroy(Nota $nota)
    {
        $nota->delete();

        return redirect()->route('notas.index')
            ->with('success', 'Nota eliminada.');
    }

    // Acción rápida: marcar como resuelto desde la lista
    public function resolver(Nota $nota)
    {
        $nota->update([
            'estado'           => 'resuelto',
            'fecha_resuelto'   => now(),
            'resuelto_por_id'  => auth()->id(),
        ]);

        return back()->with('success', "«{$nota->titulo}» marcada como resuelta.");
    }

    // Acción rápida: reabrir a pendiente
    public function reabrir(Nota $nota)
    {
        $nota->update([
            'estado'           => 'pendiente',
            'fecha_resuelto'   => null,
            'resuelto_por_id'  => null,
        ]);

        return back()->with('success', "«{$nota->titulo}» reabierta.");
    }
}
