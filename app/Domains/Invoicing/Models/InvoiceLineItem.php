<?php

namespace App\Domains\Invoicing\Models;

use App\Models\BaseModel;
use App\Domains\Invoicing\Enums\UnitType;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvoiceLineItem extends BaseModel
{
    use HasFactory;

    protected $table = 'invoice_line_items';

    protected $fillable = [
        'empresa_id',
        'invoice_id',
        'linea_numero',
        'descripcion',
        'cantidad',
        'unidad',
        'valor_unitario',
        'descuento',
        'valor_linea',
    ];

    protected $casts = [
        'cantidad' => 'decimal:4',
        'unidad' => UnitType::class,
        'valor_unitario' => 'decimal:2',
        'descuento' => 'decimal:2',
        'valor_linea' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function taxes()
    {
        return $this->hasMany(InvoiceTax::class);
    }

    protected static function booted(): void
    {
        parent::booted();

        static::creating(function ($model) {
            $model->calcularValorLinea();
        });

        static::updating(function ($model) {
            if ($model->isDirty(['cantidad', 'valor_unitario', 'descuento'])) {
                $model->calcularValorLinea();
            }
        });
    }

    public function calcularValorLinea(): void
    {
        $bruto = ($this->cantidad ?? 0) * ($this->valor_unitario ?? 0);
        $this->valor_linea = $bruto - ($this->descuento ?? 0);
    }
}
