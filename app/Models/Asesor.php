<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asesor extends BaseModel
{
    protected $table = 'asesores';

    protected $fillable = [
        'empresa_id',
        'documento_id',
        'numero_documento',
        'nombre',
        'direccion',
        'telefono',
        'email'
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function documento()
    {
        return $this->belongsTo(Documento::class);
    }

    
}