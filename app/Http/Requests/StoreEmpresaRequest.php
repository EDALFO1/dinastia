<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmpresaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
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
                'unique:empresas,nit'
            ],

            'direccion' => 'nullable|string|max:255',

            'telefono' => [
                'nullable',
                'regex:/^[0-9]+$/',
                'max:20'
            ],

            'email' => 'nullable|email|max:255|unique:empresas,email',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.regex' => 'El nombre contiene caracteres inválidos.',

            'nit.required' => 'El NIT es obligatorio.',
            'nit.regex' => 'El NIT solo puede contener números.',
            'nit.unique' => 'Este NIT ya está registrado.',

            'telefono.regex' => 'El teléfono solo puede contener números.',

            'email.email' => 'El email no es válido.',
            'email.unique' => 'Este email ya está registrado.',
        ];
    }
}