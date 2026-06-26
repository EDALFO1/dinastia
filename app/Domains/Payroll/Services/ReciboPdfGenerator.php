<?php

namespace App\Domains\Payroll\Services;

use App\Domains\Payroll\Models\NominaElectronica;
use App\Models\Recibo;
use TCPDF;

class ReciboPdfGenerator
{
    protected TCPDF $pdf;
    protected NominaElectronica $nomina;
    protected Recibo $recibo;

    public function __construct()
    {
        $this->pdf = new TCPDF('P', 'mm', 'A4');
        $this->pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $this->pdf->SetMargins(15, 15, 15);
        $this->pdf->SetFont('helvetica', '', 9);
    }

    /**
     * Generar PDF de recibo de nómina
     */
    public function generate(NominaElectronica $nomina, string $output = 'D'): string
    {
        $this->nomina = $nomina;
        $this->recibo = $nomina->recibo;

        $this->pdf->AddPage();

        // Encabezado
        $this->addHeader();

        // Información general
        $this->addPayrollInfo();

        // Información del empleado
        $this->addEmployeeInfo();

        // Período
        $this->addPeriodInfo();

        // Detalles de pago
        $this->addPaymentDetails();

        // Totales
        $this->addTotals();

        // Notas
        $this->addFooter();

        return $this->pdf->Output("recibo_{$nomina->numero_nomina}.pdf", $output);
    }

    /**
     * Encabezado del recibo
     */
    private function addHeader(): void
    {
        $empresa = $this->nomina->empresa;

        $this->pdf->SetFont('helvetica', 'B', 14);
        $this->pdf->Cell(0, 10, $empresa->nombre, 0, 1, 'C');

        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->Cell(0, 5, "NIT: {$empresa->nit}", 0, 1, 'C');
        $this->pdf->Cell(0, 5, $empresa->direccion, 0, 1, 'C');

        $this->pdf->SetFont('helvetica', 'B', 12);
        $this->pdf->Ln(5);
        $this->pdf->Cell(0, 8, 'RECIBO DE NÓMINA', 0, 1, 'C');

        $this->pdf->SetFont('helvetica', '', 8);
        $this->pdf->Cell(0, 4, "Nómina: {$this->nomina->numero_nomina} | UUID DIAN: {$this->nomina->uuid_dian}", 0, 1, 'C');

        $this->pdf->Ln(3);
        $this->pdf->SetDrawColor(200);
        $this->pdf->Line(15, $this->pdf->GetY(), 195, $this->pdf->GetY());
    }

    /**
     * Información de la nómina
     */
    private function addPayrollInfo(): void
    {
        $this->pdf->SetFont('helvetica', 'B', 9);
        $this->pdf->Cell(47, 6, 'Fecha Emisión:', 0);
        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->Cell(48, 6, $this->nomina->fecha_emision->format('d/m/Y'), 0);

        $this->pdf->SetFont('helvetica', 'B', 9);
        $this->pdf->Cell(47, 6, 'Estado:', 0);
        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->Cell(0, 6, strtoupper($this->nomina->estado), 0, 1);

        $this->pdf->SetFont('helvetica', 'B', 9);
        $this->pdf->Cell(47, 6, 'Período:', 0);
        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->Cell(95, 6, "{$this->nomina->periodo_pago_inicio->format('d/m/Y')} - {$this->nomina->periodo_pago_final->format('d/m/Y')}", 0, 1);

        $this->pdf->Ln(3);
    }

    /**
     * Información del empleado
     */
    private function addEmployeeInfo(): void
    {
        $afiliado = $this->recibo->afiliado;

        $this->pdf->SetFont('helvetica', 'B', 10);
        $this->pdf->Cell(0, 6, 'DATOS DEL EMPLEADO', 0, 1, '', false);

        $this->pdf->SetFont('helvetica', 'B', 9);
        $this->pdf->Cell(47, 5, 'Nombre:', 0);
        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->Cell(0, 5, $afiliado->nombre, 0, 1);

        $this->pdf->SetFont('helvetica', 'B', 9);
        $this->pdf->Cell(47, 5, 'Documento:', 0);
        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->Cell(48, 5, $afiliado->documento, 0);

        $this->pdf->SetFont('helvetica', 'B', 9);
        $this->pdf->Cell(47, 5, 'Cargo:', 0);
        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->Cell(0, 5, $afiliado->cargo, 0, 1);

        $this->pdf->Ln(2);
    }

