<?php

namespace App\Domains\Invoicing\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'numero' => $this->numero,
            'invoice_sequence_id' => $this->invoice_sequence_id,
            'tipo_documento' => $this->tipo_documento->value,
            'cliente_nit' => $this->cliente_nit,
            'cliente_nombre' => $this->cliente_nombre,
            'fecha_emision' => $this->fecha_emision->format('Y-m-d'),
            'fecha_vencimiento' => $this->fecha_vencimiento->format('Y-m-d'),
            'subtotal' => (float) $this->subtotal,
            'descuento' => (float) $this->descuento,
            'total_impuestos' => (float) $this->total_impuestos,
            'total' => (float) $this->total,
            'observaciones' => $this->observaciones,
            'estado' => $this->estado,
            'uuid_dian' => $this->uuid_dian,
            'firma_digital' => $this->whenNotNull($this->firma_digital),
            'xml_factura' => $this->whenNotNull($this->xml_factura ? 'Signed' : null),
            'sequence' => new InvoiceSequenceResource($this->whenLoaded('sequence')),
            'line_items' => InvoiceLineItemResource::collection($this->whenLoaded('lineItems')),
            'taxes' => InvoiceTaxResource::collection($this->whenLoaded('taxes')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
