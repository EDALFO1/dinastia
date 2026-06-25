<?php

namespace Tests\Feature\Api;

use App\Models\Afiliado;
use App\Models\Documento;
use App\Models\Empresa;
use App\Models\EmpresaLaboral;
use App\Models\Rol;
use App\Models\SubtipoCotizante;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AfiliadoApiTest extends TestCase
{
    use RefreshDatabase;

    private Empresa $empresa;
    private Empresa $otraEmpresa;
    private User $user;
    private string $token;
    private array $headers;

    protected function setUp(): void
    {
        parent::setUp();

        Rol::factory()->create(['id' => 1, 'nombre' => 'Admin']);

        $this->empresa = Empresa::factory()->create();
        $this->otraEmpresa = Empresa::factory()->create();

        $this->user = User::factory()->create();
        $this->user->empresas()->attach([$this->empresa->id, $this->otraEmpresa->id]);

        // Set current_empresa_id on the user for scoping
        $this->user->current_empresa_id = $this->empresa->id;

        $this->token = $this->user->createToken('api-token')->plainTextToken;
        $this->headers = [
            'Authorization' => "Bearer {$this->token}",
            'X-Empresa-ID' => $this->empresa->id,
        ];
    }

    public function test_index_retorna_lista_paginada(): void
    {
        Afiliado::factory(20)->create(['empresa_id' => $this->empresa->id]);

        $response = $this->getJson('/api/v1/afiliados', $this->headers);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'numero_documento', 'primer_nombre', 'primer_apellido'],
            ],
            'meta',
            'links',
        ]);
        $this->assertCount(15, $response->json('data'));
    }

    public function test_index_solo_ve_datos_de_su_empresa(): void
    {
        Afiliado::factory(5)->create(['empresa_id' => $this->empresa->id]);
        Afiliado::factory(5)->create(['empresa_id' => $this->otraEmpresa->id]);

        $response = $this->getJson('/api/v1/afiliados', $this->headers);

        $response->assertStatus(200);
        $this->assertCount(5, $response->json('data'));
    }

    public function test_index_filtra_por_estado(): void
    {
        Afiliado::factory(5)->create(['empresa_id' => $this->empresa->id, 'estado' => true]);
        Afiliado::factory(3)->create(['empresa_id' => $this->empresa->id, 'estado' => false]);

        $response = $this->getJson('/api/v1/afiliados?estado=1', $this->headers);

        $response->assertStatus(200);
        $this->assertCount(5, $response->json('data'));
    }

    public function test_index_busca_por_nombre(): void
    {
        $afiliado = Afiliado::factory()->create([
            'empresa_id' => $this->empresa->id,
            'primer_nombre' => 'Juan',
            'primer_apellido' => 'Pérez',
        ]);
        Afiliado::factory(3)->create(['empresa_id' => $this->empresa->id]);

        $response = $this->getJson('/api/v1/afiliados?q=Juan', $this->headers);

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($afiliado->id, $response->json('data.0.id'));
    }

    public function test_show_retorna_afiliado(): void
    {
        $afiliado = Afiliado::factory()->create(['empresa_id' => $this->empresa->id]);

        $response = $this->getJson("/api/v1/afiliados/{$afiliado->id}", $this->headers);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $afiliado->id,
                'numero_documento' => $afiliado->numero_documento,
            ],
        ]);
    }

    public function test_show_afiliado_otra_empresa_retorna_404(): void
    {
        $afiliado = Afiliado::factory()->create(['empresa_id' => $this->otraEmpresa->id]);

        $response = $this->getJson("/api/v1/afiliados/{$afiliado->id}", $this->headers);

        $response->assertStatus(404);
    }

    public function test_store_crea_afiliado(): void
    {
        $empresaLaboral = EmpresaLaboral::factory()->create(['empresa_id' => $this->empresa->id]);
        $documento = Documento::factory()->create();
        $subtipo = SubtipoCotizante::factory()->create();

        $data = Afiliado::factory()->make([
            'empresa_laboral_id' => $empresaLaboral->id,
            'documento_id' => $documento->id,
            'subtipo_cotizante_id' => $subtipo->id,
        ])->toArray();
        unset($data['empresa_id']);

        $response = $this->postJson('/api/v1/afiliados', $data, $this->headers);

        $response->assertStatus(201);
        $this->assertDatabaseHas('afiliados', [
            'numero_documento' => $data['numero_documento'],
            'empresa_id' => $this->empresa->id,
        ]);
    }

    public function test_store_asigna_empresa_id_correctamente(): void
    {
        $empresaLaboral = EmpresaLaboral::factory()->create(['empresa_id' => $this->empresa->id]);
        $documento = Documento::factory()->create();
        $subtipo = SubtipoCotizante::factory()->create();

        $data = Afiliado::factory()->make([
            'empresa_laboral_id' => $empresaLaboral->id,
            'documento_id' => $documento->id,
            'subtipo_cotizante_id' => $subtipo->id,
        ])->toArray();
        unset($data['empresa_id']);

        $response = $this->postJson('/api/v1/afiliados', $data, $this->headers);

        $response->assertStatus(201);
        $this->assertEquals($this->empresa->id, $response->json('data.empresa_id'));
    }

    public function test_store_documento_duplicado_retorna_422(): void
    {
        $afiliado1 = Afiliado::factory()->create(['empresa_id' => $this->empresa->id]);

        $empresaLaboral = EmpresaLaboral::factory()->create(['empresa_id' => $this->empresa->id]);
        $documento = Documento::factory()->create();
        $subtipo = SubtipoCotizante::factory()->create();

        $data = Afiliado::factory()->make([
            'numero_documento' => $afiliado1->numero_documento,
            'empresa_laboral_id' => $empresaLaboral->id,
            'documento_id' => $documento->id,
            'subtipo_cotizante_id' => $subtipo->id,
        ])->toArray();
        unset($data['empresa_id']);

        $response = $this->postJson('/api/v1/afiliados', $data, $this->headers);

        $response->assertStatus(422);
    }

    public function test_update_modifica_afiliado(): void
    {
        $afiliado = Afiliado::factory()->create(['empresa_id' => $this->empresa->id]);

        $data = [
            'empresa_laboral_id' => $afiliado->empresa_laboral_id,
            'documento_id' => $afiliado->documento_id,
            'subtipo_cotizante_id' => $afiliado->subtipo_cotizante_id,
            'numero_documento' => $afiliado->numero_documento,
            'primer_nombre' => 'Nuevo Nombre',
            'primer_apellido' => $afiliado->primer_apellido,
            'fecha_nacimiento' => $afiliado->fecha_nacimiento->format('Y-m-d'),
            'sexo' => 'M',
        ];

        $response = $this->putJson("/api/v1/afiliados/{$afiliado->id}", $data, $this->headers);

        $response->assertStatus(200);
        $this->assertDatabaseHas('afiliados', [
            'id' => $afiliado->id,
            'primer_nombre' => 'Nuevo Nombre',
        ]);
    }

    public function test_update_afiliado_otra_empresa_retorna_404(): void
    {
        $afiliado = Afiliado::factory()->create(['empresa_id' => $this->otraEmpresa->id]);

        $data = ['primer_nombre' => 'Nuevo Nombre'];

        $response = $this->putJson("/api/v1/afiliados/{$afiliado->id}", $data, $this->headers);

        $response->assertStatus(404);
    }

    public function test_destroy_elimina_afiliado(): void
    {
        $afiliado = Afiliado::factory()->create(['empresa_id' => $this->empresa->id]);

        $response = $this->deleteJson("/api/v1/afiliados/{$afiliado->id}", [], $this->headers);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('afiliados', ['id' => $afiliado->id]);
    }

    public function test_sin_token_retorna_401(): void
    {
        $response = $this->getJson('/api/v1/afiliados', [
            'X-Empresa-ID' => $this->empresa->id,
        ]);

        $response->assertStatus(401);
    }

    public function test_sin_empresa_header_retorna_422(): void
    {
        $response = $this->getJson('/api/v1/afiliados', [
            'Authorization' => "Bearer {$this->token}",
        ]);

        $response->assertStatus(422);
    }

    public function test_recibos_retorna_lista_paginada(): void
    {
        $afiliado = Afiliado::factory()->create(['empresa_id' => $this->empresa->id]);
        $afiliado->recibos()->createMany(
            \App\Models\Recibo::factory(20)->make(['empresa_id' => $this->empresa->id])->toArray()
        );

        $response = $this->getJson("/api/v1/afiliados/{$afiliado->id}/recibos", $this->headers);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'numero', 'fecha', 'total'],
            ],
            'meta',
            'links',
        ]);
    }
}
