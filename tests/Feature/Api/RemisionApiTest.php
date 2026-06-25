<?php

namespace Tests\Feature\Api;

use App\Models\Afiliado;
use App\Models\Empresa;
use App\Models\Remision;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RemisionApiTest extends TestCase
{
    use RefreshDatabase;

    private Empresa $empresa;
    private Empresa $otraEmpresa;
    private User $user;
    private string $token;
    private array $headers;
    private Afiliado $afiliado;

    protected function setUp(): void
    {
        parent::setUp();

        Rol::factory()->create(['id' => 1, 'nombre' => 'Admin']);

        $this->empresa = Empresa::factory()->create();
        $this->otraEmpresa = Empresa::factory()->create();

        $this->user = User::factory()->create();
        $this->user->empresas()->attach([$this->empresa->id, $this->otraEmpresa->id]);

        $this->token = $this->user->createToken('api-token')->plainTextToken;
        $this->headers = [
            'Authorization' => "Bearer {$this->token}",
            'X-Empresa-ID' => $this->empresa->id,
        ];

        $this->afiliado = Afiliado::factory()->create(['empresa_id' => $this->empresa->id]);
    }

    public function test_index_retorna_remisiones_paginadas(): void
    {
        Remision::factory(20)->create(['empresa_id' => $this->empresa->id]);

        $response = $this->getJson('/api/v1/remisiones', $this->headers);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'numero', 'fecha', 'total'],
            ],
            'meta',
            'links',
        ]);
        $this->assertCount(15, $response->json('data'));
    }

    public function test_index_filtra_por_afiliado_id(): void
    {
        $afiliado2 = Afiliado::factory()->create(['empresa_id' => $this->empresa->id]);

        Remision::factory(5)->create(['empresa_id' => $this->empresa->id, 'afiliado_id' => $this->afiliado->id]);
        Remision::factory(3)->create(['empresa_id' => $this->empresa->id, 'afiliado_id' => $afiliado2->id]);

        $response = $this->getJson("/api/v1/remisiones?afiliado_id={$this->afiliado->id}", $this->headers);

        $response->assertStatus(200);
        $this->assertCount(5, $response->json('data'));
    }

    public function test_show_retorna_remision(): void
    {
        $remision = Remision::factory()->create(['empresa_id' => $this->empresa->id]);

        $response = $this->getJson("/api/v1/remisiones/{$remision->id}", $this->headers);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $remision->id,
                'numero' => $remision->numero,
                'total' => $remision->total,
            ],
        ]);
    }

    public function test_store_crea_remision_con_numero_autoincremental(): void
    {
        $data = [
            'afiliado_id' => $this->afiliado->id,
            'fecha' => '2026-06-25',
            'dias_liquidar' => 30,
            'mensajeria' => 10000,
            'intereses' => 50000,
            'total' => 500000,
        ];

        $response1 = $this->postJson('/api/v1/remisiones', $data, $this->headers);
        $response1->assertStatus(201);
        $numero1 = $response1->json('data.numero');

        $response2 = $this->postJson('/api/v1/remisiones', $data, $this->headers);
        $response2->assertStatus(201);
        $numero2 = $response2->json('data.numero');

        $this->assertEquals($numero1 + 1, $numero2);
    }

    public function test_store_asigna_empresa_id_desde_header(): void
    {
        $data = [
            'afiliado_id' => $this->afiliado->id,
            'fecha' => '2026-06-25',
            'dias_liquidar' => 30,
            'total' => 500000,
        ];

        $response = $this->postJson('/api/v1/remisiones', $data, $this->headers);

        $response->assertStatus(201);
        $this->assertEquals($this->empresa->id, $response->json('data.empresa_id'));
    }

    public function test_store_requiere_afiliado_y_total(): void
    {
        $data = [
            'fecha' => '2026-06-25',
            'dias_liquidar' => 30,
        ];

        $response = $this->postJson('/api/v1/remisiones', $data, $this->headers);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['afiliado_id', 'total']);
    }

    public function test_update_modifica_remision(): void
    {
        $remision = Remision::factory()->create(['empresa_id' => $this->empresa->id]);

        $data = [
            'fecha' => '2026-07-25',
            'dias_liquidar' => 30,
            'total' => 600000,
        ];

        $response = $this->putJson("/api/v1/remisiones/{$remision->id}", $data, $this->headers);

        $response->assertStatus(200);
        $this->assertDatabaseHas('remisiones', [
            'id' => $remision->id,
            'fecha' => '2026-07-25',
            'total' => 600000,
        ]);
    }

    public function test_destroy_elimina_remision_y_detalles(): void
    {
        $remision = Remision::factory()->create(['empresa_id' => $this->empresa->id]);
        $remision->detalles()->create([
            'empresa_id' => $this->empresa->id,
            'concepto' => 'PILA',
            'valor' => 100000,
        ]);

        $response = $this->deleteJson("/api/v1/remisiones/{$remision->id}", [], $this->headers);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('remisiones', ['id' => $remision->id]);
        $this->assertDatabaseMissing('remision_detalles', ['remision_id' => $remision->id]);
    }

    public function test_aislamiento_cross_tenant(): void
    {
        $remision = Remision::factory()->create(['empresa_id' => $this->otraEmpresa->id]);

        $response = $this->getJson("/api/v1/remisiones/{$remision->id}", $this->headers);

        $response->assertStatus(404);
    }
}
