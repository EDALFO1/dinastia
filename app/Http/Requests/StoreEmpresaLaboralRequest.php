<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmpresaLaboralRequest extends FormRequest
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
                Rule::unique('empresas_laborales')
                    ->where(fn ($q) =>
                        $q->where('empresa_id', session('empresa_id'))
                    )
            ],

            'nombre' => [
                'required',
                'regex:/^[\pL\s0-9\.\-]+$/u',
                'max:255'
            ],

            'direccion' => 'nullable|string|max:255',

            'telefono' => [
                'nullable',
                'regex:/^[0-9]+$/',
                'max:50'
            ],

            'contacto' => [
    'required',
    'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/',
    'max:255'
],
            'email' => 'nullable|email|max:255',

            'estado' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'numero_documento.required' => 'El número de documento es obligatorio.',
            'numero_documento.regex' => 'El documento solo puede contener números.',
            'numero_documento.unique' => 'Este documento ya existe en esta empresa.',

            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.regex' => 'El nombre no puede contener números.',

            'telefono.regex' => 'El teléfono solo puede contener números.',
            'email' => 'nullable|email|max:255',
            'contacto.required' => 'El contacto es obligatorio.',
            'contacto.regex' => 'El contacto no puede contener números.',
        ];
    }
}