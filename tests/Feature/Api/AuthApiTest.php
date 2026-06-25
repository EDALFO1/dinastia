<?php

namespace Tests\Feature\Api;

use App\Models\Empresa;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Rol::factory()->create(['id' => 1, 'nombre' => 'Admin']);
    }

    public function test_login_devuelve_token(): void
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create([
            'empresa_id' => $empresa->id,
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token', 'user', 'empresas']);

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    }

    public function test_login_credenciales_invalidas(): void
    {
        User::factory()->create([
            'empresa_id' => Empresa::factory(),
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('message', 'Credenciales inválidas');
    }

    public function test_logout_revoca_token(): void
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create(['empresa_id' => $empresa->id]);
        $token = $user->createToken('api-token')->plainTextToken;

        $response = $this->postJson('/api/v1/auth/logout', [], [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'token' => hash('sha256', $token),
        ]);
    }

    public function test_me_retorna_usuario_autenticado(): void
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create(['empresa_id' => $empresa->id]);
        $token = $user->createToken('api-token')->plainTextToken;

        $response = $this->getJson('/api/v1/auth/me', [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_empresas_retorna_lista(): void
    {
        $empresa1 = Empresa::factory()->create();
        $empresa2 = Empresa::factory()->create();
        $user = User::factory()->create(['empresa_id' => $empresa1->id]);
        $user->empresas()->attach([$empresa1->id, $empresa2->id]);
        $token = $user->createToken('api-token')->plainTextToken;

        $response = $this->getJson('/api/v1/auth/empresas', [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_request_sin_empresa_header_retorna_422(): void
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create(['empresa_id' => $empresa->id]);
        $token = $user->createToken('api-token')->plainTextToken;

        $response = $this->getJson('/api/v1/empresas/current', [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'X-Empresa-ID header required');
    }

    public function test_request_con_empresa_invalida_retorna_403(): void
    {
        $empresa1 = Empresa::factory()->create();
        $empresa2 = Empresa::factory()->create();
        $user = User::factory()->create(['empresa_id' => $empresa1->id]);
        $token = $user->createToken('api-token')->plainTextToken;

        $response = $this->getJson('/api/v1/empresas/current', [
            'Authorization' => "Bearer $token",
            'X-Empresa-ID' => $empresa2->id,
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('message', 'Unauthorized: empresa access denied');
    }

    public function test_token_expiration(): void
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create(['empresa_id' => $empresa->id]);

        $response = $this->getJson('/api/v1/auth/me', [
            'Authorization' => 'Bearer invalid-token',
        ]);

        $response->assertStatus(401);
    }
}
