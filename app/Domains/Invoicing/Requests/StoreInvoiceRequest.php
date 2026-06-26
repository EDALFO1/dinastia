<?php

namespace App\Domains\Invoicing\Requests;

use App\Domains\Invoicing\Enums\InvoiceType;
use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invoice_sequence_id' => 'required|exists:invoice_sequences,id',
            'tipo_documento' => 'required|in:' . implode(',', array_column(InvoiceType::cases(), 'value')),
            'cliente_nit' => 'required|string|max:20',
            'cliente_nombre' => 'required|string|max:255',
            'fecha_emision' => 'required|date|date_format:Y-m-d',
            'fecha_vencimiento' => 'required|date|date_format:Y-m-d|after_or_equal:fecha_emision',
            'descuento' => 'nullable|numeric|min:0',
            'observaciones' => 'nullable|string|max:1000',
            'line_items' => 'required|array|min:1',
            'line_items.*.description' => 'required|string',
            'line_items.*.quantity' => 'required|numeric|min:0.01',
            'line_items.*.unit' => 'required|in:UNIDAD,KILOGRAMO,GRAMO,METRO,CENTIMETRO,HORA,MINUTO,LITRO,MILILITRO',
            'line_items.*.unit_price' => 'required|numeric|min:0',
            'taxes' => 'required|array|min:1',
            'taxes.*.tipo_impuesto' => 'required|in:IVA,IMPUESTO_CONSUMO,IMPUESTO_NACIONAL',
            'taxes.*.porcentaje' => 'required|numeric|min:0|max:100',
        ];
    }
}
