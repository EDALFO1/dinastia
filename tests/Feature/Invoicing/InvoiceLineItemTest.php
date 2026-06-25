<?php

namespace Tests\Feature\Invoicing;

use App\Domains\Invoicing\Models\InvoiceLineItem;
use App\Domains\Invoicing\Models\Invoice;
use App\Models\Empresa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceLineItemTest extends TestCase
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

    public function test_puede_crear_linea_de_factura(): void
    {
        $lineItem = InvoiceLineItem::factory()->create([
            'invoice_id' => $this->invoice->id,
            'empresa_id' => $this->empresa->id,
        ]);

        $this->assertDatabaseHas('invoice_line_items', [
            'id' => $lineItem->id,
            'invoice_id' => $this->invoice->id,
        ]);
    }

    public function test_calcular_valor_linea_se_ejecuta_en_creating(): void
    {
        $lineItem = InvoiceLineItem::factory()->create([
            'invoice_id' => $this->invoice->id,
            'empresa_id' => $this->empresa->id,
            'cantidad' => 10,
            'valor_unitario' => 100,
            'descuento' => 0,
        ]);

        $this->assertEquals(1000, $lineItem->valor_linea);
    }

    public function test_calcular_valor_linea_resta_descuento(): void
    {
        $lineItem = InvoiceLineItem::factory()->create([
            'invoice_id' => $this->invoice->id,
            'empresa_id' => $this->empresa->id,
            'cantidad' => 10,
            'valor_unitario' => 100,
            'descuento' => 200,
        ]);

        $this->assertEquals(800, $lineItem->valor_linea);
    }

    public function test_validar_que_linea_tiene_relacion_a_impuestos(): void
    {
        $lineItem = InvoiceLineItem::factory()->create([
            'invoice_id' => $this->invoice->id,
            'empresa_id' => $this->empresa->id,
        ]);

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $lineItem->taxes);
    }
}
