<?php

namespace App\Domains\Payroll\Services;

use App\Models\Afiliado;
use App\Models\Recibo;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use TCPDF;

class CertificadoPilaGenerator
{
    protected TCPDF $pdf;
    protected Afiliado $afiliado;
    protected Carbon $anio;

    public function __construct()
    {
        $this->pdf = new TCPDF('P', 'mm', 'A4');
        $this->pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $this->pdf->SetMargins(20, 20, 20);
        $this->pdf->SetFont('helvetica', '', 10);
    }

    /**
     * Generar certificado de PILA para afiliado
     */
    public function generate(Afiliado $afiliado, int $anio, string $output = 'D'): string
    {
        $this->afiliado = $afiliado;
        $this->anio = Carbon::create($anio, 1, 1);

        // Obtener recibos del año
        $recibos = Recibo::where('afiliado_id', $afiliado->id)
            ->whereYear('fecha_pago', $anio)
            ->with('aportes')
            ->orderBy('fecha_pago')
            ->get();

        $this->pdf->AddPage();

        // Encabezado oficial
        $this->addHeader();

        // Información del empleado
        $this->addEmployeeInfo();

        // Detalles de aportes
        $this->addContributionDetails($recibos);

        // Certificación
        $this->addCertification($recibos);

        // Pie de página
        $this->addFooter();

        return $this->pdf->Output("Certificado_PILA_{$afiliado->documento}_{$anio}.pdf", $output);
    }

    /**
     * Encabezado oficial
     */
    private function addHeader(): void
    {
        $this->pdf->SetFont('helvetica', 'B', 16);
        $this->pdf->Cell(0, 10, 'CERTIFICADO DE APORTES', 0, 1, 'C');

        $this->pdf->SetFont('helvetica', '', 10);
        $this->pdf->Cell(0, 6, 'PLANILLA INTEGRADA DE LIQUIDACIÓN DE APORTES (PILA)', 0, 1, 'C');

        $this->pdf->Ln(5);
        $this->pdf->SetDrawColor(0);
        $this->pdf->Line(20, $this->pdf->GetY(), 190, $this->pdf->GetY());
        $this->pdf->Ln(3);
    }

    /**
     * Información del empleado
     */
    private function addEmployeeInfo(): void
    {
        $this->pdf->SetFont('helvetica', 'B', 11);
        $this->pdf->Cell(0, 7, 'DATOS DEL AFILIADO', 0, 1);

        $this->pdf->SetFont('helvetica', 'B', 10);
        $this->pdf->Cell(50, 6, 'Nombre:', 0);
        $this->pdf->SetFont('helvetica', '', 10);
        $this->pdf->Cell(0, 6, $this->afiliado->nombre, 0, 1);

        $this->pdf->SetFont('helvetica', 'B', 10);
        $this->pdf->Cell(50, 6, 'Documento:', 0);
        $this->pdf->SetFont('helvetica', '', 10);
        $this->pdf->Cell(0, 6, $this->afiliado->documento, 0, 1);

        $this->pdf->SetFont('helvetica', 'B', 10);
        $this->pdf->Cell(50, 6, 'Empresa:', 0);
        $this->pdf->SetFont('helvetica', '', 10);
        $this->pdf->Cell(0, 6, $this->afiliado->empresa->nombre, 0, 1);

        $this->pdf->SetFont('helvetica', 'B', 10);
        $this->pdf->Cell(50, 6, 'Período Certificado:', 0);
        $this->pdf->SetFont('helvetica', '', 10);
        $this->pdf->Cell(0, 6, "Enero - Diciembre {$this->anio->year}", 0, 1);

        $this->pdf->Ln(4);
    }

