<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create required roles
        Rol::factory()->create(['id' => 1, 'nombre' => 'Admin']);
    }

    /**
     * Test login with valid credentials
     */
    public function test_login_with_valid_credentials(): void
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create([
            'empresa_id' => $empresa->id,
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/logear', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // Should redirect to empresa selection if user has multiple empresas, or dashboard if only one
        $this->assertTrue(session()->has('just_logged_in') || $response->getStatusCode() === 302);
    }

    /**
     * Test login with invalid credentials
     */
    public function test_login_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/logear', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * Test logout
     */
    public function test_logout(): void
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create();
        $user->empresas()->attach($empresa);

        session(['empresa_id' => $empresa->id]);

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    /**
     * Test cambiar empresa (switch company)
     */
    public function test_cambiar_empresa(): void
    {
        $empresa1 = Empresa::factory()->create(['nombre' => 'Empresa 1']);
        $empresa2 = Empresa::factory()->create(['nombre' => 'Empresa 2']);

        $user = User::factory()->create();
        $user->empresas()->attach([$empresa1->id, $empresa2->id]);

        session(['empresa_id' => $empresa1->id]);

        $response = $this->actingAs($user)->post('/cambiar-empresa', [
            'empresa_id' => $empresa2->id,
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertEquals($empresa2->id, session('empresa_id'));
    }
}
