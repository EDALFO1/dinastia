<?php

namespace App\Models;

class EmpresaClave extends BaseModel
{
    protected $table = 'empresa_claves';

    protected $fillable = [
        'empresa_id',
        'servicio_externo_id',
        'usuario',
        'correo_registrado',
        'password',
    ];

    // RELACIONES
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function servicio()
    {
        return $this->belongsTo(ServicioExterno::class, 'servicio_externo_id');
    }
}