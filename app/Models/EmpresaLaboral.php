<?php

namespace App\Models;
use App\Scopes\EmpresaScope;

use Illuminate\Database\Eloquent\Model;

class EmpresaLaboral extends BaseModel
{
    protected $table = 'empresas_laborales';

    protected $fillable = [
    'empresa_id',
    'documento_id',
    'numero_documento',
    'nombre',
    'direccion',
    'telefono',
    'contacto',
    'email', // ðŸ”¥ AGREGADO
    'estado'
];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function documento()
    {
        return $this->belongsTo(Documento::class);
    }

    protected static function booted(): void
    {
        // Auto asignar empresa
        static::creating(function ($model) {
            if (session()->has('empresa_id')) {
                $model->empresa_id = session('empresa_id');
            }
        });

    }
}