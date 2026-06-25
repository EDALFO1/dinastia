<?php

namespace App\Domains\Invoicing\Models;

use App\Models\BaseModel;
use App\Domains\Invoicing\Enums\InvoiceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends BaseModel
{
    use HasFactory;

    protected $table = 'invoices';

    protected $fillable = [
        'empresa_id',
        'numero',
        'invoice_sequence_id',
        'tipo_documento',
        'cliente_nit',
        'cliente_nombre',
        'fecha_emision',
        'fecha_vencimiento',
        'subtotal',
        'descuento',
        'total_impuestos',
        'total',
        'observaciones',
        'estado',
        'xml_factura',
        'firma_digital',
        'uuid_dian',
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'fecha_vencimiento' => 'date',
        'tipo_documento' => InvoiceType::class,
        'subtotal' => 'decimal:2',
        'descuento' => 'decimal:2',
        'total_impuestos' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function sequence()
    {
        return $this->belongsTo(InvoiceSequence::class, 'invoice_sequence_id');
    }

    public function lineItems()
    {
        return $this->hasMany(InvoiceLineItem::class);
    }

    public function taxes()
    {
        return $this->hasMany(InvoiceTax::class);
    }

    public function isActive(): bool
    {
        return $this->estado === 'borrador' || $this->estado === 'enviada';
    }

    public function canEdit(): bool
    {
        return $this->estado === 'borrador';
    }

    public function calculateTotals(): void
    {
        $this->subtotal = $this->lineItems()->sum('valor_linea');
        $this->total_impuestos = $this->taxes()->sum('valor');
        $this->total = $this->subtotal - $this->descuento + $this->total_impuestos;
    }
}
