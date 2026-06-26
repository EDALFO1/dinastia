<?php

namespace App\Domains\Accounting\Services;

use App\Domains\Accounting\Models\ChartOfAccounts;
use App\Domains\Accounting\Models\JournalEntry;
use App\Domains\Invoicing\Models\Invoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceAccountingService
{
    protected AccountingValidator $validator;

    public function __construct(AccountingValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Crear asientos automáticos cuando factura es aceptada en DIAN
     * Asiento: Débito Bancos / Crédito Ingresos
     */
    public function createInvoiceAcceptedEntry(Invoice $invoice): ?JournalEntry
    {
        return DB::transaction(function () use ($invoice) {
            // Validar que la factura esté aceptada
            if ($invoice->estado !== 'aceptada') {
                Log::warning('Intento de crear asiento para factura no aceptada', [
                    'invoice_id' => $invoice->id,
                    'estado' => $invoice->estado,
                ]);
                return null;
            }

            // Obtener cuentas contables
            $cuentaBancos = $this->findOrCreateBankAccount($invoice->empresa_id);
            $cuentaIngresos = $this->findOrCreateRevenueAccount($invoice->empresa_id);

            if (!$cuentaBancos || !$cuentaIngresos) {
                Log::error('No se encontraron cuentas contables para la factura', [
                    'invoice_id' => $invoice->id,
                ]);
                return null;
            }

            // Crear asiento
            $asiento = JournalEntry::create([
                'empresa_id' => $invoice->empresa_id,
                'numero_asiento' => JournalEntry::generateNumber($invoice->empresa_id),
                'fecha' => $invoice->fecha,
                'descripcion' => "Factura #{$invoice->numero} - {$invoice->cliente->nombre}",
                'referencia_documento' => $invoice->numero,
                'tipo_documento' => 'factura',
                'usuario_creacion_id' => auth()->id() ?? 1,
                'estado' => 'borrador',
            ]);

            // Líneas: Débito Bancos, Crédito Ingresos
            $total = (float) $invoice->monto_total;

            // Línea 1: Débito Bancos
            $asiento->lines()->create([
                'empresa_id' => $invoice->empresa_id,
                'account_id' => $cuentaBancos->id,
                'descripcion' => "Depósito factura #{$invoice->numero}",
                'tipo_movimiento' => 'debito',
                'valor' => $total,
            ]);

            // Línea 2: Crédito Ingresos
            $asiento->lines()->create([
                'empresa_id' => $invoice->empresa_id,
                'account_id' => $cuentaIngresos->id,
                'descripcion' => "Ingreso por venta",
                'tipo_movimiento' => 'credito',
                'valor' => $total,
            ]);

            // Aprobar el asiento automáticamente
            try {
                $asiento->approve(1); // Sistema user
            } catch (\Exception $e) {
                Log::error('Error aprobando asiento automático', [
                    'asiento_id' => $asiento->id,
                    'error' => $e->getMessage(),
                ]);
            }

            Log::info('Asiento contable creado automáticamente desde factura', [
                'invoice_id' => $invoice->id,
                'asiento_id' => $asiento->id,
                'numero_asiento' => $asiento->numero_asiento,
                'monto' => $total,
            ]);

            return $asiento;
        });
    }

    /**
     * Reversar asiento cuando factura es rechazada
     */
    public function reverseInvoiceEntry(Invoice $invoice): void
    {
        $asiento = JournalEntry::where('empresa_id', $invoice->empresa_id)
            ->where('referencia_documento', $invoice->numero)
            ->where('tipo_documento', 'factura')
            ->first();

        if (!$asiento) {
            return;
        }

        // Crear asiento inverso (reversión)
        DB::transaction(function () use ($asiento, $invoice) {
            $asientoReverso = JournalEntry::create([
                'empresa_id' => $asiento->empresa_id,
                'numero_asiento' => JournalEntry::generateNumber($asiento->empresa_id),
                'fecha' => now(),
                'descripcion' => "Reversión - Factura {$invoice->numero} rechazada",
                'referencia_documento' => $invoice->numero,
                'tipo_documento' => 'factura_reversal',
                'usuario_creacion_id' => auth()->id() ?? 1,
            ]);

            // Crear líneas inversas
            foreach ($asiento->lines as $linea) {
                $tipoInverso = $linea->tipo_movimiento === 'debito' ? 'credito' : 'debito';

                $asientoReverso->lines()->create([
                    'empresa_id' => $linea->empresa_id,
                    'account_id' => $linea->account_id,
                    'descripcion' => "Inversión: {$linea->descripcion}",
                    'tipo_movimiento' => $tipoInverso,
                    'valor' => $linea->valor,
                ]);
            }

            // Aprobar automáticamente
            $asientoReverso->approve(1);

            Log::info('Asiento de reversión creado', [
                'asiento_original_id' => $asiento->id,
                'asiento_reverso_id' => $asientoReverso->id,
            ]);
        });
    }

    /**
     * Encontrar o crear cuenta de bancos
     */
    private function findOrCreateBankAccount(int $empresaId): ?ChartOfAccounts
    {
        $cuenta = ChartOfAccounts::where('empresa_id', $empresaId)
            ->where('codigo', '100501')
            ->first();

        if ($cuenta) {
            return $cuenta;
        }

        // Crear si no existe
        return ChartOfAccounts::create([
            'empresa_id' => $empresaId,
            'codigo' => '100501',
            'nombre' => 'Banco - Cuenta Corriente',
            'tipo_cuenta' => 'activo',
            'nivel' => 3,
            'permite_movimiento' => true,
            'estado' => 'activo',
        ]);
    }

    /**
     * Encontrar o crear cuenta de ingresos
     */
    private function findOrCreateRevenueAccount(int $empresaId): ?ChartOfAccounts
    {
        $cuenta = ChartOfAccounts::where('empresa_id', $empresaId)
            ->where('codigo', '410101')
            ->first();

        if ($cuenta) {
            return $cuenta;
        }

        // Crear si no existe
        return ChartOfAccounts::create([
            'empresa_id' => $empresaId,
            'codigo' => '410101',
            'nombre' => 'Ventas Nacionales',
            'tipo_cuenta' => 'ingresos',
            'nivel' => 3,
            'permite_movimiento' => true,
            'estado' => 'activo',
        ]);
    }
}
