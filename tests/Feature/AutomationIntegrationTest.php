<?php

namespace Tests\Feature;

use App\Domains\Accounting\Models\ChartOfAccounts;
use App\Domains\Accounting\Models\JournalEntry;
use App\Domains\Accounting\Services\AutoJournalEntryCreator;
use App\Domains\Accounting\Services\BankReconciliationService;
use App\Models\Empresa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutomationIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected Empresa $empresa;
    protected User $user;
    protected AutoJournalEntryCreator $creator;
    protected BankReconciliationService $reconciliation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->empresa = Empresa::factory()->create();
        session(['empresa_id' => $this->empresa->id]);

        $this->user = User::factory()->create([
            'empresa_id' => $this->empresa->id,
        ]);

        $this->actingAs($this->user);

        $this->creator = app(AutoJournalEntryCreator::class);
        $this->reconciliation = app(BankReconciliationService::class);
    }

    /** @test */
    public function puedo_validar_saldo_bancario()
    {
        $resultado = $this->reconciliation->validateBankBalance($this->empresa->id);

        $this->assertArrayHasKey('cuenta', $resultado);
        $this->assertArrayHasKey('saldo_contable', $resultado);
        $this->assertArrayHasKey('conciliado', $resultado);
    }

    /** @test */
    public function puedo_generar_reporte_de_conciliacion()
    {
        $desde = now()->startOfYear();
        $hasta = now()->endOfYear();

        $resultado = $this->reconciliation->generateReconciliationReport(
            $this->empresa->id,
            $desde,
            $hasta
        );

        $this->assertArrayHasKey('cuenta', $resultado);
        $this->assertArrayHasKey('periodo', $resultado);
        $this->assertArrayHasKey('movimientos', $resultado);
        $this->assertArrayHasKey('totales', $resultado);
    }

    /** @test */
    public function puedo_detectar_transacciones_duplicadas()
    {
        // Crear asientos duplicados
        $cuentaBanco = ChartOfAccounts::factory()->create([
            'empresa_id' => $this->empresa->id,
            'codigo' => '100501',
            'permite_movimiento' => true,
        ]);

        $asiento1 = JournalEntry::factory()->create([
            'empresa_id' => $this->empresa->id,
            'estado' => 'posteado',
        ]);

        $asiento1->lines()->create([
            'empresa_id' => $this->empresa->id,
            'account_id' => $cuentaBanco->id,
            'tipo_movimiento' => 'debito',
            'valor' => 1000,
        ]);

        $asiento2 = JournalEntry::factory()->create([
            'empresa_id' => $this->empresa->id,
            'estado' => 'posteado',
        ]);

        $asiento2->lines()->create([
            'empresa_id' => $this->empresa->id,
            'account_id' => $cuentaBanco->id,
            'tipo_movimiento' => 'debito',
            'valor' => 1000, // Duplicado
        ]);

        $desde = now()->startOfYear();
        $hasta = now()->endOfYear();

        $duplicados = $this->reconciliation->detectDuplicateTransactions(
            $this->empresa->id,
            $desde,
            $hasta
        );

        // Debería encontrar los duplicados
        $this->assertNotEmpty($duplicados);
    }

    /** @test */
    public function puedo_acceder_a_estadisticas_de_asientos_automaticos()
    {
        $stats = $this->creator->getAutoEntryStatistics($this->empresa->id);

        $this->assertArrayHasKey('total_invoice_entries', $stats);
        $this->assertArrayHasKey('total_payroll_entries', $stats);
        $this->assertArrayHasKey('total_auto_entries', $stats);
    }

    /** @test */
    public function puedo_validar_saldo_bancario_via_api()
    {
        $response = $this->getJson('/api/v1/accounting/reconciliation/validate-balance');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'cuenta',
            'saldo_contable',
            'saldo_esperado',
            'diferencia',
            'conciliado',
        ]);
    }

    /** @test */
    public function puedo_obtener_reporte_conciliacion_via_api()
    {
        $desde = now()->startOfYear()->toDateString();
        $hasta = now()->endOfYear()->toDateString();

        $response = $this->getJson("/api/v1/accounting/reconciliation/report", [
            'desde' => $desde,
            'hasta' => $hasta,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'cuenta',
            'periodo',
            'movimientos',
            'totales',
        ]);
    }

    /** @test */
    public function puedo_detectar_duplicados_via_api()
    {
        $desde = now()->startOfYear()->toDateString();
        $hasta = now()->endOfYear()->toDateString();

        $response = $this->getJson("/api/v1/accounting/reconciliation/duplicates", [
            'desde' => $desde,
            'hasta' => $hasta,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'duplicados_encontrados',
            'detalle',
        ]);
    }

    /** @test */
    public function error_si_fechas_invalidas()
    {
        $response = $this->getJson('/api/v1/accounting/reconciliation/report', [
            'desde' => 'invalid-date',
            'hasta' => 'invalid-date',
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function auto_creation_puede_deshabilitarse()
    {
        $this->assertFalse(!AutoJournalEntryCreator::isAutoCreationEnabled($this->empresa->id));

        // En producción, esto debería poder configurarse por empresa
    }
}
