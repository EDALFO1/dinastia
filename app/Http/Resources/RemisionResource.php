<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RemisionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'empresa_id' => $this->empresa_id,
            'numero' => $this->numero,
            'fecha' => $this->fecha?->format('Y-m-d'),
            'afiliado_id' => $this->afiliado_id,
            'dias_liquidar' => $this->dias_liquidar,
            'mensajeria' => $this->mensajeria,
            'intereses' => $this->intereses,
            'total' => $this->total,
            'created_at' => $this->created_at,
            'afiliado' => $this->whenLoaded('afiliado'),
            'detalles' => $this->whenLoaded('detalles'),
        ];
    }
}
