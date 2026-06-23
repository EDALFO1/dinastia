<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmpresaLaboralRequest extends FormRequest
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
                    ->ignore($this->empresa_laboral->id)
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

            'contacto' => [
    'required',
    'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/',
    'max:255'
],

            'estado' => 'nullable|boolean',
            'email' => 'nullable|email|max:255',
            
        ];
    }
}