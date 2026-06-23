<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubtipoCotizante extends Model
{
    protected $table = 'subtipo_cotizantes';

    protected $fillable = [
        'codigo',
        'nombre'
    ];

    public static function rules($id = null)
{
    return [

        'codigo' => [
            'required',
            'string',
            'max:10',
            'unique:subtipo_cotizantes,codigo,' . $id
        ],

        'nombre' => [
            'required',
            'string',
            'max:255',
            'unique:subtipo_cotizantes,nombre,' . $id
        ]

    ];
}
}