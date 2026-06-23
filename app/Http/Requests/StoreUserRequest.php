<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'empresa_id' => 'required|array',
            'empresa_id.*' => 'exists:empresas,id',

            'rol_id' => 'required|exists:roles,id',

            // 🔥 NOMBRE SIN NÚMEROS
            'name' => [
                'required',
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/',
                'max:255'
            ],

            'email' => 'required|email|max:255',

            // 🔥 PASSWORD OBLIGATORIO
            'password' => 'required|min:6',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.regex' => 'El nombre no puede contener números.',

            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',

            'email.required' => 'El email es obligatorio.',
        ];
    }
}