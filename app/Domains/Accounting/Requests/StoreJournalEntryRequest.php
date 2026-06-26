<?php

namespace App\Domains\Accounting\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJournalEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha' => 'required|date|date_format:Y-m-d',
            'descripcion' => 'nullable|string|max:500',
            'referencia_documento' => 'nullable|string|max:100',
            'tipo_documento' => 'nullable|string|in:factura,recibo,comprobante,otros',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|integer|exists:chart_of_accounts,id',
            'lines.*.descripcion' => 'nullable|string|max:250',
            'lines.*.tipo_movimiento' => 'required|in:debito,credito',
            'lines.*.valor' => 'required|numeric|min:0.01|max:999999999.99',
            'lines.*.centro_costo_id' => 'nullable|integer|exists:chart_of_accounts,id',
        ];
    }

    public function messages(): array
    {
        return [
            'fecha.required' => 'La fecha es requerida',
            'fecha.date' => 'La fecha debe ser válida',
            'lines.required' => 'El asiento debe tener al menos 2 líneas',
            'lines.min' => 'El asiento debe tener al menos 2 líneas',
            'lines.*.account_id.required' => 'Cada línea debe tener una cuenta',
            'lines.*.account_id.exists' => 'La cuenta especificada no existe',
            'lines.*.tipo_movimiento.required' => 'El tipo de movimiento es requerido',
            'lines.*.tipo_movimiento.in' => 'El tipo debe ser débito o crédito',
            'lines.*.valor.required' => 'El valor es requerido',
            'lines.*.valor.min' => 'El valor debe ser mayor a 0',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Agregar empresa_id automáticamente
        $this->merge([
            'empresa_id' => session('empresa_id'),
        ]);
    }
}
