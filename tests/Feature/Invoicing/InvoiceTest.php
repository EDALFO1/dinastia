<?php

namespace Tests\Feature\Invoicing;

use App\Domains\Invoicing\Models\Invoice;
use App\Domains\Invoicing\Models\InvoiceLineItem;
use App\Domains\Invoicing\Models\InvoiceTax;
use App\Domains\Invoicing\Enums\InvoiceType;
use App\Models\Empresa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    protected Empresa $empresa;

    protected function setUp(): void
    {
        parent::setUp();
        $this->empresa = Empresa::factory()->create();
    }

    public function test_puede_crear_factura_con_relaciones(): void
    {
        $invoice = Invoice::factory()->create(['empresa_id' => $this->empresa->id]);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'empresa_id' => $this->empresa->id,
        ]);
    }

    public function test_factura_tiene_relaciones_a_lineas_e_impuestos(): void
    {
        $invoice = Invoice::factory()->create(['empresa_id' => $this->empresa->id]);
        InvoiceLineItem::factory()->create(['invoice_id' => $invoice->id, 'empresa_id' => $this->empresa->id]);
        InvoiceTax::factory()->create(['invoice_id' => $invoice->id, 'empresa_id' => $this->empresa->id]);

        $this->assertCount(1, $invoice->lineItems);
        $this->assertCount(1, $invoice->taxes);
    }

    public function test_can_edit_retorna_true_solo_para_borrador(): void
    {
        $borrador = Invoice::factory()->create(['empresa_id' => $this->empresa->id, 'estado' => 'borrador']);
        $enviada = Invoice::factory()->create(['empresa_id' => $this->empresa->id, 'estado' => 'enviada']);

        $this->assertTrue($borrador->canEdit());
        $this->assertFalse($enviada->canEdit());
    }

    public function test_is_active_valida_estados_activos(): void
    {
        $borrador = Invoice::factory()->create(['empresa_id' => $this->empresa->id, 'estado' => 'borrador']);
        $enviada = Invoice::factory()->create(['empresa_id' => $this->empresa->id, 'estado' => 'enviada']);
        $rechazada = Invoice::factory()->create(['empresa_id' => $this->empresa->id, 'estado' => 'rechazada']);

        $this->assertTrue($borrador->isActive());
        $this->assertTrue($enviada->isActive());
        $this->assertFalse($rechazada->isActive());
    }

    public function test_calculate_totals_suma_lineas_e_impuestos_correctamente(): void
    {
        $sequence = \Database\Factories\InvoiceSequenceFactory::new()->create(['empresa_id' => $this->empresa->id]);

        $invoice = Invoice::factory()->create([
            'empresa_id' => $this->empresa->id,
            'invoice_sequence_id' => $sequence->id,
            'subtotal' => 0,
            'descuento' => 0,
            'total_impuestos' => 0,
            'total' => 0,
        ]);

        InvoiceLineItem::factory()->create([
            'invoice_id' => $invoice->id,
            'empresa_id' => $this->empresa->id,
            'cantidad' => 1,
            'valor_unitario' => 100.00,
            'descuento' => 0,
            'valor_linea' => 100.00,
        ]);

        InvoiceTax::factory()->create([
            'invoice_id' => $invoice->id,
            'empresa_id' => $this->empresa->id,
            'base' => 100.00,
            'porcentaje' => 19.00,
            'valor' => 19.00,
        ]);

        $invoice->calculateTotals();

        $this->assertEquals(100.00, $invoice->subtotal);
        $this->assertEquals(19.00, $invoice->total_impuestos);
        $this->assertEquals(119.00, $invoice->total);
    }
}
