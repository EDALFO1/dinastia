<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    protected $table = 'cajas';

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
            'unique:cajas,nombre,' . $id
        ],

        'codigo' => [
            'required',
            'regex:/^[A-Za-z0-9\-]+$/',
            'max:20',
            'unique:cajas,codigo,' . $id
        ],

        'porcentaje' => [
            'required',
            'numeric',
            'regex:/^\d+(\.\d{1,2})?$/'
        ],

    ];
}
}