<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Scopes\EmpresaScope;

class Recibo extends BaseModel
{
    use HasFactory;
    protected $table = 'recibos';

    protected $fillable = [
        'empresa_id',
        'numero',
        'fecha',
        'afiliado_id',
        'dias_liquidar',
        'ibc',

        'valor_eps',
        'valor_arl',
        'valor_pension',
        'valor_caja',

        'valor_admon',
        'valor_servicios',

        'total',

        'novedad',
        'fecha_retiro',

        'export_batch_id'
    ];
    protected static function booted(): void
    {
        // Auto asignar empresa
        static::creating(function ($model) {
            if (session()->has('empresa_id')) {
                $model->empresa_id = session('empresa_id');
            }
        });

    }

    public function afiliado()
    {
        return $this->belongsTo(Afiliado::class);
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function detalles()
    {
        return $this->hasMany(ReciboDetalle::class);
    }
    

}
