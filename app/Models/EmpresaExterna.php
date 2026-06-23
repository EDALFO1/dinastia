<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmpresaExterna extends Model
{
    protected $table = 'empresa_externas';

    protected $fillable = [
        'documento_id',
        'numero',
        'nombre',
        'direccion',
        'telefono',
        'contacto',
        'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    public function documento()
    {
        return $this->belongsTo(Documento::class);
    }
}
