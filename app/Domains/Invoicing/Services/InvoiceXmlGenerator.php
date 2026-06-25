<?php

namespace App\Domains\Invoicing\Services;

use App\Domains\Invoicing\Models\Invoice;

class InvoiceXmlGenerator
{
    private XmlBuilder $builder;

    public function generate(Invoice $invoice): string
    {
        $this->builder = new XmlBuilder();
        $root = $this->builder->getRoot();

        $this->addInvoiceHeader($invoice);
        $this->addSupplierParty($invoice);
        $this->addCustomerParty($invoice);
        $this->addLineItems($invoice);
        $this->addTaxTotals($invoice);
        $this->addLegalMonetaryTotals($invoice);

        return $this->builder->getXmlString();
    }

    private function addInvoiceHeader(Invoice $invoice): void
    {
        $this->builder->addCbcElement('UBLVersionID', '2.1');
        $this->builder->addCbcElement('CustomizationID', '05');
        $this->builder->addCbcElement('ProfileID', 'DIAN 2.1');
        $this->builder->addCbcElement('ID', (string) $invoice->numero);
        $this->builder->addCbcElement('IssueDate', $invoice->fecha_emision->format('Y-m-d'));
        $this->builder->addCbcElement('IssueTime', $invoice->created_at->format('H:i:s'));
        $this->builder->addCbcElement('InvoiceTypeCode', $this->getInvoiceTypeCode($invoice->tipo_documento->value));
        $this->builder->addCbcElement('DocumentCurrencyCode', 'COP');
        $this->builder->addCbcElement('OrderReference', $invoice->uuid_dian ?? 'PENDING');

        if ($invoice->observaciones) {
            $this->builder->addCbcElement('Note', $invoice->observaciones);
        }
    }

    private function addSupplierParty(Invoice $invoice): void
    {
        $supplier = $this->builder->addCacElement('AccountingSupplierParty');

        $partyElement = $this->builder->addNestedCacElement($supplier, 'Party');
        $this->builder->addPartyIdentification($partyElement, 'NIT', $invoice->empresa->numero_documento ?? '');
        $this->builder->addPartyName($partyElement, $invoice->empresa->nombre ?? 'Unknown');

        $legalEntity = $this->builder->addPartyLegalEntity(
            $partyElement,
            $invoice->empresa->nombre ?? 'Unknown',
            $invoice->empresa->numero_documento ?? '',
            'NIT'
        );
    }

    private function addCustomerParty(Invoice $invoice): void
    {
        $customer = $this->builder->addCacElement('AccountingCustomerParty');

        $partyElement = $this->builder->addNestedCacElement($customer, 'Party');
        $this->builder->addPartyIdentification($partyElement, 'NIT', $invoice->cliente_nit);
        $this->builder->addPartyName($partyElement, $invoice->cliente_nombre);

        $this->builder->addPartyLegalEntity(
            $partyElement,
            $invoice->cliente_nombre,
            $invoice->cliente_nit,
            'NIT'
        );
    }

    private function addLineItems(Invoice $invoice): void
    {
        foreach ($invoice->lineItems as $index => $lineItem) {
            $line = $this->builder->addCacElement('InvoiceLine');
            $this->builder->addNestedCbcElement($line, 'ID', (string) ($index + 1));
            $this->builder->addNestedCbcElement($line, 'InvoicedQuantity', (string) $lineItem->cantidad, ['unitCode' => $this->getUnitCode($lineItem->unidad->value)]);
            $this->builder->addNestedCbcElement($line, 'LineExtensionAmount', number_format($lineItem->valor_linea, 2, '.', ''), ['currencyID' => 'COP']);

            $item = $this->builder->addNestedCacElement($line, 'Item');
            $this->builder->addNestedCbcElement($item, 'Description', $lineItem->descripcion);

            $price = $this->builder->addNestedCacElement($line, 'Price');
            $this->builder->addNestedCbcElement($price, 'PriceAmount', number_format($lineItem->valor_unitario, 2, '.', ''), ['currencyID' => 'COP']);

            foreach ($lineItem->taxes as $tax) {
                $taxTotal = $this->builder->addNestedCacElement($line, 'TaxTotal');
                $this->builder->addNestedCbcElement($taxTotal, 'TaxAmount', number_format($tax->valor, 2, '.', ''), ['currencyID' => 'COP']);

                $taxSubtotal = $this->builder->addNestedCacElement($taxTotal, 'TaxSubtotal');
                $this->builder->addNestedCbcElement($taxSubtotal, 'TaxableAmount', number_format($tax->base, 2, '.', ''), ['currencyID' => 'COP']);
                $this->builder->addNestedCbcElement($taxSubtotal, 'TaxAmount', number_format($tax->valor, 2, '.', ''), ['currencyID' => 'COP']);
                $this->builder->addNestedCbcElement($taxSubtotal, 'Percent', number_format($tax->porcentaje, 2, '.', ''));

                $taxCategory = $this->builder->addNestedCacElement($taxSubtotal, 'TaxCategory');
                $this->builder->addNestedCbcElement($taxCategory, 'ID', $this->getTaxCategoryCode($tax->tipo_impuesto->value));
                $this->builder->addNestedCbcElement($taxCategory, 'Percent', number_format($tax->porcentaje, 2, '.', ''));

                $taxScheme = $this->builder->addNestedCacElement($taxCategory, 'TaxScheme');
                $this->builder->addNestedCbcElement($taxScheme, 'ID', 'IVA');
            }
        }
    }

