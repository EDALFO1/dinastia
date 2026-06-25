<?php

namespace Tests\Feature\Invoicing;

use App\Domains\Invoicing\Models\InvoiceTax;
use App\Domains\Invoicing\Models\Invoice;
use App\Models\Empresa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTaxTest extends TestCase
{
    use RefreshDatabase;

    protected Empresa $empresa;
    protected Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();
        $this->empresa = Empresa::factory()->create();
        $this->invoice = Invoice::factory()->create(['empresa_id' => $this->empresa->id]);
    }

    public function test_puede_crear_impuesto_de_factura(): void
    {
        $tax = InvoiceTax::factory()->create([
            'invoice_id' => $this->invoice->id,
            'empresa_id' => $this->empresa->id,
        ]);

        $this->assertDatabaseHas('invoice_taxes', [
            'id' => $tax->id,
            'invoice_id' => $this->invoice->id,
        ]);
    }

    public function test_calcular_valor_se_ejecuta_en_creating(): void
    {
        $tax = InvoiceTax::factory()->create([
            'invoice_id' => $this->invoice->id,
            'empresa_id' => $this->empresa->id,
            'base' => 1000,
            'porcentaje' => 19,
        ]);

        $this->assertEqualsWithDelta($tax->valor, 190, 0.01);
    }

    public function test_calcular_valor_actualiza_en_updating(): void
    {
        $tax = InvoiceTax::factory()->create([
            'invoice_id' => $this->invoice->id,
            'empresa_id' => $this->empresa->id,
            'base' => 1000,
            'porcentaje' => 19,
        ]);

        $tax->update(['porcentaje' => 5]);

        $this->assertEqualsWithDelta($tax->valor, 50, 0.01);
    }
}
