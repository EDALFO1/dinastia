<?php

namespace Tests;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Base test case that provides a pre-configured user with empresa context
 */
abstract class TestCaseWithUser extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Empresa $empresa;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupUserWithEmpresa();
    }

    /**
     * Create empresa and user with proper relationships
     */
    protected function setupUserWithEmpresa(): void
    {
        // Create empresa first
        $this->empresa = Empresa::factory()->create();

        // Create user with empresa_id (NOT using factory to avoid pivot issues)
        $this->user = User::create([
            'empresa_id' => $this->empresa->id,
            'rol_id' => 1,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'estado' => 1,
        ]);
    }

    /**
     * Get authenticated user
     */
    protected function getUser(): User
    {
        return $this->user;
    }

    /**
     * Get current empresa
     */
    protected function getEmpresa(): Empresa
    {
        return $this->empresa;
    }

    /**
     * Make authenticated request with empresa header
     */
    protected function authJson(string $method, string $uri, array $data = []): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($this->user)
            ->withHeader('X-Empresa-ID', $this->empresa->id)
            ->json($method, $uri, $data);
    }

}
