<?php

namespace App\Domains\Payroll\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RemisionResource;
use App\Models\Remision;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class RemisionApiController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $remisiones = Remision::with('afiliado')
            ->when($request->has('afiliado_id'), fn($q) =>
                $q->where('afiliado_id', $request->integer('afiliado_id'))
            )
            ->paginate(15);

        return RemisionResource::collection($remisiones);
    }

    public function show(Remision $remision): RemisionResource
    {
        $remision->load(['afiliado', 'detalles']);
        return new RemisionResource($remision);
    }

    public function store(Request $request): RemisionResource
    {
        $empresaId = $request->user()->current_empresa_id;

        $validated = $request->validate([
            'afiliado_id' => 'required|exists:afiliados,id',
            'fecha' => 'required|date',
            'dias_liquidar' => 'required|integer|min:1|max:30',
            'mensajeria' => 'nullable|numeric|min:0',
            'intereses' => 'nullable|numeric|min:0',
            'total' => 'required|numeric|min:0',
        ]);

        $remision = DB::transaction(function() use ($empresaId, $validated) {
            $numero = Remision::lockForUpdate()
                ->where('empresa_id', $empresaId)
                ->max('numero') + 1;

            return Remision::create(array_merge($validated, [
                'empresa_id' => $empresaId,
                'numero' => $numero,
            ]));
        });

        return (new RemisionResource($remision))
            ->response()
            ->setStatusCode(201)
            ->getData();
    }

    public function update(Request $request, Remision $remision): RemisionResource
    {
        $validated = $request->validate([
            'fecha' => 'required|date',
            'dias_liquidar' => 'required|integer|min:1|max:30',
            'mensajeria' => 'nullable|numeric|min:0',
            'intereses' => 'nullable|numeric|min:0',
            'total' => 'required|numeric|min:0',
        ]);

        $remision->update($validated);

        return new RemisionResource($remision->fresh());
    }

    public function destroy(Remision $remision): \Illuminate\Http\JsonResponse
    {
        DB::transaction(function() use ($remision) {
            $remision->detalles()->delete();
            $remision->delete();
        });

        return response()->json(['message' => 'Remisión eliminada'], 200);
    }
}
