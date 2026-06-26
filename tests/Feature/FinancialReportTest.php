<?php

namespace Tests\Feature;

use App\Domains\Accounting\Models\ChartOfAccounts;
use App\Domains\Accounting\Models\JournalEntry;
use App\Domains\Accounting\Models\JournalLine;
use App\Models\Empresa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialReportTest extends TestCase
{
    use RefreshDatabase;

    protected Empresa $empresa;
    protected User $user;
    protected ChartOfAccounts $cuentaBanco;
    protected ChartOfAccounts $cuentaIngresos;
    protected ChartOfAccounts $cuentaGastos;

    protected function setUp(): void
    {
        parent::setUp();

        $this->empresa = Empresa::factory()->create();
        session(['empresa_id' => $this->empresa->id]);

        $this->user = User::factory()->create([
            'empresa_id' => $this->empresa->id,
        ]);

        // Crear cuentas
        $this->cuentaBanco = ChartOfAccounts::factory()->create([
            'empresa_id' => $this->empresa->id,
            'codigo' => '100501',
            'nombre' => 'Banco Occidente',
            'tipo_cuenta' => 'activo',
            'permite_movimiento' => true,
        ]);

        $this->cuentaIngresos = ChartOfAccounts::factory()->create([
            'empresa_id' => $this->empresa->id,
            'codigo' => '410101',
            'nombre' => 'Ventas Nacionales',
            'tipo_cuenta' => 'ingresos',
            'permite_movimiento' => true,
        ]);

        $this->cuentaGastos = ChartOfAccounts::factory()->create([
            'empresa_id' => $this->empresa->id,
            'codigo' => '510101',
            'nombre' => 'Salarios',
            'tipo_cuenta' => 'gastos',
            'permite_movimiento' => true,
        ]);
    }

    /** @test */
    public function puedo_obtener_libro_mayor_de_cuenta()
    {
        $this->actingAs($this->user);

        // Crear asiento
        $asiento = JournalEntry::factory()->create([
            'empresa_id' => $this->empresa->id,
            'estado' => 'posteado',
            'fecha' => now(),
        ]);

        $asiento->lines()->create([
            'empresa_id' => $this->empresa->id,
            'account_id' => $this->cuentaBanco->id,
            'tipo_movimiento' => 'debito',
            'valor' => 1000,
        ]);

        $response = $this->getJson('/api/v1/accounting/reports/ledger', [
            'codigo' => '100501',
            'desde' => now()->startOfYear()->toDateString(),
            'hasta' => now()->endOfYear()->toDateString(),
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'cuenta',
            'periodo',
            'saldo_inicial',
            'saldo_final',
            'detalles',
            'totales',
        ]);
    }

    /** @test */
    public function puedo_obtener_balance_general()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/v1/accounting/reports/balance-sheet', [
            'fecha' => now()->toDateString(),
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'fecha',
            'activos',
            'pasivos',
            'patrimonio',
            'ecuacion_contable',
        ]);
    }

    /** @test */
    public function balance_general_debe_balancear()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/v1/accounting/reports/balance-sheet', [
            'fecha' => now()->toDateString(),
        ]);

        $data = $response->json();
        $this->assertTrue($data['ecuacion_contable']['balanceado']);
    }

    /** @test */
    public function puedo_obtener_analisis_vertical()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/v1/accounting/reports/balance-sheet-vertical', [
            'fecha' => now()->toDateString(),
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'fecha',
            'analisis_vertical',
        ]);
    }

    /** @test */
    public function puedo_obtener_estado_de_resultados()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/v1/accounting/reports/income-statement', [
            'desde' => now()->startOfYear()->toDateString(),
            'hasta' => now()->endOfYear()->toDateString(),
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'periodo',
            'ingresos',
            'costo_venta',
            'utilidad_bruta',
            'gastos',
            'utilidad_operacional',
            'indicadores',
        ]);
    }

    /** @test */
    public function puedo_obtener_comparativa_de_periodos()
    {
        $this->actingAs($this->user);

        $actual = now();
        $previo = now()->subMonth();

        $response = $this->getJson('/api/v1/accounting/reports/income-comparison', [
            'actual' => $actual->toDateString(),
            'previo' => $previo->toDateString(),
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'actual',
            'previo',
            'variacion',
        ]);
    }

    /** @test */
    public function puedo_obtener_ratios_financieros()
    {
        $this->actingAs($this->user);

        $fecha = now();
        $desde = $fecha->copy()->startOfYear();
        $hasta = $fecha->copy()->endOfYear();

        $response = $this->getJson('/api/v1/accounting/reports/financial-ratios', [
            'fecha' => $fecha->toDateString(),
            'desde' => $desde->toDateString(),
            'hasta' => $hasta->toDateString(),
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'fecha_calculo',
            'ratios_liquidez',
            'ratios_rentabilidad',
            'ratios_solvencia',
            'ratios_eficiencia',
        ]);
    }

    /** @test */
    public function puedo_obtener_libro_mayor_consolidado()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/v1/accounting/reports/ledger-consolidated', [
            'desde' => now()->startOfYear()->toDateString(),
            'hasta' => now()->endOfYear()->toDateString(),
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'periodo',
            'cuentas',
            'totales',
        ]);
    }

    /** @test */
    public function error_si_cuenta_no_existe()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/v1/accounting/reports/ledger', [
            'codigo' => '999999',
            'desde' => now()->startOfYear()->toDateString(),
            'hasta' => now()->endOfYear()->toDateString(),
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function error_si_fecha_invalida()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/v1/accounting/reports/balance-sheet', [
            'fecha' => 'invalid-date',
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function ratios_incluyen_interpretaciones()
    {
        $this->actingAs($this->user);

        $fecha = now();
        $desde = $fecha->copy()->startOfYear();
        $hasta = $fecha->copy()->endOfYear();

        $response = $this->getJson('/api/v1/accounting/reports/financial-ratios', [
            'fecha' => $fecha->toDateString(),
            'desde' => $desde->toDateString(),
            'hasta' => $hasta->toDateString(),
        ]);

        $data = $response->json();
        $this->assertArrayHasKey('interpretacion_razon_corriente', $data['ratios_liquidez']);
    }
}
