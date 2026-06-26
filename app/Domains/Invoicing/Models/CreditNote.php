<?php

namespace App\Domains\Invoicing\Models;

use App\Models\BaseModel;
use App\Domains\Invoicing\Enums\InvoiceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CreditNote extends BaseModel
{
    use HasFactory;

    protected $table = 'credit_notes';

    protected static function newFactory()
    {
        return \Database\Factories\CreditNoteFactory::new();
    }

    protected $fillable = [
        'empresa_id',
        'numero',
        'invoice_sequence_id',
        'invoice_id',
        'razon_ajuste',
        'descripcion_ajuste',
        'fecha_emision',
        'porcentaje_descuento',
        'valor_descuento',
        'valor_impuesto_descuento',
        'observaciones',
        'estado',
        'xml_factura',
        'firma_digital',
        'uuid_dian',
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'porcentaje_descuento' => 'decimal:2',
        'valor_descuento' => 'decimal:2',
        'valor_impuesto_descuento' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function sequence()
    {
        return $this->belongsTo(InvoiceSequence::class, 'invoice_sequence_id');
    }

    public function isActive(): bool
    {
        return $this->estado === 'borrador' || $this->estado === 'enviada';
    }

    public function canEdit(): bool
    {
        return $this->estado === 'borrador';
    }

    public function toXml(): string
    {
        $generator = new \App\Domains\Invoicing\Services\InvoiceXmlGenerator();
        return $generator->generateCreditNote($this);
    }

    public function toSignedXml(string $certificatePath, string $password = ''): string
    {
        $xmlContent = $this->toXml();
        $signer = new \App\Domains\Invoicing\Services\XmlSigner();

        try {
            $signedXml = $signer->sign($xmlContent, $certificatePath, $password);
            $this->update([
                'xml_factura' => $signedXml,
                'firma_digital' => $signer->getSignatureInfo($signedXml),
            ]);
            return $signedXml;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
