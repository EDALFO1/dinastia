<?php

namespace Tests\Feature;

use App\Domains\Accounting\Models\ChartOfAccounts;
use App\Domains\Accounting\Models\JournalEntry;
use App\Domains\Accounting\Services\AuditTrailService;
use App\Domains\Accounting\Services\BudgetService;
use App\Domains\Accounting\Services\HorizontalAnalysisService;
use App\Models\Empresa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvancedReportTest extends TestCase
{
    use RefreshDatabase;

    protected Empresa $empresa;
    protected User $user;
    protected ChartOfAccounts $cuentaBanco;

    protected function setUp(): void
    {
        parent::setUp();

        $this->empresa = Empresa::factory()->create();
        session(['empresa_id' => $this->empresa->id]);

        $this->user = User::factory()->create([
            'empresa_id' => $this->empresa->id,
        ]);

        $this->actingAs($this->user);

        $this->cuentaBanco = ChartOfAccounts::factory()->create([
            'empresa_id' => $this->empresa->id,
            'codigo' => '100501',
            'nombre' => 'Banco',
            'permite_movimiento' => true,
            'tipo_cuenta' => 'activo',
        ]);
    }

    /** @test */
    public function puedo_generar_analisis_horizontal()
    {
        $horizontal = app(HorizontalAnalysisService::class);

        $resultado = $horizontal->analyzeHorizontal(
            $this->empresa->id,
            now()->subYear()->startOfYear(),
            now()->subYear()->endOfYear(),
            now()->startOfYear(),
            now()->endOfYear()
        );

        $this->assertArrayHasKey('tipo_analisis', $resultado);
        $this->assertArrayHasKey('periodos', $resultado);
        $this->assertArrayHasKey('resumen', $resultado);
        $this->assertEquals('horizontal', $resultado['tipo_analisis']);
    }

    /** @test */
    public function puedo_analizar_tendencias()
    {
        $horizontal = app(HorizontalAnalysisService::class);

        $resultado = $horizontal->analyzeTrends($this->empresa->id, now(), 12);

        $this->assertArrayHasKey('tipo_analisis', $resultado);
        $this->assertArrayHasKey('cuentas', $resultado);
        $this->assertEquals('tendencias', $resultado['tipo_analisis']);
    }

    /** @test */
    public function puedo_comparar_presupuesto_vs_real()
    {
        $budget = app(BudgetService::class);

        $presupuestos = [
            '100501' => 5000000, // 5 millones presupuestados
        ];

        $resultado = $budget->compareBudgetVsActual(
            $this->empresa->id,
            now()->startOfYear(),
            now()->endOfYear(),
            $presupuestos
        );

        $this->assertArrayHasKey('periodo', $resultado);
        $this->assertArrayHasKey('resumen', $resultado);
        $this->assertArrayHasKey('detalles', $resultado);
    }

    /** @test */
    public function presupuesto_calcula_variacion()
    {
        $budget = app(BudgetService::class);

        $presupuestos = [
            '100501' => 1000000,
        ];

        $resultado = $budget->compareBudgetVsActual(
            $this->empresa->id,
            now()->startOfYear(),
            now()->endOfYear(),
            $presupuestos
        );

        $this->assertNotEmpty($resultado['detalles']);
        $this->assertArrayHasKey('variacion', $resultado['detalles'][0] ?? []);
    }

    /** @test */
    public function puedo_obtener_rastreo_de_asiento()
    {
        $asiento = JournalEntry::factory()->create([
            'empresa_id' => $this->empresa->id,
        ]);

        $audit = app(AuditTrailService::class);
        $resultado = $audit->getEntryAuditTrail($asiento->id);

        $this->assertArrayHasKey('asiento', $resultado);
        $this->assertArrayHasKey('eventos', $resultado);
        $this->assertArrayHasKey('lineas', $resultado);
    }

    /** @test */
    public function puedo_obtener_auditoria_por_usuario()
    {
        JournalEntry::factory()->count(3)->create([
            'empresa_id' => $this->empresa->id,
            'usuario_creacion_id' => $this->user->id,
        ]);

        $audit = app(AuditTrailService::class);
        $resultado = $audit->getAuditByUser(
            $this->empresa->id,
            $this->user->id,
            now()->startOfYear(),
            now()->endOfYear()
        );

        $this->assertArrayHasKey('resumen', $resultado);
        $this->assertArrayHasKey('asientos', $resultado);
    }

    /** @test */
    public function puedo_obtener_auditoria_por_periodo()
    {
        JournalEntry::factory()->count(5)->create([
            'empresa_id' => $this->empresa->id,
        ]);

        $audit = app(AuditTrailService::class);
        $resultado = $audit->getAuditByPeriod(
            $this->empresa->id,
            now()->startOfYear(),
            now()->endOfYear()
        );

        $this->assertArrayHasKey('resumen', $resultado);
        $this->assertGreaterThan(0, $resultado['resumen']['total_asientos']);
    }

    /** @test */
    public function puedo_detectar_actividades_sospechosas()
    {
        $audit = app(AuditTrailService::class);
        $resultado = $audit->detectSuspiciousActivity(
            $this->empresa->id,
            now()->startOfYear(),
            now()->endOfYear()
        );

        $this->assertArrayHasKey('actividades_sospechosas', $resultado);
        $this->assertArrayHasKey('total_alertas', $resultado);
    }

    /** @test */
    public function puedo_acceder_analisis_via_api()
    {
        $response = $this->postJson('/api/v1/accounting/advanced/budget-comparison', [
            'desde' => now()->startOfYear()->toDateString(),
            'hasta' => now()->endOfYear()->toDateString(),
            'presupuestos' => [
                '100501' => 5000000,
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'periodo',
            'resumen',
            'detalles',
        ]);
    }

    /** @test */
    public function puedo_acceder_auditoria_via_api()
    {
        $asiento = JournalEntry::factory()->create([
            'empresa_id' => $this->empresa->id,
        ]);

        $response = $this->getJson("/api/v1/accounting/advanced/audit-trail/{$asiento->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'asiento',
            'eventos',
            'lineas',
        ]);
    }

    /** @test */
    public function error_si_asiento_no_existe()
    {
        $response = $this->getJson('/api/v1/accounting/advanced/audit-trail/99999');

        $response->assertStatus(404);
    }
}
