<?php

namespace App\Domains\Accounting\Services;

use App\Domains\Invoicing\Models\Invoice;
use App\Domains\Payroll\Models\NominaElectronica;
use Illuminate\Support\Facades\Log;

class AutoJournalEntryCreator
{
    protected InvoiceAccountingService $invoiceService;
    protected PayrollAccountingService $payrollService;
    protected BankReconciliationService $reconciliationService;

    public function __construct(
        InvoiceAccountingService $invoiceService,
        PayrollAccountingService $payrollService,
        BankReconciliationService $reconciliationService
    ) {
        $this->invoiceService = $invoiceService;
        $this->payrollService = $payrollService;
        $this->reconciliationService = $reconciliationService;
    }

    /**
     * Procesar evento de factura aceptada
     */
    public function processInvoiceAccepted(Invoice $invoice): void
    {
        try {
            $asiento = $this->invoiceService->createInvoiceAcceptedEntry($invoice);

            if ($asiento) {
                // Ejecutar conciliación automática si está configurada
                if (config('accounting.auto_reconciliation_enabled', false)) {
                    $this->reconciliationService->validateBankBalance($invoice->empresa_id);
                }

                Log::info('Factura procesada en contabilidad', [
                    'invoice_id' => $invoice->id,
                    'asiento_id' => $asiento->id,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error procesando factura en contabilidad', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Procesar evento de factura rechazada
     */
    public function processInvoiceRejected(Invoice $invoice): void
    {
        try {
            $this->invoiceService->reverseInvoiceEntry($invoice);

            Log::info('Factura rechazada - asiento reversado', [
                'invoice_id' => $invoice->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Error reversando asiento de factura', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Procesar evento de nómina aceptada
     */
    public function processPayrollAccepted(NominaElectronica $nomina): void
    {
        try {
            $asiento = $this->payrollService->createPayrollAcceptedEntry($nomina);

            if ($asiento) {
                Log::info('Nómina procesada en contabilidad', [
                    'nomina_id' => $nomina->id,
                    'asiento_id' => $asiento->id,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error procesando nómina en contabilidad', [
                'nomina_id' => $nomina->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Procesar evento de pago de nómina
     */
    public function processPayrollPayment(int $empresaId, float $monto, string $referencia): void
    {
        try {
            $asiento = $this->payrollService->createAportesPaymentEntry($empresaId, $monto, $referencia);

            Log::info('Pago de nómina registrado en contabilidad', [
                'asiento_id' => $asiento->id,
                'monto' => $monto,
            ]);
        } catch (\Exception $e) {
            Log::error('Error registrando pago de nómina', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Activar/Desactivar creación automática de asientos
     */
    public static function isAutoCreationEnabled(int $empresaId): bool
    {
        // Leer de configuración por empresa
        return config("accounting.companies.{$empresaId}.auto_entries", true);
    }

    /**
     * Obtener estadísticas de asientos automáticos
     */
    public function getAutoEntryStatistics(int $empresaId): array
    {
        $invoiceEntries = \App\Domains\Accounting\Models\JournalEntry::where('empresa_id', $empresaId)
            ->where('tipo_documento', 'factura')
            ->count();

        $payrollEntries = \App\Domains\Accounting\Models\JournalEntry::where('empresa_id', $empresaId)
            ->where('tipo_documento', 'nomina')
            ->count();

        return [
            'total_invoice_entries' => $invoiceEntries,
            'total_payroll_entries' => $payrollEntries,
            'total_auto_entries' => $invoiceEntries + $payrollEntries,
        ];
    }
}
