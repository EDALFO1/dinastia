<?php

namespace App\Domains\Accounting\Services;

use App\Domains\Accounting\Models\ChartOfAccounts;
use App\Domains\Accounting\Models\JournalEntry;
use Illuminate\Support\Facades\Log;

class AccountingValidator
{
    protected array $errors = [];

    /**
     * Validar que una cuenta existe
     */
    public function validateAccountExists(int $empresaId, string $codigoCuenta): bool
    {
        $cuenta = ChartOfAccounts::where('empresa_id', $empresaId)
            ->where('codigo', $codigoCuenta)
            ->first();

        if (!$cuenta) {
            $this->errors[] = "Cuenta {$codigoCuenta} no existe";
            return false;
        }

        return true;
    }

    /**
     * Validar que una cuenta permite movimientos
     */
    public function validateAccountCanMove(ChartOfAccounts $cuenta): bool
    {
        if (!$cuenta->permits_movimiento) {
            $this->errors[] = "Cuenta {$cuenta->codigo} no permite movimientos";
            return false;
        }

        if ($cuenta->estado !== 'activo') {
            $this->errors[] = "Cuenta {$cuenta->codigo} está inactiva";
            return false;
        }

        if (!$cuenta->isVigent()) {
            $this->errors[] = "Cuenta {$cuenta->codigo} no está vigente";
            return false;
        }

        return true;
    }

    /**
     * Validar integridad de asiento
     */
    public function validateJournalEntry(JournalEntry $entry): bool
    {
        $this->errors = [];

        // Validar que tenga al menos 2 líneas
        if ($entry->lines()->count() < 2) {
            $this->errors[] = 'El asiento debe tener al mínimo 2 líneas';
            return false;
        }

        // Validar que débito = crédito
        $debit = $entry->getTotalDebit();
        $credit = $entry->getTotalCredit();

        if (abs($debit - $credit) > 0.01) {
            $this->errors[] = "Asiento desbalanceado: Débito {$debit} ≠ Crédito {$credit}";
            return false;
        }

        // Validar cada línea
        foreach ($entry->lines as $line) {
            if (!$this->validateAccountCanMove($line->account)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validar saldo de una cuenta
     */
    public function validateAccountBalance(ChartOfAccounts $cuenta, float $saldoEsperado): bool
    {
        $saldoReal = $cuenta->getCurrentBalance();

        if (abs($saldoReal - $saldoEsperado) > 0.01) {
            $this->errors[] = "Saldo incorrecto en cuenta {$cuenta->codigo}: esperado {$saldoEsperado}, obtenido {$saldoReal}";
            return false;
        }

        return true;
    }

    /**
     * Validar rango de valores
     */
    public function validateValueRange(float $valor, float $minimo = 0.01, float $maximo = 999999999.99): bool
    {
        if ($valor < $minimo || $valor > $maximo) {
            $this->errors[] = "Valor fuera de rango permitido [{$minimo}, {$maximo}]";
            return false;
        }

        return true;
    }

    /**
     * Validar duplicados en período
     */
    public function validateNoDuplicate(int $empresaId, string $numeroAsiento): bool
    {
        $exists = JournalEntry::where('empresa_id', $empresaId)
            ->where('numero_asiento', $numeroAsiento)
            ->exists();

        if ($exists) {
            $this->errors[] = "Asiento {$numeroAsiento} ya existe";
            return false;
        }

        return true;
    }

    /**
     * Validar que la fecha es válida
     */
    public function validateDate(\Carbon\Carbon $fecha, \Carbon\Carbon $fechaInicio, \Carbon\Carbon $fechaFin): bool
    {
        if ($fecha->isBefore($fechaInicio) || $fecha->isAfter($fechaFin)) {
            $this->errors[] = "Fecha debe estar entre {$fechaInicio->toDateString()} y {$fechaFin->toDateString()}";
            return false;
        }

        return true;
    }

    /**
     * Obtener errores
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Limpiar errores
     */
    public function clearErrors(): void
    {
        $this->errors = [];
    }

    /**
     * Registrar validación
     */
    public function log(string $action, int $empresaId, bool $success, ?string $message = null): void
    {
        $level = $success ? 'info' : 'warning';

        Log::channel('accounting')->{$level}("Validación contable", [
            'action' => $action,
            'empresa_id' => $empresaId,
            'success' => $success,
            'message' => $message ?? ($success ? 'OK' : implode('; ', $this->errors)),
            'timestamp' => now(),
        ]);
    }
}
