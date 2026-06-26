<?php

namespace App\Domains\Invoicing\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvoiceAuditLog extends BaseModel
{
    use HasFactory;

    protected $table = 'invoice_audit_logs';
    public $timestamps = false;

    protected $fillable = [
        'empresa_id',
        'invoice_id',
        'tipo_documento', // 'factura', 'nota_credito', 'nota_debito'
        'documento_id',
        'evento',
        'descripcion',
        'usuario_id',
        'ip_address',
        'datos_anteriores',
        'datos_nuevos',
        'timestamp',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'datos_anteriores' => 'json',
        'datos_nuevos' => 'json',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function usuario()
    {
        return $this->belongsTo(\App\Models\User::class, 'usuario_id');
    }

    /**
     * Log an event
     */
    public static function logEvent(
        string $evento,
        int $empresaId,
        int $invoiceId,
        string $tipoDocumento = 'factura',
        ?int $documentoId = null,
        string $descripcion = '',
        ?array $datosAnteriores = null,
        ?array $datosNuevos = null,
        ?string $ipAddress = null
    ): self {
        return self::create([
            'empresa_id' => $empresaId,
            'invoice_id' => $invoiceId,
            'tipo_documento' => $tipoDocumento,
            'documento_id' => $documentoId,
            'evento' => $evento,
            'descripcion' => $descripcion,
            'usuario_id' => auth()->id(),
            'ip_address' => $ipAddress ?? request()->ip(),
            'datos_anteriores' => $datosAnteriores,
            'datos_nuevos' => $datosNuevos,
            'timestamp' => now(),
        ]);
    }

    /**
     * Get events for a specific invoice
     */
    public static function getInvoiceHistory(int $invoiceId)
    {
        return self::where('invoice_id', $invoiceId)
            ->orderBy('timestamp', 'desc')
            ->get();
    }
}
