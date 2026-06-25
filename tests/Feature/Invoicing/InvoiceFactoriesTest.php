<?php

namespace Tests\Feature\Invoicing;

use App\Domains\Invoicing\Models\Invoice;
use App\Domains\Invoicing\Models\InvoiceLineItem;
use App\Domains\Invoicing\Models\InvoiceTax;
use App\Domains\Invoicing\Models\InvoiceSequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceFactoriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_sequence_factory_genera_datos_validos(): void
    {
        $sequence = InvoiceSequence::factory()->create();

        $this->assertNotNull($sequence->id);
        $this->assertNotNull($sequence->empresa_id);
        $this->assertGreaterThan($sequence->rango_inicio, $sequence->rango_fin);
        $this->assertGreaterThanOrEqual($sequence->proximo_numero, $sequence->rango_inicio);
    }

    public function test_invoice_factory_genera_datos_validos(): void
    {
        $invoice = Invoice::factory()->create();

        $this->assertNotNull($invoice->id);
        $this->assertNotNull($invoice->empresa_id);
        $this->assertNotNull($invoice->numero);
        $this->assertGreaterThan(0, $invoice->total);
    }

    public function test_invoice_line_item_factory_genera_datos_validos(): void
    {
        $lineItem = InvoiceLineItem::factory()->create();

        $this->assertNotNull($lineItem->id);
        $this->assertNotNull($lineItem->empresa_id);
        $this->assertGreaterThan(0, $lineItem->cantidad);
        $this->assertGreaterThanOrEqual(0, $lineItem->valor_unitario);
        $this->assertGreaterThanOrEqual(0, $lineItem->valor_linea);
    }

    public function test_invoice_tax_factory_genera_datos_validos(): void
    {
        $tax = InvoiceTax::factory()->create();

        $this->assertNotNull($tax->id);
        $this->assertNotNull($tax->empresa_id);
        $this->assertGreaterThanOrEqual(0, $tax->porcentaje);
        $this->assertGreaterThan(0, $tax->base);
    }
}
