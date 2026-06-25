<?php

namespace App\Domains\Invoicing\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvoiceSequence extends BaseModel
{
    use HasFactory;

    protected $table = 'invoice_sequences';

    protected $fillable = [
        'empresa_id',
        'numero_resolucion',
        'tipo_factura',
        'rango_inicio',
        'rango_fin',
        'proximo_numero',
        'fecha_vigencia_inicio',
        'fecha_vigencia_fin',
        'estado',
    ];

    protected $casts = [
        'fecha_vigencia_inicio' => 'date',
        'fecha_vigencia_fin' => 'date',
    ];

    public function getNextNumber(): int
    {
        return $this->lockForUpdate()->increment('proximo_numero');
    }

    public function isActive(): bool
    {
        $now = now()->toDateString();
        return $this->estado === 'activa'
            && $now >= $this->fecha_vigencia_inicio->toDateString()
            && $now <= $this->fecha_vigencia_fin->toDateString();
    }

    public function getRangeStatus(): float
    {
        $total = $this->rango_fin - $this->rango_inicio + 1;
        $used = $this->proximo_numero - $this->rango_inicio;
        return $total > 0 ? ($used / $total) * 100 : 0;
    }

    public function isWithinRange(): bool
    {
        return $this->proximo_numero <= $this->rango_fin;
    }
}
