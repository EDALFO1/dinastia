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
    /**
     * @OA\Get(
     *     path="/afiliados",
     *     tags={"Afiliados"},
     *     summary="Listar afiliados",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="X-Empresa-ID",
     *         in="header",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         @OA\Schema(type="string"),
     *         description="Buscar por nombre o documento"
     *     ),
     *     @OA\Parameter(
     *         name="estado",
     *         in="query",
     *         @OA\Schema(type="boolean"),
     *         description="Filtrar por estado (activo/inactivo)"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista paginada de afiliados",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/AfiliadoResource")),
     *             @OA\Property(property="meta", type="object"),
     *             @OA\Property(property="links", type="object")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/afiliados/{id}",
     *     tags={"Afiliados"},
     *     summary="Obtener detalle de afiliado",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Empresa-ID", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Detalle de afiliado", @OA\JsonContent(ref="#/components/schemas/AfiliadoResource")),
     *     @OA\Response(response=404, description="Afiliado no encontrado")
     * )
     */
    public function show(Afiliado $afiliado): AfiliadoResource
    {
        $afiliado->load(['empresaLaboral', 'asesor', 'documento', 'subtipoCotizante']);
        return new AfiliadoResource($afiliado);
    }

    /**
     * @OA\Post(
     *     path="/afiliados",
     *     tags={"Afiliados"},
     *     summary="Crear nuevo afiliado",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Empresa-ID", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"empresa_laboral_id","documento_id","subtipo_cotizante_id","numero_documento","primer_nombre","primer_apellido","fecha_nacimiento","sexo"},
     *             @OA\Property(property="empresa_laboral_id", type="integer"),
     *             @OA\Property(property="documento_id", type="integer"),
     *             @OA\Property(property="subtipo_cotizante_id", type="integer"),
     *             @OA\Property(property="numero_documento", type="string"),
     *             @OA\Property(property="primer_nombre", type="string"),
     *             @OA\Property(property="primer_apellido", type="string"),
     *             @OA\Property(property="fecha_nacimiento", type="string", format="date"),
     *             @OA\Property(property="sexo", type="string", enum={"M","F","Otro"})
     *         )
     *     ),
     *     @OA\Response(response=201, description="Afiliado creado", @OA\JsonContent(ref="#/components/schemas/AfiliadoResource")),
     *     @OA\Response(response=422, description="Validación fallida")
     * )
     */
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

    /**
     * @OA\Put(
     *     path="/afiliados/{id}",
     *     tags={"Afiliados"},
     *     summary="Actualizar afiliado",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Empresa-ID", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/AfiliadoResource")),
     *     @OA\Response(response=200, description="Afiliado actualizado", @OA\JsonContent(ref="#/components/schemas/AfiliadoResource")),
     *     @OA\Response(response=404, description="Afiliado no encontrado")
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/afiliados/{id}",
     *     tags={"Afiliados"},
     *     summary="Eliminar afiliado",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Empresa-ID", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Afiliado eliminado"),
     *     @OA\Response(response=404, description="Afiliado no encontrado")
     * )
     */
    public function destroy(Afiliado $afiliado): \Illuminate\Http\JsonResponse
    {
        $afiliado->delete();

        return response()->json(['message' => 'Afiliado eliminado'], 200);
    }

    /**
     * @OA\Get(
     *     path="/afiliados/{id}/recibos",
     *     tags={"Afiliados"},
     *     summary="Listar recibos de un afiliado",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Empresa-ID", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Lista de recibos", @OA\JsonContent(
     *         @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/ReciboResource")),
     *         @OA\Property(property="meta", type="object"),
     *         @OA\Property(property="links", type="object")
     *     ))
     * )
     */
    public function recibos(Afiliado $afiliado): AnonymousResourceCollection
    {
        $recibos = $afiliado->recibos()->with('detalles')->paginate(15);

        return ReciboResource::collection($recibos);
    }
}
