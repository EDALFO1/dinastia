<?php

namespace App\Domains\Invoicing\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceSequenceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'numero_inicial' => $this->numero_inicial,
            'numero_final' => $this->numero_final,
            'numero_actual' => $this->numero_actual,
            'estado' => $this->estado,
            'fecha_vigencia' => $this->fecha_vigencia->format('Y-m-d'),
            'prefijo' => $this->prefijo,
        ];
    }
}
