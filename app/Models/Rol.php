<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasFactory;

    protected $table = 'roles';

    protected $fillable = [
        'nombre',
        'descripcion'
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function modulos()
    {
        return $this->belongsToMany(Modulo::class, 'rol_modulo');
    }

    public static function rules($id = null)
{
    return [

        'nombre' => [
            'required',
            'string',
            'max:255',
            'unique:roles,nombre,' . $id
        ],

        'descripcion' => [
            'nullable',
            'string',
            'max:255'
        ]

    ];
}
}