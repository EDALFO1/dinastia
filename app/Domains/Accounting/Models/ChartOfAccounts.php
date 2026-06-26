<?php

namespace App\Domains\Accounting\Models;

use App\Domains\Accounting\Enums\AccountLevel;
use App\Domains\Accounting\Enums\AccountType;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChartOfAccounts extends BaseModel
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'chart_of_accounts';

    protected $fillable = [
        'empresa_id',
        'parent_id',
        'codigo',
        'nombre',
        'descripcion',
        'tipo_cuenta',
        'nivel',
        'saldo_inicial',
        'fecha_vigencia_inicio',
        'fecha_vigencia_fin',
        'estado',
        'permite_movimiento',
        'orden',
    ];

    protected $casts = [
        'tipo_cuenta' => AccountType::class,
        'nivel' => AccountLevel::class,
        'saldo_inicial' => 'decimal:2',
        'fecha_vigencia_inicio' => 'date',
        'fecha_vigencia_fin' => 'date',
        'permite_movimiento' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    protected $attributes = [
        'saldo_inicial' => 0,
        'permite_movimiento' => false,
        'estado' => 'activo',
    ];

    /**
     * Relación con padre (cuenta padre)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccounts::class, 'parent_id');
    }

    /**
     * Relación con cuentas hijo
     */
    public function children(): HasMany
    {
        return $this->hasMany(ChartOfAccounts::class, 'parent_id');
    }

    /**
     * Relación con asientos contables
     */
    public function journalLines(): HasMany
    {
        return $this->hasMany(\App\Domains\Accounting\Models\JournalLine::class, 'account_id');
    }

    /**
     * Obtener la ruta jerárquica de la cuenta
     */
    public function getPath(): string
    {
        $path = [$this->codigo];
        $parent = $this->parent;

        while ($parent) {
            array_unshift($path, $parent->codigo);
            $parent = $parent->parent;
        }

        return implode(' > ', $path);
    }

    /**
     * Obtener nombre con jerarquía
     */
    public function getFullName(): string
    {
        return "{$this->codigo} - {$this->nombre}";
    }

    /**
     * Validar si la cuenta puede tener movimientos
     */
    public function canHaveMovements(): bool
    {
        return $this->permite_movimiento && $this->estado === 'activo' && $this->isVigent();
    }

    /**
     * Validar vigencia de la cuenta
     */
    public function isVigent(): bool
    {
        $today = now()->toDateString();

        if ($this->fecha_vigencia_inicio && $today < $this->fecha_vigencia_inicio->toDateString()) {
            return false;
        }

        if ($this->fecha_vigencia_fin && $today > $this->fecha_vigencia_fin->toDateString()) {
            return false;
        }

        return true;
    }

    /**
     * Obtener saldo actual (débito - crédito)
     */
    public function getCurrentBalance(): float
    {
        $balance = (float) $this->saldo_inicial;

        $movimientos = $this->journalLines()
            ->whereHas('journalEntry', fn ($q) => $q->where('estado', 'posteado'))
            ->get();

        foreach ($movimientos as $line) {
            if ($line->tipo_movimiento === 'debito') {
                $balance += $line->valor;
            } else {
                $balance -= $line->valor;
            }
        }

        return $balance;
    }

    /**
     * Obtener saldo por período
     */
    public function getBalanceByPeriod(\Carbon\Carbon $desde, \Carbon\Carbon $hasta): float
    {
        $balance = (float) $this->saldo_inicial;

        $movimientos = $this->journalLines()
            ->whereHas('journalEntry', function ($q) use ($desde, $hasta) {
                $q->where('estado', 'posteado')
                    ->whereBetween('fecha', [$desde, $hasta]);
            })
            ->get();

        foreach ($movimientos as $line) {
            if ($line->tipo_movimiento === 'debito') {
                $balance += $line->valor;
            } else {
                $balance -= $line->valor;
            }
        }

        return $balance;
    }

    /**
     * Activar cuenta
     */
    public function activate(): void
    {
        $this->update(['estado' => 'activo']);
    }

    /**
     * Desactivar cuenta
     */
    public function deactivate(): void
    {
        $this->update(['estado' => 'inactivo']);
    }
}
