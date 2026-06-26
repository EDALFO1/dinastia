<?php

namespace App\Domains\Accounting\Listeners;

use App\Domains\Accounting\Services\AutoJournalEntryCreator;
use App\Domains\Invoicing\Models\Invoice;
use Illuminate\Events\Dispatcher;

class InvoiceAcceptedListener
{
    protected AutoJournalEntryCreator $creator;

    public function __construct(AutoJournalEntryCreator $creator)
    {
        $this->creator = $creator;
    }

    /**
     * Escuchar evento cuando factura es aceptada por DIAN
     */
    public function handle(Invoice $invoice): void
    {
        // Validar que auto-creación esté habilitada
        if (!AutoJournalEntryCreator::isAutoCreationEnabled($invoice->empresa_id)) {
            return;
        }

        // Procesar factura en contabilidad
        $this->creator->processInvoiceAccepted($invoice);
    }

    /**
     * Registrar listeners
     */
    public static function register(Dispatcher $dispatcher): void
    {
        // Se registrará en EventServiceProvider
    }
}
