<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    use HasFactory;

    protected $table = 'documentos';

    protected $fillable = [
        'nombre',
        'codigo'
    ];

    public static function rules($id = null)
{
    return [

        'nombre' => [
            'required',
            'string',
            'max:255',
            'unique:documentos,nombre,' . $id
        ],

        'codigo' => [
            'required',
            'alpha_num',
            'max:10',
            'unique:documentos,codigo,' . $id
        ]

    ];
}
}