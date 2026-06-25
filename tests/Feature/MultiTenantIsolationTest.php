<?php

namespace Tests\Feature;

use App\Models\Afiliado;
use App\Models\Empresa;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiTenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Rol::factory()->create(['id' => 1, 'nombre' => 'Admin']);
    }

    /**
     * Verify that users from empresa A cannot see data from empresa B
     */
    public function test_usuario_solo_ve_datos_de_su_empresa(): void
    {
        // Create two companies
        $empresa_a = Empresa::factory()->create(['nombre' => 'Empresa A']);
        $empresa_b = Empresa::factory()->create(['nombre' => 'Empresa B']);

        // Create users for each company
        $user_a = User::factory()->create(['empresa_id' => $empresa_a->id, 'email' => 'user_a@test.com']);
        $user_b = User::factory()->create(['empresa_id' => $empresa_b->id, 'email' => 'user_b@test.com']);

        // Create afiliados for each company
        $afiliado_a = Afiliado::factory()->create([
            'empresa_id' => $empresa_a->id,
            'nombre' => 'Afiliado de Empresa A'
        ]);

        $afiliado_b = Afiliado::factory()->create([
            'empresa_id' => $empresa_b->id,
            'nombre' => 'Afiliado de Empresa B'
        ]);

        // Login as user_a and set session
        session(['empresa_id' => $empresa_a->id]);
        $this->actingAs($user_a);

        // User A should see their company's data
        $response = $this->get('/afiliados');
        $response->assertStatus(200);

        // Verify data isolation: Query returns only Empresa A's afiliado
        $afiliados = Afiliado::all();
        $this->assertCount(1, $afiliados);
        $this->assertEquals($afiliado_a->id, $afiliados->first()->id);
    }

    /**
     * Verify multi-tenant scope is applied correctly to queries
     */
    public function test_basemodel_applies_empresa_scope(): void
    {
        $empresa1 = Empresa::factory()->create();
        $empresa2 = Empresa::factory()->create();

        // Create afiliados in both companies
        Afiliado::factory(3)->create(['empresa_id' => $empresa1->id]);
        Afiliado::factory(2)->create(['empresa_id' => $empresa2->id]);

        // Query without session should return empty (no empresa_id set)
        $all_afiliados = Afiliado::all();
        $this->assertCount(0, $all_afiliados);

        // Query with session empresa_id should return only that company's data
        session(['empresa_id' => $empresa1->id]);
        $empresa1_afiliados = Afiliado::all();
        $this->assertCount(3, $empresa1_afiliados);

        session(['empresa_id' => $empresa2->id]);
        $empresa2_afiliados = Afiliado::all();
        $this->assertCount(2, $empresa2_afiliados);
    }
}
