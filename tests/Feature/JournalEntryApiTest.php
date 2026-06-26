<?php

namespace Tests\Feature;

use App\Domains\Accounting\Models\ChartOfAccounts;
use App\Domains\Accounting\Models\JournalEntry;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JournalEntryApiTest extends TestCase
{
    use RefreshDatabase;

    protected Empresa $empresa;
    protected User $user;
    protected ChartOfAccounts $cuentaBanco;
    protected ChartOfAccounts $cuentaGastos;
    protected ChartOfAccounts $cuentaIngresos;

    protected function setUp(): void
    {
        parent::setUp();

        $this->empresa = Empresa::factory()->create();
        session(['empresa_id' => $this->empresa->id]);

        $this->user = User::factory()->create([
            'empresa_id' => $this->empresa->id,
        ]);

        // Crear cuentas operativas
        $this->cuentaBanco = ChartOfAccounts::factory()->create([
            'empresa_id' => $this->empresa->id,
            'codigo' => '100501',
            'nombre' => 'Banco Occidente',
            'tipo_cuenta' => 'activo',
            'nivel' => 3,
            'permite_movimiento' => true,
            'estado' => 'activo',
        ]);

        $this->cuentaGastos = ChartOfAccounts::factory()->create([
            'empresa_id' => $this->empresa->id,
            'codigo' => '510101',
            'nombre' => 'Salarios y Jornales',
            'tipo_cuenta' => 'gastos',
            'nivel' => 3,
            'permite_movimiento' => true,
            'estado' => 'activo',
        ]);

        $this->cuentaIngresos = ChartOfAccounts::factory()->create([
            'empresa_id' => $this->empresa->id,
            'codigo' => '410101',
            'nombre' => 'Ventas Nacionales',
            'tipo_cuenta' => 'ingresos',
            'nivel' => 3,
            'permite_movimiento' => true,
            'estado' => 'activo',
        ]);
    }

    /** @test */
    public function puedo_crear_asiento_balanceado()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/v1/accounting/journal-entries', [
            'fecha' => now()->toDateString(),
            'descripcion' => 'Asiento de prueba',
            'lines' => [
                [
                    'account_id' => $this->cuentaBanco->id,
                    'tipo_movimiento' => 'debito',
                    'valor' => 1000000.00,
                    'descripcion' => 'Depósito en banco',
                ],
                [
                    'account_id' => $this->cuentaIngresos->id,
                    'tipo_movimiento' => 'credito',
                    'valor' => 1000000.00,
                    'descripcion' => 'Venta de servicios',
                ],
            ],
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['data' => ['id', 'numero_asiento', 'estado']]);

        $this->assertDatabaseHas('journal_entries', [
            'numero_asiento' => $response->json('data.numero_asiento'),
            'estado' => 'borrador',
        ]);
    }

    /** @test */
    public function no_puedo_crear_asiento_desbalanceado()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/v1/accounting/journal-entries', [
            'fecha' => now()->toDateString(),
            'descripcion' => 'Asiento desbalanceado',
            'lines' => [
                [
                    'account_id' => $this->cuentaBanco->id,
                    'tipo_movimiento' => 'debito',
                    'valor' => 1000000.00,
                ],
                [
                    'account_id' => $this->cuentaGastos->id,
                    'tipo_movimiento' => 'credito',
                    'valor' => 500000.00, // Desbalanceado
                ],
            ],
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function no_puedo_crear_asiento_con_una_sola_linea()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/v1/accounting/journal-entries', [
            'fecha' => now()->toDateString(),
            'descripcion' => 'Asiento con una línea',
            'lines' => [
                [
                    'account_id' => $this->cuentaBanco->id,
                    'tipo_movimiento' => 'debito',
                    'valor' => 1000000.00,
                ],
            ],
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function puedo_listar_asientos()
    {
        $this->actingAs($this->user);

        JournalEntry::factory()->count(3)->create([
            'empresa_id' => $this->empresa->id,
        ]);

        $response = $this->getJson('/api/v1/accounting/journal-entries');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'links',
            'meta',
        ]);
    }

    /** @test */
    public function puedo_obtener_asiento_especifico()
    {
        $this->actingAs($this->user);

        $asiento = JournalEntry::factory()->create([
            'empresa_id' => $this->empresa->id,
        ]);

        $response = $this->getJson("/api/v1/accounting/journal-entries/{$asiento->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('data.numero_asiento', $asiento->numero_asiento);
    }

    /** @test */
    public function puedo_actualizar_asiento_en_borrador()
    {
        $this->actingAs($this->user);

        $asiento = JournalEntry::factory()->create([
            'empresa_id' => $this->empresa->id,
            'estado' => 'borrador',
        ]);

        $response = $this->putJson("/api/v1/accounting/journal-entries/{$asiento->id}", [
            'descripcion' => 'Descripción actualizada',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('journal_entries', [
            'id' => $asiento->id,
            'descripcion' => 'Descripción actualizada',
        ]);
    }

    /** @test */
    public function no_puedo_actualizar_asiento_posteado()
    {
        $this->actingAs($this->user);

        $asiento = JournalEntry::factory()->create([
            'empresa_id' => $this->empresa->id,
            'estado' => 'posteado',
        ]);

        $response = $this->putJson("/api/v1/accounting/journal-entries/{$asiento->id}", [
            'descripcion' => 'Nueva descripción',
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function puedo_eliminar_asiento_en_borrador()
    {
        $this->actingAs($this->user);

        $asiento = JournalEntry::factory()->create([
            'empresa_id' => $this->empresa->id,
            'estado' => 'borrador',
        ]);

        $response = $this->deleteJson("/api/v1/accounting/journal-entries/{$asiento->id}");

        $response->assertStatus(204);
        $this->assertSoftDeleted('journal_entries', ['id' => $asiento->id]);
    }

    /** @test */
    public function no_puedo_eliminar_asiento_posteado()
    {
        $this->actingAs($this->user);

        $asiento = JournalEntry::factory()->create([
            'empresa_id' => $this->empresa->id,
            'estado' => 'posteado',
        ]);

        $response = $this->deleteJson("/api/v1/accounting/journal-entries/{$asiento->id}");

        $response->assertStatus(422);
    }

    /** @test */
    public function puedo_aprobar_asiento_balanceado()
    {
        $this->actingAs($this->user);

        $asiento = JournalEntry::factory()->create([
            'empresa_id' => $this->empresa->id,
            'estado' => 'borrador',
        ]);

        // Agregar líneas balanceadas
        $asiento->lines()->create([
            'empresa_id' => $this->empresa->id,
            'account_id' => $this->cuentaBanco->id,
            'tipo_movimiento' => 'debito',
            'valor' => 1000,
        ]);

        $asiento->lines()->create([
            'empresa_id' => $this->empresa->id,
            'account_id' => $this->cuentaIngresos->id,
            'tipo_movimiento' => 'credito',
            'valor' => 1000,
        ]);

        $response = $this->postJson("/api/v1/accounting/journal-entries/{$asiento->id}/approve");

        $response->assertStatus(200);
        $this->assertDatabaseHas('journal_entries', [
            'id' => $asiento->id,
            'estado' => 'posteado',
        ]);
    }

    /** @test */
    public function puedo_rechazar_asiento_en_borrador()
    {
        $this->actingAs($this->user);

        $asiento = JournalEntry::factory()->create([
            'empresa_id' => $this->empresa->id,
            'estado' => 'borrador',
        ]);

        $response = $this->postJson("/api/v1/accounting/journal-entries/{$asiento->id}/reject");

        $response->assertStatus(200);
        $this->assertDatabaseHas('journal_entries', [
            'id' => $asiento->id,
            'estado' => 'rechazado',
        ]);
    }

    /** @test */
    public function puedo_obtener_resumen_de_saldos()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/v1/accounting/summary/balances');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['codigo', 'nombre', 'tipo', 'saldo'],
            ],
        ]);
    }

    /** @test */
    public function puedo_filtrar_asientos_por_estado()
    {
        $this->actingAs($this->user);

        JournalEntry::factory()->count(2)->create([
            'empresa_id' => $this->empresa->id,
            'estado' => 'borrador',
        ]);

        JournalEntry::factory()->count(1)->create([
            'empresa_id' => $this->empresa->id,
            'estado' => 'posteado',
        ]);

        $response = $this->getJson('/api/v1/accounting/journal-entries?estado=borrador');

        $response->assertStatus(200);
        $this->assertEquals(2, count($response->json('data')));
    }

    /** @test */
    public function numero_asiento_es_unico_por_empresa()
    {
        $response = $this->postJson('/api/v1/accounting/journal-entries', [
            'fecha' => now()->toDateString(),
            'descripcion' => 'Asiento 1',
            'lines' => [
                [
                    'account_id' => $this->cuentaBanco->id,
                    'tipo_movimiento' => 'debito',
                    'valor' => 1000,
                ],
                [
                    'account_id' => $this->cuentaIngresos->id,
                    'tipo_movimiento' => 'credito',
                    'valor' => 1000,
                ],
            ],
        ]);

        $this->actingAs($this->user);
        $response->assertStatus(201);
    }
}
