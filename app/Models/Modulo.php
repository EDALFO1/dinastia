<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Modulo extends Model
{
    protected $table = 'modulos';

    protected $fillable = [
        'slug',
        'nombre',
        'descripcion',
        'grupo',
        'orden',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'rol_modulo');
    }

    public function empresas()
    {
        return $this->belongsToMany(Empresa::class, 'empresa_modulo');
    }
}
