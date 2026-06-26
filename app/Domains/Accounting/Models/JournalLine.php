<?php

namespace App\Domains\Accounting\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalLine extends BaseModel
{
    use HasFactory;

    protected $table = 'journal_lines';

    protected $fillable = [
        'empresa_id',
        'journal_entry_id',
        'account_id',
        'descripcion',
        'tipo_movimiento',
        'valor',
        'centro_costo_id',
        'referencia_documento',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
    ];

    /**
     * Relación con asiento
     */
    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    /**
     * Relación con cuenta
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccounts::class, 'account_id');
    }

    /**
     * Validar que el tipo de movimiento sea válido
     */
    public function isValidMovement(): bool
    {
        return in_array($this->tipo_movimiento, ['debito', 'credito']);
    }

    /**
     * Obtener el valor con signo según el tipo de movimiento
     */
    public function getSignedValue(): float
    {
        return $this->tipo_movimiento === 'debito' ? $this->valor : -$this->valor;
    }
}
