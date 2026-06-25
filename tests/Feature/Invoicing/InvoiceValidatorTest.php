<?php

namespace Tests\Feature\Invoicing;

use App\Domains\Invoicing\Models\Invoice;
use App\Domains\Invoicing\Models\InvoiceLineItem;
use App\Domains\Invoicing\Models\InvoiceTax;
use App\Domains\Invoicing\Services\DianValidator;
use App\Domains\Invoicing\Services\InvoiceValidator;
use App\Models\Empresa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceValidatorTest extends TestCase
{
    use RefreshDatabase;

    protected Empresa $empresa;
    protected InvoiceValidator $validator;
    protected DianValidator $dianValidator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->empresa = Empresa::factory()->create();
        $this->dianValidator = new DianValidator();
        $this->validator = new InvoiceValidator($this->dianValidator);
    }

    public function test_validar_integridad_rechaza_factura_sin_lineas(): void
    {
        $invoice = Invoice::factory()->create(['empresa_id' => $this->empresa->id]);
        $this->assertFalse($this->validator->validarIntegridad($invoice));
    }

    public function test_validar_integridad_valida_sumas_correctas(): void
    {
        $sequence = \Database\Factories\InvoiceSequenceFactory::new()->create(['empresa_id' => $this->empresa->id]);

        $invoice = Invoice::factory()->create([
            'empresa_id' => $this->empresa->id,
            'invoice_sequence_id' => $sequence->id,
            'subtotal' => 100,
            'descuento' => 0,
            'total_impuestos' => 19,
            'total' => 119,
        ]);

        InvoiceLineItem::factory()->create([
            'invoice_id' => $invoice->id,
            'empresa_id' => $this->empresa->id,
            'cantidad' => 1,
            'valor_unitario' => 100,
            'descuento' => 0,
            'valor_linea' => 100,
        ]);

        InvoiceTax::factory()->create([
            'invoice_id' => $invoice->id,
            'empresa_id' => $this->empresa->id,
            'base' => 100,
            'porcentaje' => 19,
            'valor' => 19,
        ]);

        $this->assertTrue($this->validator->validarIntegridad($invoice));
    }

    public function test_validar_documento_valida_nit_cliente(): void
    {
        $this->assertTrue($this->validator->validarDocumento('123456'));
    }

    public function test_validar_fechas_rechaza_vencimiento_antes_de_emision(): void
    {
        $invoice = Invoice::factory()->create([
            'empresa_id' => $this->empresa->id,
            'fecha_emision' => '2026-07-01',
            'fecha_vencimiento' => '2026-06-01',
        ]);

        $this->assertFalse($this->validator->validarFechas($invoice));
    }

    public function test_validar_fechas_acepta_vencimiento_despues_de_emision(): void
    {
        $invoice = Invoice::factory()->create([
            'empresa_id' => $this->empresa->id,
            'fecha_emision' => '2026-06-01',
            'fecha_vencimiento' => '2026-07-01',
        ]);

        $this->assertTrue($this->validator->validarFechas($invoice));
    }

    public function test_validar_resolucion_rechaza_resolucion_vencida(): void
    {
        $sequence = \Database\Factories\InvoiceSequenceFactory::new()->expired()->create(['empresa_id' => $this->empresa->id]);
        $invoice = Invoice::factory()->create([
            'empresa_id' => $this->empresa->id,
            'invoice_sequence_id' => $sequence->id,
        ]);

        $this->assertFalse($this->validator->validarResolucion($invoice));
    }

    public function test_validar_resolucion_rechaza_rango_excedido(): void
    {
        $sequence = \Database\Factories\InvoiceSequenceFactory::new()->create([
            'empresa_id' => $this->empresa->id,
            'rango_inicio' => 1000,
            'rango_fin' => 1100,
            'proximo_numero' => 1101,
        ]);

        $invoice = Invoice::factory()->create([
            'empresa_id' => $this->empresa->id,
            'invoice_sequence_id' => $sequence->id,
        ]);

        $this->assertFalse($this->validator->validarResolucion($invoice));
    }

    public function test_validar_detalles_rechaza_linea_con_cantidad_cero(): void
    {
        $invoice = Invoice::factory()->create(['empresa_id' => $this->empresa->id]);

        InvoiceLineItem::factory()->create([
            'invoice_id' => $invoice->id,
            'empresa_id' => $this->empresa->id,
            'cantidad' => 0,
        ]);

        $this->assertFalse($this->validator->validarDetalles($invoice));
    }
}
