<?php

namespace App\Models;

class ReciboAfiliacion extends BaseModel
{
    protected $table = 'recibos_afiliacion';

    protected $fillable = [
        'empresa_id',
        'numero',
        'afiliado_id',
        'fecha',
        'concepto',
        'valor',
        'estado_pago',
        'fecha_pago',
        'notas',
    ];

    protected $casts = [
        'fecha'      => 'date',
        'fecha_pago' => 'date',
        'valor'      => 'decimal:2',
    ];

    public function afiliado()
    {
        return $this->belongsTo(Afiliado::class);
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function isPagado(): bool
    {
        return $this->estado_pago === 'pagado';
    }
}
