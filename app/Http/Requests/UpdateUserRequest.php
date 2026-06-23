<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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

            'name' => [
                'required',
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/',
                'max:255'
            ],

            'email' => 'required|email|max:255',

            // 🔥 PASSWORD OPCIONAL EN UPDATE
            'password' => 'nullable|min:6',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.regex' => 'El nombre no puede contener números.',
        ];
    }
}