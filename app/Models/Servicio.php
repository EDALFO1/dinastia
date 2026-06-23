<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    protected $table = 'servicios';

    protected $fillable = [
        'nombre',
        'tipo',
        'valor_base',
        'estado'
    ];

    public static function rules()
    {
        return [

            'nombre' => [
                'required',
                'string',
                'max:255'
            ],

            'tipo' => [
                'nullable',
                'string',
                'max:100'
            ],

            'valor_base' => [
                'required',
                'numeric'
            ],

        ];
    }
}