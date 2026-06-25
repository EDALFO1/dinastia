<?php

namespace Tests\Feature;

use App\Models\Afiliado;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AfiliadoTest extends TestCase
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
     * Test listing afiliados
     */
    public function test_lista_afiliados(): void
    {
        session(['empresa_id' => $this->empresa->id]);

        Afiliado::factory(3)->create(['empresa_id' => $this->empresa->id]);

        $response = $this->actingAs($this->user)->get('/afiliados');

        $response->assertStatus(200);
    }

    /**
     * Test create afiliado view
     */
    public function test_crear_afiliado_view(): void
    {
        session(['empresa_id' => $this->empresa->id]);

        $response = $this->actingAs($this->user)->get('/afiliados/create');

        $response->assertStatus(200);
    }

    /**
     * Test store afiliado
     */
    public function test_guardar_afiliado(): void
    {
        session(['empresa_id' => $this->empresa->id]);

        $data = [
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'numero_documento' => '1234567890',
            'tipo_documento' => 'CC',
            'estado' => 'activo',
            'fecha_ingreso' => '2026-01-01',
            'salario' => 2500000,
        ];

        $response = $this->actingAs($this->user)->post('/afiliados', $data);

        $response->assertStatus(302); // Redirect after creation

        $this->assertDatabaseHas('afiliados', [
            'numero_documento' => '1234567890',
            'empresa_id' => $this->empresa->id,
        ]);
    }

    /**
     * Test show afiliado from own empresa
     */
    public function test_mostrar_afiliado_propia_empresa(): void
    {
        session(['empresa_id' => $this->empresa->id]);

        $afiliado = Afiliado::factory()->create(['empresa_id' => $this->empresa->id]);

        $response = $this->actingAs($this->user)->get("/afiliados/{$afiliado->id}");

        $response->assertStatus(200);
    }

    /**
     * Test show afiliado from another empresa (should not be found)
     */
    public function test_mostrar_afiliado_otra_empresa_retorna_404(): void
    {
        $otra_empresa = Empresa::factory()->create();
        $afiliado = Afiliado::factory()->create(['empresa_id' => $otra_empresa->id]);

        session(['empresa_id' => $this->empresa->id]);

        $response = $this->actingAs($this->user)->get("/afiliados/{$afiliado->id}");

        // Should return 404 because the global scope filters it out
        $response->assertStatus(404);
    }
}
