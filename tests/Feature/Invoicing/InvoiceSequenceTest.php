<?php

namespace Tests\Feature\Invoicing;

use App\Domains\Invoicing\Models\InvoiceSequence;
use App\Models\Empresa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceSequenceTest extends TestCase
{
    use RefreshDatabase;

    protected Empresa $empresa;

    protected function setUp(): void
    {
        parent::setUp();
        $this->empresa = Empresa::factory()->create();
    }

    public function test_puede_crear_secuencia_de_facturacion(): void
    {
        $sequence = InvoiceSequence::factory()->create(['empresa_id' => $this->empresa->id]);

        $this->assertDatabaseHas('invoice_sequences', [
            'id' => $sequence->id,
            'empresa_id' => $this->empresa->id,
            'estado' => 'activa',
        ]);
    }

    public function test_get_next_number_incrementa_secuencia_con_lock(): void
    {
        $sequence = InvoiceSequence::factory()->create([
            'empresa_id' => $this->empresa->id,
            'rango_inicio' => 1000,
            'proximo_numero' => 1000,
        ]);

        $numero1 = $sequence->getNextNumber();
        $numero2 = $sequence->getNextNumber();

        $this->assertEquals($numero1, 1001);
        $this->assertEquals($numero2, 1002);
    }

    public function test_is_active_valida_vigencia_de_resolucion(): void
    {
        $active = InvoiceSequence::factory()->create(['empresa_id' => $this->empresa->id, 'estado' => 'activa']);
        $expired = InvoiceSequence::factory()->expired()->create(['empresa_id' => $this->empresa->id]);

        $this->assertTrue($active->isActive());
        $this->assertFalse($expired->isActive());
    }

    public function test_get_range_status_calcula_porcentaje_de_uso(): void
    {
        $sequence = InvoiceSequence::factory()->create([
            'empresa_id' => $this->empresa->id,
            'rango_inicio' => 1000,
            'rango_fin' => 1099,
            'proximo_numero' => 1050,
        ]);

        $status = $sequence->getRangeStatus();
        $this->assertEqualsWithDelta($status, 50.0, 1.0);
    }
}
