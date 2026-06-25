<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReciboResource extends JsonResource
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
            'ibc' => $this->ibc,
            'valor_eps' => $this->valor_eps,
            'valor_arl' => $this->valor_arl,
            'valor_pension' => $this->valor_pension,
            'valor_caja' => $this->valor_caja,
            'valor_admon' => $this->valor_admon,
            'valor_servicios' => $this->valor_servicios,
            'total' => $this->total,
            'novedad' => $this->novedad,
            'fecha_retiro' => $this->fecha_retiro?->format('Y-m-d'),
            'created_at' => $this->created_at,
            'afiliado' => $this->whenLoaded('afiliado'),
            'detalles' => $this->whenLoaded('detalles'),
        ];
    }
}
