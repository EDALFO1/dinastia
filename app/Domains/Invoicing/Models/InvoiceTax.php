<?php

namespace App\Domains\Invoicing\Models;

use App\Models\BaseModel;
use App\Domains\Invoicing\Enums\TaxType;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvoiceTax extends BaseModel
{
    use HasFactory;

    protected $table = 'invoice_taxes';

    protected static function newFactory()
    {
        return \Database\Factories\InvoiceTaxFactory::new();
    }

    protected $fillable = [
        'empresa_id',
        'invoice_id',
        'invoice_line_item_id',
        'tipo_impuesto',
        'porcentaje',
        'base',
        'valor',
    ];

    protected $casts = [
        'tipo_impuesto' => TaxType::class,
        'porcentaje' => 'decimal:2',
        'base' => 'decimal:2',
        'valor' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function lineItem()
    {
        return $this->belongsTo(InvoiceLineItem::class, 'invoice_line_item_id');
    }

    protected static function booted(): void
    {
        parent::booted();

        static::creating(function ($model) {
            $model->calcularValor();
        });

        static::updating(function ($model) {
            if ($model->isDirty(['base', 'porcentaje'])) {
                $model->calcularValor();
            }
        });
    }

    public function calcularValor(): void
    {
        if ($this->base && $this->porcentaje) {
            $this->valor = ($this->base * $this->porcentaje) / 100;
        }
    }
}
