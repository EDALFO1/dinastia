<?php

namespace App\Domains\Payroll\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NominaElectronica extends BaseModel
{
    use HasFactory;

    protected $table = 'nominas_electronicas';

    protected $fillable = [
        'empresa_id',
        'recibo_id',
        'numero_nomina',
        'consecutivo',
        'fecha_emision',
        'periodo_pago_inicio',
        'periodo_pago_final',
        'salario_ordinario',
        'salario_integral',
        'total_devengado',
        'total_descuentos',
        'neto_pagar',
        'estado',
        'xml_nomina',
        'firma_digital',
        'uuid_dian',
        'fecha_envio_dian',
        'respuesta_dian',
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'periodo_pago_inicio' => 'date',
        'periodo_pago_final' => 'date',
        'salario_ordinario' => 'decimal:2',
        'salario_integral' => 'decimal:2',
        'total_devengado' => 'decimal:2',
        'total_descuentos' => 'decimal:2',
        'neto_pagar' => 'decimal:2',
        'fecha_envio_dian' => 'datetime',
        'respuesta_dian' => 'json',
    ];

    public function recibo()
    {
        return $this->belongsTo(\App\Models\Recibo::class);
    }

    public function empresa()
    {
        return $this->belongsTo(\App\Models\Empresa::class);
    }

    public function afiliado()
    {
        return $this->recibo->afiliado();
    }

    /**
     * Estados posibles de la nómina
     */
    public function isActive(): bool
    {
        return in_array($this->estado, ['borrador', 'enviada']);
    }

    public function canEdit(): bool
    {
        return $this->estado === 'borrador';
    }

    public function canSign(): bool
    {
        return $this->estado === 'borrador' && !$this->xml_nomina;
    }

    public function canSendToDian(): bool
    {
        return $this->estado === 'borrador' && $this->xml_nomina && $this->firma_digital;
    }

    /**
     * Generar XML de nómina
     */
    public function generarXml(): string
    {
        $generator = new \App\Domains\Payroll\Services\NominaXmlGenerator();
        return $generator->generate($this);
    }

    /**
     * Firmar nómina
     */
    public function firmar(string $certificatePath, string $password = ''): string
    {
        $xmlContent = $this->generarXml();
        $signer = new \App\Domains\Payroll\Services\NominaXmlSigner();

        try {
            $signedXml = $signer->sign($xmlContent, $certificatePath, $password);
            $this->update([
                'xml_nomina' => $signedXml,
                'firma_digital' => $signer->getSignatureInfo($signedXml),
            ]);
            return $signedXml;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Obtener estado para display
     */
    public function getEstadoLabel(): string
    {
        return match ($this->estado) {
            'borrador' => 'Borrador',
            'enviada' => 'Enviada a DIAN',
            'aceptada' => 'Aceptada por DIAN',
            'rechazada' => 'Rechazada por DIAN',
            'error' => 'Error en envío',
            default => 'Desconocido',
        };
    }
}
