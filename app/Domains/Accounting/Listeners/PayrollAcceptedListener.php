<?php

namespace App\Domains\Accounting\Listeners;

use App\Domains\Accounting\Services\AutoJournalEntryCreator;
use App\Domains\Payroll\Models\NominaElectronica;

class PayrollAcceptedListener
{
    protected AutoJournalEntryCreator $creator;

    public function __construct(AutoJournalEntryCreator $creator)
    {
        $this->creator = $creator;
    }

    /**
     * Escuchar evento cuando nómina es aceptada por DIAN
     */
    public function handle(NominaElectronica $nomina): void
    {
        // Validar que auto-creación esté habilitada
        if (!AutoJournalEntryCreator::isAutoCreationEnabled($nomina->empresa_id)) {
            return;
        }

        // Procesar nómina en contabilidad
        $this->creator->processPayrollAccepted($nomina);
    }
}
