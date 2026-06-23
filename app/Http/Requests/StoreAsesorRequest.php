<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAsesorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'documento_id' => 'required|exists:documentos,id',

            'numero_documento' => [
                'required',
                'regex:/^[0-9]+$/',
                'max:50',
                Rule::unique('asesores')
                    ->where(fn ($q) =>
                        $q->where('empresa_id', session('empresa_id'))
                    )
            ],

            'nombre' => [
                'required',
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/',
                'max:255'
            ],

            'direccion' => 'nullable|string|max:255',

            'telefono' => [
                'nullable',
                'regex:/^[0-9]+$/',
                'max:50'
            ],

            'email' => 'nullable|email|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'numero_documento.required' => 'El documento es obligatorio.',
            'numero_documento.regex' => 'El documento solo puede contener números.',
            'numero_documento.unique' => 'Este documento ya existe en esta empresa.',

            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.regex' => 'El nombre no puede contener números.',

            'telefono.regex' => 'El teléfono solo puede contener números.',
        ];
    }
}