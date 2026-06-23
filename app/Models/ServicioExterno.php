<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicioExterno extends Model
{
    protected $table = 'servicios_externos';

    protected $fillable = [
        'nombre',
        'url',
        'activo',
    ];

    // RELACIÓN
    public function claves()
    {
        return $this->hasMany(EmpresaClave::class);
    }
}