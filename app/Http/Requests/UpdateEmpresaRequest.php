<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmpresaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $empresaId = $this->route('empresa') ?? $this->route('id');

        return [
            'nombre' => [
                'required',
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9 ]+$/',
                'max:255'
            ],

            'nit' => [
                'required',
                'regex:/^[0-9]+$/',
                'max:20',
                'unique:empresas,nit,' . $empresaId
            ],

            'direccion' => 'nullable|string|max:255',

            'telefono' => [
                'nullable',
                'regex:/^[0-9]+$/',
                'max:20'
            ],

            'email' => 'nullable|email|max:255|unique:empresas,email,' . $empresaId,
        ];
    }
}