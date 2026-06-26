<?php

namespace App\Domains\Accounting\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class JournalEntry extends BaseModel
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'journal_entries';

    protected $fillable = [
        'empresa_id',
        'numero_asiento',
        'fecha',
        'descripcion',
        'referencia_documento',
        'tipo_documento',
        'estado',
        'usuario_creacion_id',
        'usuario_aprobacion_id',
        'fecha_aprobacion',
    ];

    protected $casts = [
        'fecha' => 'date',
        'fecha_aprobacion' => 'datetime',
    ];

    protected $attributes = [
        'estado' => 'borrador',
    ];

    protected static function newFactory()
    {
        return \Database\Factories\JournalEntryFactory::new();
    }

    /**
     * Relación con líneas de asiento
     */
    public function lines(): HasMany
    {
        return $this->hasMany(JournalLine::class, 'journal_entry_id');
    }

    /**
     * Obtener total débito
     */
    public function getTotalDebit(): float
    {
        return $this->lines()->where('tipo_movimiento', 'debito')->sum('valor');
    }

    /**
     * Obtener total crédito
     */
    public function getTotalCredit(): float
    {
        return $this->lines()->where('tipo_movimiento', 'credito')->sum('valor');
    }

    /**
     * Validar que débito = crédito
     */
    public function isBalanced(): bool
    {
        $debit = $this->getTotalDebit();
        $credit = $this->getTotalCredit();

        return abs($debit - $credit) < 0.01; // Tolerancia de centavos
    }

    /**
     * Validar integridad del asiento
     */
    public function validate(): array
    {
        $errors = [];

        if ($this->lines()->count() < 2) {
            $errors[] = 'Un asiento debe tener al menos 2 líneas';
        }

        if (!$this->isBalanced()) {
            $errors[] = 'El asiento no está balanceado (débito ≠ crédito)';
        }

        // Validar que todas las cuentas estén activas
        foreach ($this->lines as $line) {
            if (!$line->account->canHaveMovements()) {
                $errors[] = "La cuenta {$line->account->codigo} no puede tener movimientos";
            }
        }

        return $errors;
    }

    /**
     * Aprobar asiento
     */
    public function approve(int $userId): void
    {
        $errors = $this->validate();

        if (!empty($errors)) {
            throw new \Exception('No se puede aprobar el asiento: ' . implode(', ', $errors));
        }

        $this->update([
            'estado' => 'posteado',
            'usuario_aprobacion_id' => $userId,
            'fecha_aprobacion' => now(),
        ]);
    }

    /**
     * Rechazar asiento
     */
    public function reject(): void
    {
        if ($this->estado === 'posteado') {
            throw new \Exception('No se puede rechazar un asiento ya posteado');
        }

        $this->update(['estado' => 'rechazado']);
    }

    /**
     * Generar número de asiento automático
     */
    public static function generateNumber(int $empresaId): string
    {
        $count = self::where('empresa_id', $empresaId)->count() + 1;
        $date = now()->format('Ym');
        return "{$date}-" . str_pad($count, 6, '0', STR_PAD_LEFT);
    }
}
