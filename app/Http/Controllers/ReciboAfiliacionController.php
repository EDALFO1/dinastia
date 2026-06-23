<?php

namespace App\Http\Controllers;

use App\Models\Afiliado;
use App\Models\Empresa;
use App\Models\ReciboAfiliacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReciboAfiliacionController extends Controller
{
    public function index(Request $request)
    {
        $query = ReciboAfiliacion::with('afiliado')
            ->orderBy('numero', 'desc');

        if ($request->filled('estado')) {
            $query->where('estado_pago', $request->estado);
        }

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->whereHas('afiliado', function ($q) use ($buscar) {
                $q->where('primer_nombre', 'like', "%$buscar%")
                  ->orWhere('primer_apellido', 'like', "%$buscar%")
                  ->orWhere('numero_documento', 'like', "%$buscar%");
            });
        }

        $recibos  = $query->paginate(15)->withQueryString();
        $pendientes = ReciboAfiliacion::where('estado_pago', 'pendiente')->count();

        return view('modules.recibos_afiliacion.index', compact('recibos', 'pendientes'));
    }

    public function create()
    {
        return view('modules.recibos_afiliacion.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'afiliado_id' => 'required|exists:afiliados,id',
            'fecha'       => 'required|date',
            'concepto'    => 'required|string|max:500',
            'valor'       => 'required|numeric|min:0',
            'notas'       => 'nullable|string|max:1000',
        ]);

        $empresaId = session('empresa_id');

        DB::beginTransaction();

        try {
            $numero = ReciboAfiliacion::lockForUpdate()
                ->where('empresa_id', $empresaId)
                ->max('numero') + 1;

            $recibo = ReciboAfiliacion::create([
                'empresa_id'  => $empresaId,
                'numero'      => $numero,
                'afiliado_id' => $request->afiliado_id,
                'fecha'       => $request->fecha,
                'concepto'    => $request->concepto,
                'valor'       => $request->valor,
                'estado_pago' => 'pendiente',
                'notas'       => $request->notas,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al guardar el recibo de afiliación.');
        }

        return redirect()->route('recibos-afiliacion.index')
            ->with('success', 'Recibo de afiliación creado correctamente.');
    }

    public function imprimir(ReciboAfiliacion $recibos_afiliacion)
    {
        $recibo  = $recibos_afiliacion->load('afiliado');
        $empresa = Empresa::find(session('empresa_id'));

        return view('modules.recibos_afiliacion.imprimir', compact('recibo', 'empresa'));
    }

    public function pagar(ReciboAfiliacion $recibos_afiliacion)
    {
        if ($recibos_afiliacion->isPagado()) {
            return back()->with('error', 'Este recibo ya fue marcado como pagado.');
        }

        $recibos_afiliacion->update([
            'estado_pago' => 'pagado',
            'fecha_pago'  => now()->toDateString(),
        ]);

        return back()->with('success', 'Recibo marcado como pagado.');
    }

    public function destroy(ReciboAfiliacion $recibos_afiliacion)
    {
        if ($recibos_afiliacion->isPagado()) {
            return back()->with('error', 'No se puede eliminar un recibo ya pagado.');
        }

        $recibos_afiliacion->delete();

        return redirect()->route('recibos-afiliacion.index')
            ->with('success', 'Recibo eliminado.');
    }
}
