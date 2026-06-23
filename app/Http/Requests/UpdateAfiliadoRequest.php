<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAfiliadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $afiliadoId = $this->route('afiliado');

        return [
            'empresa_laboral_id' => 'required|exists:empresas_laborales,id',
            'asesor_id' => 'nullable|exists:asesores,id',
            'documento_id' => 'required|exists:documentos,id',
            'subtipo_cotizante_id' => 'required|exists:subtipo_cotizantes,id',

            'numero_documento' => [
                'required',
                'regex:/^[0-9]+$/',
                Rule::unique('afiliados')
                    ->ignore($afiliadoId)
                    ->where(function ($q) {
                        return $q->where('empresa_id', session('empresa_id'));
                    }),
            ],

            'primer_nombre' => 'required|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/',
            'segundo_nombre' => 'nullable|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/',
            'primer_apellido' => 'required|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/',
            'segundo_apellido' => 'nullable|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/',

            'fecha_nacimiento' => 'required|date',
            'sexo' => ['required', Rule::in(['M','F','Otro'])],

            // ✅ AQUÍ SE AGREGA
            'estado' => 'required|boolean',

            'correo' => 'nullable|email',
            'telefono' => 'nullable|regex:/^[0-9]+$/',

            'direccion' => 'nullable|string',
            'ciudad' => 'nullable|string',

            'google_drive_folder_id' => 'nullable|url|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'numero_documento.regex' => 'El documento solo puede contener números.',
            'numero_documento.unique' => 'Este documento ya está registrado en la empresa.',
        ];
    }
}