    private function addTaxTotals(Invoice $invoice): void
    {
        if ($invoice->taxes->isNotEmpty()) {
            $taxTotal = $this->builder->addCacElement('TaxTotal');
            $this->builder->addNestedCbcElement($taxTotal, 'TaxAmount', number_format($invoice->total_impuestos, 2, '.', ''), ['currencyID' => 'COP']);

            foreach ($invoice->taxes->groupBy('tipo_impuesto') as $taxType => $taxes) {
                $subtotal = $this->builder->addNestedCacElement($taxTotal, 'TaxSubtotal');

                $totalByType = $taxes->sum('valor');
                $baseByType = $taxes->sum('base');

                $this->builder->addNestedCbcElement($subtotal, 'TaxableAmount', number_format($baseByType, 2, '.', ''), ['currencyID' => 'COP']);
                $this->builder->addNestedCbcElement($subtotal, 'TaxAmount', number_format($totalByType, 2, '.', ''), ['currencyID' => 'COP']);

                $category = $this->builder->addNestedCacElement($subtotal, 'TaxCategory');
                $this->builder->addNestedCbcElement($category, 'ID', $this->getTaxCategoryCode($taxType));
                $this->builder->addNestedCbcElement($category, 'Percent', number_format($taxes->first()->porcentaje, 2, '.', ''));

                $scheme = $this->builder->addNestedCacElement($category, 'TaxScheme');
                $this->builder->addNestedCbcElement($scheme, 'ID', 'IVA');
            }
        }
    }

    private function addLegalMonetaryTotals(Invoice $invoice): void
    {
        $monetary = $this->builder->addCacElement('LegalMonetaryTotal');
        $this->builder->addNestedCbcElement($monetary, 'LineExtensionAmount', number_format($invoice->subtotal, 2, '.', ''), ['currencyID' => 'COP']);
        $this->builder->addNestedCbcElement($monetary, 'TaxExclusiveAmount', number_format($invoice->subtotal - $invoice->descuento, 2, '.', ''), ['currencyID' => 'COP']);
        $this->builder->addNestedCbcElement($monetary, 'TaxInclusiveAmount', number_format($invoice->total, 2, '.', ''), ['currencyID' => 'COP']);

        if ($invoice->descuento > 0) {
            $this->builder->addNestedCbcElement($monetary, 'AllowanceTotalAmount', number_format($invoice->descuento, 2, '.', ''), ['currencyID' => 'COP']);
        }

        $this->builder->addNestedCbcElement($monetary, 'PayableAmount', number_format($invoice->total, 2, '.', ''), ['currencyID' => 'COP']);
    }

    private function getInvoiceTypeCode(string $type): string
    {
        return match ($type) {
            'factura' => '01',
            'nota_credito' => '91',
            'nota_debito' => '92',
            default => '01',
        };
    }

    private function getUnitCode(string $unit): string
    {
        return match ($unit) {
            'unidad' => 'UN',
            'kilogramo' => 'KG',
            'gramo' => 'GR',
            'metro' => 'MTR',
            'centimetro' => 'CMT',
            'hora' => 'HUR',
            'minuto' => 'MIN',
            'litro' => 'LTR',
            'mililitro' => 'MLT',
            default => 'UN',
        };
    }

    private function getTaxCategoryCode(string $type): string
    {
        return match ($type) {
            'iva' => 'S',
            'impuesto_consumo' => 'C',
            'impuesto_nacional' => 'O',
            default => 'S',
        };
    }
}
