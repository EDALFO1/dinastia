<?php

namespace App\Domains\Accounting\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJournalEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha' => 'sometimes|date|date_format:Y-m-d',
            'descripcion' => 'nullable|string|max:500',
            'referencia_documento' => 'nullable|string|max:100',
            'tipo_documento' => 'nullable|string|in:factura,recibo,comprobante,otros',
            'lines' => 'sometimes|array|min:2',
            'lines.*.account_id' => 'required_with:lines|integer|exists:chart_of_accounts,id',
            'lines.*.descripcion' => 'nullable|string|max:250',
            'lines.*.tipo_movimiento' => 'required_with:lines|in:debito,credito',
            'lines.*.valor' => 'required_with:lines|numeric|min:0.01|max:999999999.99',
            'lines.*.centro_costo_id' => 'nullable|integer|exists:chart_of_accounts,id',
        ];
    }

    public function messages(): array
    {
        return [
            'fecha.date' => 'La fecha debe ser válida',
            'lines.min' => 'El asiento debe tener al menos 2 líneas',
            'lines.*.account_id.exists' => 'La cuenta especificada no existe',
            'lines.*.valor.min' => 'El valor debe ser mayor a 0',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'empresa_id' => session('empresa_id'),
        ]);
    }
}
