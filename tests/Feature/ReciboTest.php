<?php

namespace Tests\Feature;

use App\Models\Afiliado;
use App\Models\Empresa;
use App\Models\Recibo;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReciboTest extends TestCase
{
    use RefreshDatabase;

    private Empresa $empresa;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Rol::factory()->create(['id' => 1, 'nombre' => 'Admin']);
        $this->empresa = Empresa::factory()->create();
        $this->user = User::factory()->create(['empresa_id' => $this->empresa->id]);
    }

    /**
     * Test listing recibos
     */
    public function test_lista_recibos(): void
    {
        session(['empresa_id' => $this->empresa->id]);

        Recibo::factory(2)->create(['empresa_id' => $this->empresa->id]);

        $response = $this->actingAs($this->user)->get('/recibos');

        $response->assertStatus(200);
    }

    /**
     * Test create recibo view
     */
    public function test_crear_recibo_view(): void
    {
        session(['empresa_id' => $this->empresa->id]);

        $response = $this->actingAs($this->user)->get('/recibos/create');

        $response->assertStatus(200);
    }

    /**
     * Test store recibo
     */
    public function test_guardar_recibo(): void
    {
        session(['empresa_id' => $this->empresa->id]);

        $afiliado = Afiliado::factory()->create(['empresa_id' => $this->empresa->id]);

        $data = [
            'afiliado_id' => $afiliado->id,
            'numero' => 'REC-001',
            'periodo' => '2026-06',
            'salario' => 3000000,
            'salario_neto' => 2400000,
            'aporte_eps' => 150000,
            'aporte_arl' => 60000,
            'aporte_pension' => 300000,
            'estado' => 'generado',
        ];

        $response = $this->actingAs($this->user)->post('/recibos', $data);

        $response->assertStatus(302);

        $this->assertDatabaseHas('recibos', [
            'numero' => 'REC-001',
            'empresa_id' => $this->empresa->id,
        ]);
    }

    /**
     * Test show recibo
     */
    public function test_mostrar_recibo(): void
    {
        session(['empresa_id' => $this->empresa->id]);

        $recibo = Recibo::factory()->create(['empresa_id' => $this->empresa->id]);

        $response = $this->actingAs($this->user)->get("/recibos/{$recibo->id}");

        $response->assertStatus(200);
    }

    /**
     * Test that recibo from another empresa is not visible
     */
    public function test_recibo_otra_empresa_retorna_404(): void
    {
        $otra_empresa = Empresa::factory()->create();
        $recibo = Recibo::factory()->create(['empresa_id' => $otra_empresa->id]);

        session(['empresa_id' => $this->empresa->id]);

        $response = $this->actingAs($this->user)->get("/recibos/{$recibo->id}");

        $response->assertStatus(404);
    }
}
