<?php

namespace App\Domains\Invoicing\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cliente_nombre' => 'nullable|string|max:255',
            'cliente_nit' => 'nullable|string|max:20',
            'fecha_vencimiento' => 'nullable|date|date_format:Y-m-d',
            'descuento' => 'nullable|numeric|min:0',
            'observaciones' => 'nullable|string|max:1000',
        ];
    }
}
