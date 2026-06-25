<?php

namespace Tests\Feature\Api;

use App\Models\Afiliado;
use App\Models\Empresa;
use App\Models\Recibo;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReciboApiTest extends TestCase
{
    use RefreshDatabase;

    private Empresa $empresa;
    private User $user;
    private string $token;
    private array $headers;
    private Afiliado $afiliado;

    protected function setUp(): void
    {
        parent::setUp();

        Rol::factory()->create(['id' => 1, 'nombre' => 'Admin']);

        $this->empresa = Empresa::factory()->create();
        $this->user = User::factory()->create();
        $this->user->empresas()->attach($this->empresa->id);

        $this->token = $this->user->createToken('api-token')->plainTextToken;
        $this->headers = [
            'Authorization' => "Bearer {$this->token}",
            'X-Empresa-ID' => $this->empresa->id,
        ];

        $this->afiliado = Afiliado::factory()->create(['empresa_id' => $this->empresa->id]);
    }

    public function test_index_retorna_recibos_paginados(): void
    {
        Recibo::factory(20)->create(['empresa_id' => $this->empresa->id]);

        $response = $this->getJson('/api/v1/recibos', $this->headers);

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

        Recibo::factory(5)->create(['empresa_id' => $this->empresa->id, 'afiliado_id' => $this->afiliado->id]);
        Recibo::factory(3)->create(['empresa_id' => $this->empresa->id, 'afiliado_id' => $afiliado2->id]);

        $response = $this->getJson("/api/v1/recibos?afiliado_id={$this->afiliado->id}", $this->headers);

        $response->assertStatus(200);
        $this->assertCount(5, $response->json('data'));
    }

    public function test_show_retorna_recibo(): void
    {
        $recibo = Recibo::factory()->create(['empresa_id' => $this->empresa->id]);

        $response = $this->getJson("/api/v1/recibos/{$recibo->id}", $this->headers);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $recibo->id,
                'numero' => $recibo->numero,
                'total' => $recibo->total,
            ],
        ]);
    }

    public function test_store_crea_recibo_con_numero_autoincremental(): void
    {
        $data = [
            'afiliado_id' => $this->afiliado->id,
            'fecha' => '2026-06-25',
            'dias_liquidar' => 30,
            'ibc' => 3000000,
            'valor_eps' => 120000,
            'valor_arl' => 30000,
            'valor_pension' => 480000,
            'valor_caja' => 120000,
            'valor_admon' => 120000,
            'valor_servicios' => 0,
            'total' => 1930000,
        ];

        $response1 = $this->postJson('/api/v1/recibos', $data, $this->headers);
        $response1->assertStatus(201);
        $numero1 = $response1->json('data.numero');

        $response2 = $this->postJson('/api/v1/recibos', $data, $this->headers);
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
            'ibc' => 3000000,
            'total' => 1930000,
        ];

        $response = $this->postJson('/api/v1/recibos', $data, $this->headers);

        $response->assertStatus(201);
        $this->assertEquals($this->empresa->id, $response->json('data.empresa_id'));
    }

    public function test_store_requiere_campos_obligatorios(): void
    {
        $data = [
            'fecha' => '2026-06-25',
            'dias_liquidar' => 30,
        ];

        $response = $this->postJson('/api/v1/recibos', $data, $this->headers);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['afiliado_id', 'ibc', 'total']);
    }

    public function test_update_modifica_recibo(): void
    {
        $recibo = Recibo::factory()->create(['empresa_id' => $this->empresa->id]);

        $data = [
            'afiliado_id' => $recibo->afiliado_id,
            'fecha' => '2026-07-25',
            'dias_liquidar' => 30,
            'ibc' => 4000000,
            'total' => 2500000,
        ];

        $response = $this->putJson("/api/v1/recibos/{$recibo->id}", $data, $this->headers);

        $response->assertStatus(200);
        // Verify the recibo was updated
        $updated = Recibo::find($recibo->id);
        $this->assertEquals('2026-07-25', $updated->fecha->format('Y-m-d'));
        $this->assertEquals(4000000, $updated->ibc);
    }

    public function test_recibo_exportado_no_se_puede_modificar(): void
    {
        $batch = \App\Models\ExportBatch::factory()->create(['empresa_id' => $this->empresa->id]);
        $recibo = Recibo::factory()->create([
            'empresa_id' => $this->empresa->id,
            'export_batch_id' => $batch->id,
        ]);

        $data = ['total' => 5000000];

        $response = $this->putJson("/api/v1/recibos/{$recibo->id}", $data, $this->headers);

        $response->assertStatus(422);
    }

    public function test_destroy_elimina_recibo(): void
    {
        $recibo = Recibo::factory()->create(['empresa_id' => $this->empresa->id]);

        $response = $this->deleteJson("/api/v1/recibos/{$recibo->id}", [], $this->headers);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('recibos', ['id' => $recibo->id]);
    }

    public function test_recibo_exportado_no_se_puede_eliminar(): void
    {
        $batch = \App\Models\ExportBatch::factory()->create(['empresa_id' => $this->empresa->id]);
        $recibo = Recibo::factory()->create([
            'empresa_id' => $this->empresa->id,
            'export_batch_id' => $batch->id,
        ]);

        $response = $this->deleteJson("/api/v1/recibos/{$recibo->id}", [], $this->headers);

        $response->assertStatus(422);
    }
}
