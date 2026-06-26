<?php

namespace App\Domains\Invoicing\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceTaxResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_id' => $this->invoice_id,
            'tipo_impuesto' => $this->tipo_impuesto->value,
            'porcentaje' => (float) $this->porcentaje,
            'valor' => (float) $this->valor,
            'exento' => (bool) $this->exento,
        ];
    }
}
