<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="AfiliadoResource",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="numero_documento", type="string"),
 *     @OA\Property(property="primer_nombre", type="string"),
 *     @OA\Property(property="primer_apellido", type="string"),
 *     @OA\Property(property="estado", type="boolean")
 * )
 */
class AfiliadoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'empresa_id' => $this->empresa_id,
            'numero_documento' => $this->numero_documento,
            'primer_nombre' => $this->primer_nombre,
            'segundo_nombre' => $this->segundo_nombre,
            'primer_apellido' => $this->primer_apellido,
            'segundo_apellido' => $this->segundo_apellido,
            'fecha_nacimiento' => $this->fecha_nacimiento?->format('Y-m-d'),
            'sexo' => $this->sexo,
            'correo' => $this->correo,
            'telefono' => $this->telefono,
            'direccion' => $this->direccion,
            'ciudad' => $this->ciudad,
            'estado' => $this->estado,
            'created_at' => $this->created_at,
            'empresa_laboral' => $this->whenLoaded('empresaLaboral'),
            'asesor' => $this->whenLoaded('asesor'),
        ];
    }
}
