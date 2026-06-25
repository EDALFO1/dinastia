<?php

namespace App\Domains\Invoicing\Services;

use App\Domains\Invoicing\Models\Invoice;
use Illuminate\Support\Collection;

class InvoiceValidator
{
    public function __construct(private DianValidator $dianValidator) {}

    public function validarIntegridad(Invoice $invoice): bool
    {
        if ($invoice->lineItems->isEmpty()) {
            return false;
        }

        $sumaLineas = $invoice->lineItems->sum('valor_linea');
        $sumaImpuestos = $invoice->taxes->sum('valor');

        $totalCalculado = $sumaLineas - $invoice->descuento + $sumaImpuestos;
        $diferencia = abs($totalCalculado - $invoice->total);

        return $diferencia < 0.01; // Tolerance for rounding
    }

    public function validarDocumento(string $nit): bool
    {
        return $this->dianValidator->validarNit($nit);
    }

    public function validarFechas(Invoice $invoice): bool
    {
        if ($invoice->fecha_vencimiento < $invoice->fecha_emision) {
            return false;
        }

        return true;
    }

    public function validarResolucion(Invoice $invoice): bool
    {
        $sequence = $invoice->sequence;

        if (!$sequence || !$sequence->isActive()) {
            return false;
        }

        if (!$sequence->isWithinRange()) {
            return false;
        }

        return true;
    }

    public function validarDetalles(Invoice $invoice): bool
    {
        foreach ($invoice->lineItems as $lineItem) {
            if ($lineItem->cantidad <= 0) {
                return false;
            }

            if ($lineItem->valor_unitario < 0) {
                return false;
            }

            if ($lineItem->descuento > ($lineItem->cantidad * $lineItem->valor_unitario)) {
                return false;
            }

            if (!$this->dianValidator->validarUnidadMedida((string) $lineItem->unidad->value)) {
                return false;
            }
        }

        foreach ($invoice->taxes as $tax) {
            if (!$this->dianValidator->validarTipoImpuesto((string) $tax->tipo_impuesto->value)) {
                return false;
            }

            if ($tax->base <= 0) {
                return false;
            }

            if ($tax->porcentaje < 0 || $tax->porcentaje > 100) {
                return false;
            }
        }

        return true;
    }
}
