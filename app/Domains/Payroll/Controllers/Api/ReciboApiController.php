<?php

namespace App\Domains\Payroll\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReciboResource;
use App\Models\Recibo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ReciboApiController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $recibos = Recibo::with('afiliado')
            ->when($request->has('afiliado_id'), fn($q) =>
                $q->where('afiliado_id', $request->integer('afiliado_id'))
            )
            ->when($request->has('fecha_from'), fn($q) =>
                $q->whereDate('fecha', '>=', $request->date('fecha_from'))
            )
            ->when($request->has('fecha_to'), fn($q) =>
                $q->whereDate('fecha', '<=', $request->date('fecha_to'))
            )
            ->paginate(15);

        return ReciboResource::collection($recibos);
    }

    public function show(Recibo $recibo): ReciboResource
    {
        $recibo->load(['afiliado', 'detalles']);
        return new ReciboResource($recibo);
    }

    public function store(Request $request): JsonResponse
    {
        $empresaId = $request->user()->current_empresa_id;

        $validated = $request->validate([
            'afiliado_id' => 'required|exists:afiliados,id',
            'fecha' => 'required|date',
            'dias_liquidar' => 'required|integer|min:1|max:30',
            'ibc' => 'required|numeric|min:0',
            'valor_eps' => 'nullable|numeric|min:0',
            'valor_arl' => 'nullable|numeric|min:0',
            'valor_pension' => 'nullable|numeric|min:0',
            'valor_caja' => 'nullable|numeric|min:0',
            'valor_admon' => 'nullable|numeric|min:0',
            'valor_servicios' => 'nullable|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'novedad' => 'nullable|in:Ingreso,Retiro',
            'fecha_retiro' => 'nullable|date|required_if:novedad,Retiro',
        ]);

        $recibo = DB::transaction(function() use ($empresaId, $validated) {
            $numero = Recibo::lockForUpdate()
                ->where('empresa_id', $empresaId)
                ->max('numero') + 1;

            return Recibo::create(array_merge($validated, [
                'empresa_id' => $empresaId,
                'numero' => $numero,
            ]));
        });

        return (new ReciboResource($recibo))
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, Recibo $recibo): JsonResponse|ReciboResource
    {
        if ($recibo->export_batch_id !== null) {
            return response()->json([
                'message' => 'Recibo exportado, no se puede modificar',
            ], 422);
        }

        $validated = $request->validate([
            'afiliado_id' => 'required|exists:afiliados,id',
            'fecha' => 'required|date',
            'dias_liquidar' => 'required|integer|min:1|max:30',
            'ibc' => 'required|numeric|min:0',
            'valor_eps' => 'nullable|numeric|min:0',
            'valor_arl' => 'nullable|numeric|min:0',
            'valor_pension' => 'nullable|numeric|min:0',
            'valor_caja' => 'nullable|numeric|min:0',
            'valor_admon' => 'nullable|numeric|min:0',
            'valor_servicios' => 'nullable|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'novedad' => 'nullable|in:Ingreso,Retiro',
            'fecha_retiro' => 'nullable|date|required_if:novedad,Retiro',
        ]);

        $recibo->update($validated);

        return new ReciboResource($recibo->fresh());
    }

    public function destroy(Recibo $recibo): \Illuminate\Http\JsonResponse
    {
        if ($recibo->export_batch_id !== null) {
            return response()->json([
                'message' => 'Recibo exportado, no se puede eliminar',
            ], 422);
        }

        $recibo->delete();

        return response()->json(['message' => 'Recibo eliminado'], 200);
    }
}
