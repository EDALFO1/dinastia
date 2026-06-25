<?php

namespace App\Domains\Payroll\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AfiliadoResource;
use App\Http\Resources\ReciboResource;
use App\Models\Afiliado;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class AfiliadoApiController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $afiliados = Afiliado::with(['empresaLaboral', 'asesor'])
            ->when($request->has('estado'), fn($q) => $q->where('estado', $request->boolean('estado')))
            ->when($request->has('q'), fn($q) => $q->where(fn($query) =>
                $query->where('primer_nombre', 'like', "%{$request->query('q')}%")
                    ->orWhere('primer_apellido', 'like', "%{$request->query('q')}%")
                    ->orWhere('numero_documento', 'like', "%{$request->query('q')}%")
            ))
            ->paginate(15);

        return AfiliadoResource::collection($afiliados);
    }

    public function show(Afiliado $afiliado): AfiliadoResource
    {
        $afiliado->load(['empresaLaboral', 'asesor', 'documento', 'subtipoCotizante']);
        return new AfiliadoResource($afiliado);
    }

    public function store(Request $request): JsonResponse
    {
        $empresaId = $request->user()->current_empresa_id;

        $validated = $request->validate([
            'empresa_laboral_id' => 'required|exists:empresas_laborales,id',
            'asesor_id' => 'nullable|exists:asesores,id',
            'documento_id' => 'required|exists:documentos,id',
            'subtipo_cotizante_id' => 'required|exists:subtipo_cotizantes,id',
            'numero_documento' => [
                'required',
                'string',
                'max:50',
                Rule::unique('afiliados')->where(fn($q) => $q->where('empresa_id', $empresaId)),
            ],
            'primer_nombre' => 'required|string|max:255',
            'segundo_nombre' => 'nullable|string|max:255',
            'primer_apellido' => 'required|string|max:255',
            'segundo_apellido' => 'nullable|string|max:255',
            'fecha_nacimiento' => 'required|date',
            'sexo' => ['required', Rule::in(['M', 'F', 'Otro'])],
            'correo' => 'nullable|email',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string',
            'ciudad' => 'nullable|string',
        ]);

        $afiliado = Afiliado::create(array_merge($validated, ['empresa_id' => $empresaId]));

        return (new AfiliadoResource($afiliado))
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, Afiliado $afiliado): AfiliadoResource
    {
        $empresaId = $request->user()->current_empresa_id;

        $validated = $request->validate([
            'empresa_laboral_id' => 'required|exists:empresas_laborales,id',
            'asesor_id' => 'nullable|exists:asesores,id',
            'documento_id' => 'required|exists:documentos,id',
            'subtipo_cotizante_id' => 'required|exists:subtipo_cotizantes,id',
            'numero_documento' => [
                'required',
                'string',
                'max:50',
                Rule::unique('afiliados')->ignore($afiliado->id)->where(fn($q) =>
                    $q->where('empresa_id', $empresaId)
                ),
            ],
            'primer_nombre' => 'required|string|max:255',
            'segundo_nombre' => 'nullable|string|max:255',
            'primer_apellido' => 'required|string|max:255',
            'segundo_apellido' => 'nullable|string|max:255',
            'fecha_nacimiento' => 'required|date',
            'sexo' => ['required', Rule::in(['M', 'F', 'Otro'])],
            'correo' => 'nullable|email',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string',
            'ciudad' => 'nullable|string',
        ]);

        $afiliado->update($validated);

        return new AfiliadoResource($afiliado->fresh());
    }

    public function destroy(Afiliado $afiliado): \Illuminate\Http\JsonResponse
    {
        $afiliado->delete();

        return response()->json(['message' => 'Afiliado eliminado'], 200);
    }

    public function recibos(Afiliado $afiliado): AnonymousResourceCollection
    {
        $recibos = $afiliado->recibos()->with('detalles')->paginate(15);

        return ReciboResource::collection($recibos);
    }
}
