<?php

namespace Tests\Feature;

use App\Domains\Payroll\Models\NominaElectronica;
use App\Domains\Payroll\Services\ReportesService;
use App\Models\Afiliado;
use App\Models\Empresa;
use App\Models\Recibo;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollReportesTest extends TestCase
{
    use RefreshDatabase;
    protected Empresa $empresa;
    protected Afiliado $afiliado;
    protected Recibo $recibo;
    protected NominaElectronica $nomina;

    protected function setUp(): void
    {
        parent::setUp();

        $this->empresa = Empresa::factory()->create();
        session(['empresa_id' => $this->empresa->id]);

        $this->afiliado = Afiliado::factory()->create([
            'empresa_id' => $this->empresa->id,
        ]);

        $this->recibo = Recibo::factory()->create([
            'empresa_id' => $this->empresa->id,
            'afiliado_id' => $this->afiliado->id,
            'fecha' => now()->startOfMonth(),
        ]);

        $this->nomina = NominaElectronica::factory()->create([
            'empresa_id' => $this->empresa->id,
            'recibo_id' => $this->recibo->id,
            'numero_nomina' => '20260626001',
            'uuid_dian' => 'uuid-test-001',
            'estado' => 'aceptada',
        ]);
    }

    /** @test */
    public function puedo_generar_recibo_pdf()
    {
        $service = new ReportesService();
        $pdf = $service->generarReciboPdf($this->nomina);

        $this->assertNotEmpty($pdf);
        $this->assertStringContainsString('%PDF', $pdf);
    }

    /** @test */
    public function puedo_generar_nota_credito()
    {
        $service = new ReportesService();
        $xml = $service->generarNotaCredito(
            $this->nomina,
            'Ajuste por error de cálculo',
            50000
        );

        $this->assertStringContainsString('CreditDebitNote', $xml);
        $this->assertStringContainsString('91', $xml); // Código de nota crédito
    }

    /** @test */
    public function puedo_generar_nota_debito()
    {
        $service = new ReportesService();
        $xml = $service->generarNotaDebito(
            $this->nomina,
            'Ajuste por cambio de salario',
            100000
        );

        $this->assertStringContainsString('CreditDebitNote', $xml);
        $this->assertStringContainsString('92', $xml); // Código de nota débito
    }

    /** @test */
    public function puedo_generar_reporte_pila()
    {
        // Crear 3 nóminas en el período
        $desde = Carbon::create(2026, 6, 1);
        $hasta = Carbon::create(2026, 6, 30);

        NominaElectronica::factory()->count(2)->create([
            'empresa_id' => $this->empresa->id,
            'fecha_emision' => $desde->addDay(),
        ]);

        $service = new ReportesService();
        $pdf = $service->generarReportePila($this->empresa, $desde, $hasta);

        $this->assertNotEmpty($pdf);
        $this->assertStringContainsString('%PDF', $pdf);
    }

    /** @test */
    public function puedo_generar_certificado_pila()
    {
        $service = new ReportesService();
        $pdf = $service->generarCertificadoPila($this->afiliado, 2026);

        $this->assertNotEmpty($pdf);
        $this->assertStringContainsString('%PDF', $pdf);
    }

    /** @test */
    public function puedo_generar_batch_certificados_pila()
    {
        // Crear 2 afiliados más
        Afiliado::factory()->count(2)->create([
            'empresa_id' => $this->empresa->id,
        ]);

        $service = new ReportesService();
        $batch = $service->generarBatchCertificadosPila($this->empresa, 2026, '/tmp');

        $this->assertCount(3, $batch);
        $this->assertEquals('Certificado_PILA_' . $this->afiliado->documento . '_2026.pdf', $batch[0]['nombre_archivo']);
    }

    /** @test */
    public function puedo_generar_reporte_347()
    {
        // Crear recibos en múltiples meses
        Recibo::factory()->count(11)->create([
            'empresa_id' => $this->empresa->id,
            'afiliado_id' => $this->afiliado->id,
            'fecha_pago' => fn () => now()->setMonth(rand(1, 12)),
        ]);

        $service = new ReportesService();
        $reporte = $service->generarReporte347($this->empresa, 2026);

        $this->assertArrayHasKey('empresa', $reporte);
        $this->assertArrayHasKey('afiliados', $reporte);
        $this->assertArrayHasKey('resumen_general', $reporte);

        $this->assertGreaterThan(0, $reporte['resumen_general']['total_afiliados']);
    }

    /** @test */
    public function puedo_validar_nominas_para_reporte()
    {
        $desde = Carbon::create(2026, 6, 1);
        $hasta = Carbon::create(2026, 6, 30);

        $service = new ReportesService();
        $validacion = $service->validarNominasParaReporte($this->empresa, $desde, $hasta);

        $this->assertArrayHasKey('total_nominas', $validacion);
        $this->assertArrayHasKey('aceptadas', $validacion);
        $this->assertArrayHasKey('lista_para_reporte', $validacion);

        $this->assertGreaterThan(0, $validacion['total_nominas']);
    }

    /** @test */
    public function validacion_rechaza_nominas_con_pendientes()
    {
        // Crear nómina en estado borrador
        NominaElectronica::factory()->create([
            'empresa_id' => $this->empresa->id,
            'estado' => 'borrador',
        ]);

        $desde = Carbon::create(2026, 6, 1);
        $hasta = Carbon::create(2026, 6, 30);

        $service = new ReportesService();
        $validacion = $service->validarNominasParaReporte($this->empresa, $desde, $hasta);

        $this->assertFalse($validacion['lista_para_reporte']);
    }
}
