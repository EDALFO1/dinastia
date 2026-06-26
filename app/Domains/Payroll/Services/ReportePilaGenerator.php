<?php

namespace App\Domains\Payroll\Services;

use App\Models\Empresa;
use App\Models\Recibo;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use TCPDF;

class ReportePilaGenerator
{
    protected TCPDF $pdf;
    protected Empresa $empresa;
    protected Carbon $periodDesde;
    protected Carbon $periodHasta;

    public function __construct()
    {
        $this->pdf = new TCPDF('L', 'mm', 'A4');
        $this->pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $this->pdf->SetMargins(10, 10, 10);
        $this->pdf->SetFont('helvetica', '', 8);
    }

    /**
     * Generar reporte PILA consolidado
     */
    public function generate(Empresa $empresa, Carbon $desde, Carbon $hasta, string $output = 'D'): string
    {
        $this->empresa = $empresa;
        $this->periodDesde = $desde;
        $this->periodHasta = $hasta;

        // Obtener recibos del período
        $recibos = Recibo::where('empresa_id', $empresa->id)
            ->whereBetween('fecha_pago', [$desde, $hasta])
            ->with(['afiliado', 'aportes', 'retenciones'])
            ->get();

        $this->pdf->AddPage();

        // Encabezado
        $this->addHeader();

        // Información del período
        $this->addPeriodInfo();

        // Detalles de PILA
        $this->addPilaDetails($recibos);

        // Resumen
        $this->addSummary($recibos);

        return $this->pdf->Output("PILA_{$desde->format('Y-m')}.pdf", $output);
    }

    /**
     * Encabezado
     */
    private function addHeader(): void
    {
        $this->pdf->SetFont('helvetica', 'B', 12);
        $this->pdf->Cell(0, 8, 'PLANILLA INTEGRADA DE LIQUIDACIÓN DE APORTES (PILA)', 0, 1, 'C');

        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->Cell(0, 6, $this->empresa->nombre, 0, 1, 'C');
        $this->pdf->Cell(0, 6, "NIT: {$this->empresa->nit}", 0, 1, 'C');

        $this->pdf->Ln(3);
        $this->pdf->SetDrawColor(0);
        $this->pdf->Line(10, $this->pdf->GetY(), 277, $this->pdf->GetY());
    }

    /**
     * Información del período
     */
    private function addPeriodInfo(): void
    {
        $this->pdf->SetFont('helvetica', 'B', 9);
        $this->pdf->Cell(60, 5, "Período: {$this->periodDesde->format('d/m/Y')} - {$this->periodHasta->format('d/m/Y')}", 0);
        $this->pdf->Cell(0, 5, "Fecha Generación: " . now()->format('d/m/Y H:i'), 0, 1);
        $this->pdf->Ln(2);
    }

    /**
     * Detalles de PILA (por afiliado)
     */
    private function addPilaDetails(Collection $recibos): void
    {
        $this->pdf->SetFont('helvetica', 'B', 8);
        $this->pdf->SetFillColor(200, 200, 200);

        // Encabezados
        $this->pdf->Cell(12, 5, 'No.', 1, 0, 'C', true);
        $this->pdf->Cell(15, 5, 'Cédula', 1, 0, 'C', true);
        $this->pdf->Cell(40, 5, 'Nombre Afiliado', 1, 0, 'L', true);
        $this->pdf->Cell(20, 5, 'Días', 1, 0, 'C', true);
        $this->pdf->Cell(30, 5, 'Salario Base', 1, 0, 'R', true);
        $this->pdf->Cell(30, 5, 'AFP', 1, 0, 'R', true);
        $this->pdf->Cell(30, 5, 'EPS', 1, 0, 'R', true);
        $this->pdf->Cell(30, 5, 'ARL', 1, 0, 'R', true);
        $this->pdf->Cell(30, 5, 'Caja Comp.', 1, 1, 'R', true);

        $this->pdf->SetFont('helvetica', '', 8);
        $this->pdf->SetFillColor(255);

        $numero = 1;

        foreach ($recibos as $recibo) {
            $afiliado = $recibo->afiliado;

            // Calcular aportes por tipo
            $aportes = $recibo->aportes
                ->groupBy('tipo_aporte')
                ->map(fn ($group) => $group->sum('aporte_empleador'));

            $this->pdf->Cell(12, 5, $numero++, 1, 0, 'C');
            $this->pdf->Cell(15, 5, substr($afiliado->documento, -10), 1, 0, 'C');
            $this->pdf->Cell(40, 5, substr($afiliado->nombre, 0, 30), 1, 0, 'L');
            $this->pdf->Cell(20, 5, $recibo->dias_laborados ?? 30, 1, 0, 'C');
            $this->pdf->Cell(30, 5, number_format($recibo->salario_base ?? 0, 0, '.', ','), 1, 0, 'R');

            // AFP
            $afp = $aportes->get('AFP', 0);
            $this->pdf->Cell(30, 5, number_format($afp, 0, '.', ','), 1, 0, 'R');

            // EPS
            $eps = $aportes->get('EPS', 0);
            $this->pdf->Cell(30, 5, number_format($eps, 0, '.', ','), 1, 0, 'R');

            // ARL
            $arl = $aportes->get('ARL', 0);
            $this->pdf->Cell(30, 5, number_format($arl, 0, '.', ','), 1, 0, 'R');

            // Caja de Compensación
            $caja = $aportes->get('CAJA_COMPENSACION', 0);
            $this->pdf->Cell(30, 5, number_format($caja, 0, '.', ','), 1, 1, 'R');
        }
    }

    /**
     * Resumen de PILA
     */
    private function addSummary(Collection $recibos): void
    {
        $this->pdf->Ln(5);
        $this->pdf->SetFont('helvetica', 'B', 9);
        $this->pdf->Cell(0, 6, 'RESUMEN DE APORTES', 0, 1);

        $this->pdf->SetFont('helvetica', '', 8);

        // Calcular totales
        $totalAfialiados = $recibos->count();
        $totalSalarios = $recibos->sum('salario_base');

        $totalAportes = $recibos->lazy()
            ->flatMap(fn ($r) => $r->aportes)
            ->groupBy('tipo_aporte')
            ->map(fn ($group) => $group->sum('aporte_empleador'));

        $this->pdf->Cell(60, 5, "Total Afiliados: {$totalAfialiados}", 0, 1);
        $this->pdf->Cell(60, 5, "Total Salarios: " . number_format($totalSalarios, 0, '.', ','), 0, 1);

        $this->pdf->Ln(2);
        $this->pdf->SetFont('helvetica', 'B', 8);
        $this->pdf->Cell(60, 5, 'TIPO DE APORTE', 1, 0, 'L');
        $this->pdf->Cell(60, 5, 'TOTAL EMPLEADOR', 1, 1, 'R');

        $this->pdf->SetFont('helvetica', '', 8);
        $totalGeneral = 0;

        foreach ($totalAportes as $tipo => $valor) {
            $this->pdf->Cell(60, 5, $tipo, 1, 0, 'L');
            $this->pdf->Cell(60, 5, number_format($valor, 0, '.', ','), 1, 1, 'R');
            $totalGeneral += $valor;
        }

        $this->pdf->SetFont('helvetica', 'B', 9);
        $this->pdf->SetFillColor(200, 200, 200);
        $this->pdf->Cell(60, 6, 'TOTAL APORTES', 1, 0, 'L', true);
        $this->pdf->Cell(60, 6, number_format($totalGeneral, 0, '.', ','), 1, 1, 'R', true);
    }
}