    /**
     * Información del período
     */
    private function addPeriodInfo(): void
    {
        $this->pdf->SetFont('helvetica', 'B', 10);
        $this->pdf->Cell(0, 6, 'PERÍODO DE PAGO', 0, 1, '', false);

        $diasLaborados = $this->recibo->dias_laborados ?? 30;

        $this->pdf->SetFont('helvetica', 'B', 9);
        $this->pdf->Cell(47, 5, 'Días Laborados:', 0);
        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->Cell(48, 5, $diasLaborados, 0);

        $this->pdf->SetFont('helvetica', 'B', 9);
        $this->pdf->Cell(47, 5, 'Salario Base:', 0);
        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->Cell(0, 5, number_format($this->nomina->salario_ordinario, 0, '.', ','), 0, 1);

        $this->pdf->Ln(2);
    }

    /**
     * Detalles de pago
     */
    private function addPaymentDetails(): void
    {
        $this->pdf->SetFont('helvetica', 'B', 10);
        $this->pdf->Cell(0, 6, 'DETALLES DE PAGO', 0, 1, '', false);

        // Tabla de devengado
        $this->pdf->SetFont('helvetica', 'B', 8);
        $this->pdf->SetFillColor(220, 220, 220);
        $this->pdf->Cell(130, 5, 'CONCEPTO', 1, 0, 'L', true);
        $this->pdf->Cell(50, 5, 'VALOR', 1, 1, 'R', true);

        $this->pdf->SetFont('helvetica', '', 8);
        $this->pdf->SetFillColor(255);

        // Ingresos desde relaciones
        if ($this->recibo->detalles) {
            foreach ($this->recibo->detalles as $detalle) {
                $this->pdf->Cell(130, 5, $detalle->concepto, 1);
                $this->pdf->Cell(50, 5, number_format($detalle->valor, 0, '.', ','), 1, 1, 'R');
            }
        }

        // Total devengado
        $this->pdf->SetFont('helvetica', 'B', 9);
        $this->pdf->SetFillColor(200, 200, 200);
        $this->pdf->Cell(130, 6, 'TOTAL DEVENGADO', 1, 0, 'R', true);
        $this->pdf->Cell(50, 6, number_format($this->nomina->total_devengado, 0, '.', ','), 1, 1, 'R', true);

        $this->pdf->Ln(3);

        // Tabla de descuentos
        $this->pdf->SetFont('helvetica', 'B', 8);
        $this->pdf->SetFillColor(220, 220, 220);
        $this->pdf->Cell(130, 5, 'DESCUENTOS', 1, 0, 'L', true);
        $this->pdf->Cell(50, 5, 'VALOR', 1, 1, 'R', true);

        $this->pdf->SetFont('helvetica', '', 8);
        $this->pdf->SetFillColor(255);

        // Aportes desde relaciones
        if ($this->recibo->aportes) {
            foreach ($this->recibo->aportes as $aporte) {
                $this->pdf->Cell(130, 5, "Aporte {$aporte->tipo_aporte}", 1);
                $this->pdf->Cell(50, 5, number_format($aporte->aporte_empleado, 0, '.', ','), 1, 1, 'R');
            }
        }

        // Retenciones
        if ($this->recibo->retenciones) {
            foreach ($this->recibo->retenciones as $retencion) {
                $this->pdf->Cell(130, 5, "Retención {$retencion->tipo_retencion}", 1);
                $this->pdf->Cell(50, 5, number_format($retencion->valor_retencion, 0, '.', ','), 1, 1, 'R');
            }
        }

        // Total descuentos
        $this->pdf->SetFont('helvetica', 'B', 9);
        $this->pdf->SetFillColor(200, 200, 200);
        $this->pdf->Cell(130, 6, 'TOTAL DESCUENTOS', 1, 0, 'R', true);
        $this->pdf->Cell(50, 6, number_format($this->nomina->total_descuentos, 0, '.', ','), 1, 1, 'R', true);

        $this->pdf->Ln(2);
    }

    /**
     * Totales finales
     */
    private function addTotals(): void
    {
        $this->pdf->SetFont('helvetica', 'B', 11);
        $this->pdf->SetFillColor(50, 100, 150);
        $this->pdf->SetTextColor(255);
        $this->pdf->Cell(130, 8, 'NETO A PAGAR', 1, 0, 'R', true);
        $this->pdf->Cell(50, 8, number_format($this->nomina->neto_pagar, 0, '.', ','), 1, 1, 'R', true);

        $this->pdf->SetTextColor(0);
        $this->pdf->Ln(3);
    }

    /**
     * Pie de página
     */
    private function addFooter(): void
    {
        $this->pdf->SetFont('helvetica', 'I', 7);
        $this->pdf->SetTextColor(100);
        $this->pdf->Cell(0, 4, 'Este documento es un recibo informativo de nómina. Para validación oficial, consulte DIAN.', 0, 1, 'C');
        $this->pdf->Cell(0, 4, "Generado: " . now()->format('Y-m-d H:i:s'), 0, 1, 'C');

        if ($this->nomina->uuid_dian) {
            $this->pdf->Cell(0, 4, "UUID DIAN: {$this->nomina->uuid_dian}", 0, 1, 'C');
        }
    }
}
