<?php

namespace Tests\Feature;

use App\Models\Afiliado;
use App\Models\Empresa;
use App\Models\Remision;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RemisionTest extends TestCase
{
    use RefreshDatabase;

    private Empresa $empresa;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->empresa = Empresa::factory()->create();
        $this->user = User::factory()->create();
        $this->user->empresas()->attach($this->empresa);
    }

    /**
     * Test listing remisiones
     */
    public function test_lista_remisiones(): void
    {
        session(['empresa_id' => $this->empresa->id]);

        Remision::factory(2)->create(['empresa_id' => $this->empresa->id]);

        $response = $this->actingAs($this->user)->get('/remisiones');

        $response->assertStatus(200);
    }

    /**
     * Test create remision view
     */
    public function test_crear_remision_view(): void
    {
        session(['empresa_id' => $this->empresa->id]);

        $response = $this->actingAs($this->user)->get('/remisiones/create');

        $response->assertStatus(200);
    }

    /**
     * Test store remision
     */
    public function test_guardar_remision(): void
    {
        session(['empresa_id' => $this->empresa->id]);

        $afiliado = Afiliado::factory()->create(['empresa_id' => $this->empresa->id]);

        $data = [
            'afiliado_id' => $afiliado->id,
            'numero' => 'REM-001',
            'fecha' => '2026-06-25',
            'tipo' => 'PILA',
            'estado' => 'generada',
        ];

        $response = $this->actingAs($this->user)->post('/remisiones', $data);

        $response->assertStatus(302);

        $this->assertDatabaseHas('remisiones', [
            'numero' => 'REM-001',
            'empresa_id' => $this->empresa->id,
        ]);
    }

    /**
     * Test show remision
     */
    public function test_mostrar_remision(): void
    {
        session(['empresa_id' => $this->empresa->id]);

        $remision = Remision::factory()->create(['empresa_id' => $this->empresa->id]);

        $response = $this->actingAs($this->user)->get("/remisiones/{$remision->id}");

        $response->assertStatus(200);
    }

    /**
     * Test that remision from another empresa is not visible
     */
    public function test_remision_otra_empresa_retorna_404(): void
    {
        $otra_empresa = Empresa::factory()->create();
        $remision = Remision::factory()->create(['empresa_id' => $otra_empresa->id]);

        session(['empresa_id' => $this->empresa->id]);

        $response = $this->actingAs($this->user)->get("/remisiones/{$remision->id}");

        $response->assertStatus(404);
    }
}