    /**
     * Detalles de aportes mensuales
     */
    private function addContributionDetails(Collection $recibos): void
    {
        $this->pdf->SetFont('helvetica', 'B', 10);
        $this->pdf->Cell(0, 7, 'RESUMEN DE APORTES MENSUALES', 0, 1);

        $this->pdf->SetFont('helvetica', 'B', 8);
        $this->pdf->SetFillColor(220, 220, 220);

        // Encabezados
        $this->pdf->Cell(20, 5, 'Mes', 1, 0, 'C', true);
        $this->pdf->Cell(30, 5, 'AFP Empl.', 1, 0, 'R', true);
        $this->pdf->Cell(30, 5, 'EPS Empl.', 1, 0, 'R', true);
        $this->pdf->Cell(30, 5, 'ARL Empl.', 1, 0, 'R', true);
        $this->pdf->Cell(30, 5, 'Caja Comp.', 1, 0, 'R', true);
        $this->pdf->Cell(30, 5, 'TOTAL MES', 1, 1, 'R', true);

        $this->pdf->SetFont('helvetica', '', 8);
        $this->pdf->SetFillColor(255);

        $meses = [
            'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
        ];

        $totalesPorTipo = ['AFP' => 0, 'EPS' => 0, 'ARL' => 0, 'CAJA_COMPENSACION' => 0];

        for ($mes = 1; $mes <= 12; $mes++) {
            $recibosMes = $recibos->filter(fn ($r) => $r->fecha_pago->month == $mes);

            $this->pdf->Cell(20, 5, $meses[$mes - 1], 1, 0, 'C');

            if ($recibosMes->isEmpty()) {
                $this->pdf->Cell(30, 5, '-', 1, 0, 'R');
                $this->pdf->Cell(30, 5, '-', 1, 0, 'R');
                $this->pdf->Cell(30, 5, '-', 1, 0, 'R');
                $this->pdf->Cell(30, 5, '-', 1, 0, 'R');
                $this->pdf->Cell(30, 5, '0', 1, 1, 'R');
            } else {
                $aportes = $recibosMes->first()->aportes;

                $afp = $aportes->where('tipo_aporte', 'AFP')->sum('aporte_empleador');
                $eps = $aportes->where('tipo_aporte', 'EPS')->sum('aporte_empleador');
                $arl = $aportes->where('tipo_aporte', 'ARL')->sum('aporte_empleador');
                $caja = $aportes->where('tipo_aporte', 'CAJA_COMPENSACION')->sum('aporte_empleador');

                $totalMes = $afp + $eps + $arl + $caja;

                $this->pdf->Cell(30, 5, number_format($afp, 0, '.', ','), 1, 0, 'R');
                $this->pdf->Cell(30, 5, number_format($eps, 0, '.', ','), 1, 0, 'R');
                $this->pdf->Cell(30, 5, number_format($arl, 0, '.', ','), 1, 0, 'R');
                $this->pdf->Cell(30, 5, number_format($caja, 0, '.', ','), 1, 0, 'R');
                $this->pdf->Cell(30, 5, number_format($totalMes, 0, '.', ','), 1, 1, 'R');

                $totalesPorTipo['AFP'] += $afp;
                $totalesPorTipo['EPS'] += $eps;
                $totalesPorTipo['ARL'] += $arl;
                $totalesPorTipo['CAJA_COMPENSACION'] += $caja;
            }
        }

        // Totales
        $this->pdf->SetFont('helvetica', 'B', 8);
        $this->pdf->SetFillColor(200, 200, 200);

        $totalGeneral = array_sum($totalesPorTipo);

        $this->pdf->Cell(20, 6, 'TOTAL', 1, 0, 'C', true);
        $this->pdf->Cell(30, 6, number_format($totalesPorTipo['AFP'], 0, '.', ','), 1, 0, 'R', true);
        $this->pdf->Cell(30, 6, number_format($totalesPorTipo['EPS'], 0, '.', ','), 1, 0, 'R', true);
        $this->pdf->Cell(30, 6, number_format($totalesPorTipo['ARL'], 0, '.', ','), 1, 0, 'R', true);
        $this->pdf->Cell(30, 6, number_format($totalesPorTipo['CAJA_COMPENSACION'], 0, '.', ','), 1, 0, 'R', true);
        $this->pdf->Cell(30, 6, number_format($totalGeneral, 0, '.', ','), 1, 1, 'R', true);

        $this->pdf->Ln(3);
    }

    /**
     * Certificación oficial
     */
    private function addCertification(Collection $recibos): void
    {
        $this->pdf->SetFont('helvetica', 'B', 10);
        $this->pdf->Cell(0, 7, 'CERTIFICACIÓN', 0, 1);

        $this->pdf->SetFont('helvetica', '', 9);

        $totalMeses = $recibos->groupBy(fn ($r) => $r->fecha_pago->month)->count();

        $textoCertificado = "Se certifica que " . $this->afiliado->nombre . " con documento "
            . $this->afiliado->documento . " fue afiliado a PILA durante "
            . $totalMeses . " meses en el año " . $this->anio->year . " en la empresa "
            . $this->afiliado->empresa->nombre . ".";

        $this->pdf->MultiCell(0, 6, $textoCertificado, 0, 'J');

        $this->pdf->Ln(10);

        // Espacios para firma
        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->Cell(90, 30, '', 0, 0);
        $this->pdf->MultiCell(90, 5, "Responsable\n\n_____________________\nFecha: " . now()->format('d/m/Y'), 0, 'C');
    }

    /**
     * Pie de página
     */
    private function addFooter(): void
    {
        $this->pdf->SetY(-30);
        $this->pdf->SetFont('helvetica', 'I', 7);
        $this->pdf->SetTextColor(150);
        $this->pdf->Cell(0, 4, 'Este certificado es válido como comprobante de aportes a seguridad social', 0, 1, 'C');
        $this->pdf->Cell(0, 4, 'Generado por Dinastía - ' . now()->format('Y-m-d H:i:s'), 0, 1, 'C');
    }
}
