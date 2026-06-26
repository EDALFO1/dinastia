<?php

namespace App\Domains\Invoicing\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DebitNote extends BaseModel
{
    use HasFactory;

    protected $table = 'debit_notes';

    protected static function newFactory()
    {
        return \Database\Factories\DebitNoteFactory::new();
    }

    protected $fillable = [
        'empresa_id',
        'numero',
        'invoice_sequence_id',
        'invoice_id',
        'razon_ajuste',
        'descripcion_ajuste',
        'fecha_emision',
        'porcentaje_adicion',
        'valor_adicion',
        'valor_impuesto_adicion',
        'observaciones',
        'estado',
        'xml_factura',
        'firma_digital',
        'uuid_dian',
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'porcentaje_adicion' => 'decimal:2',
        'valor_adicion' => 'decimal:2',
        'valor_impuesto_adicion' => 'decimal:2',
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
        return $generator->generateDebitNote($this);
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
