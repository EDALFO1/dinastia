<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    protected $table = 'empresas';

    protected $fillable = [
        'nombre',
        'nit',
        'direccion',
        'telefono',
        'email',
        'estado',
        'user_id'
    ];

    public static function rules($id = null)
    {
        return [

            'nombre' => [
                'required',
                'string',
                'max:255'
            ],

            'nit' => [
                'required',
                'string',
                'max:20'
            ],

            'direccion' => [
                'nullable',
                'string',
                'max:255'
            ],

            'telefono' => [
                'nullable',
                'string',
                'max:20'
            ],

            'email' => [
                'nullable',
                'email',
                'max:255'
            ],

            'estado' => [
                'nullable',
                'boolean'
            ],

        ];
    }
    public function usuarios()
    {
        return $this->belongsToMany(User::class);
    }

    public function modulos()
    {
        return $this->belongsToMany(Modulo::class, 'empresa_modulo');
    }
public function claves()
{
    return $this->hasMany(EmpresaClave::class);
}
}