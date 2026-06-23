<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $table = 'users';

    protected $fillable = [
        'rol_id',
        'name',
        'email',
        'password',
        'estado'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    // 🔥 MULTIEMPRESA REAL (pivot empresa_user)
    public function empresas()
    {
        return $this->belongsToMany(Empresa::class);
    }

    // 🔥 ROL DEL USUARIO
    public function rol()
    {
        return $this->belongsTo(Rol::class);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    // 🔥 Obtener empresa activa desde sesión
    public function empresaActiva()
    {
        return $this->empresas()
            ->where('empresas.id', session('empresa_id'))
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDACIONES
    |--------------------------------------------------------------------------
    */

    public static function rules($id = null)
    {
        return [

            // 🔥 IMPORTANTE: ya no es columna en users
            'empresa_id' => [
                'required',
                'exists:empresas,id'
            ],

            'rol_id' => [
                'required',
                'exists:roles,id'
            ],

            'name' => [
                'required',
                'string',
                'max:255'
            ],

            'email' => [
                'required',
                'email',
                'max:255'
            ],

            'password' => [
                $id ? 'nullable' : 'required',
                'min:6'
            ],

            'estado' => [
                'nullable',
                'boolean'
            ]

        ];
    }
}