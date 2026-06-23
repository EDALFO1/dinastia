<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Eps extends Model
{
    protected $table = 'eps';

    protected $fillable = [
        'nombre',
        'codigo',
        'porcentaje',
    ];

    public static function rules($id = null)
{
    return [

        'nombre' => [
            'required',
            'regex:/^[A-Za-zÁÉÍÓÚÜáéíóúüÑñ\s\.\(\)]+$/u',
            'max:255',
            'unique:eps,nombre,' . $id
        ],

        'codigo' => [
            'required',
            'alpha_num',
            'max:20',
            'unique:eps,codigo,' . $id
        ],

        'porcentaje' => [
            'required',
            'numeric',
            'regex:/^\d+(\.\d{1,4})?$/',
        ],
    ];
}

    public function usuarioExterno()
    {
        return $this->hasMany(UsuarioExterno::class);
    }
}
