<?php

namespace App\Domains\Invoicing\Services;

use App\Domains\Invoicing\Models\Invoice;
use App\Models\Empresa;
use Carbon\Carbon;
use Maatwebsite\Excel\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SalesBookGenerator
{
    protected Empresa $empresa;
    protected Carbon $fechaInicio;
    protected Carbon $fechaFin;
    protected ?string $estado = null;

    public function __construct(
        Empresa $empresa,
        Carbon $fechaInicio,
        Carbon $fechaFin,
        ?string $estado = null
    ) {
        $this->empresa = $empresa;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        $this->estado = $estado;
    }

    /**
     * Generate Excel file
     */
    public function generate(): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $this->addHeader($sheet);
        $this->addData($sheet);
        $this->addTotals($sheet);
        $this->formatSheet($sheet);

        $writer = new Xlsx($spreadsheet);
        $filename = storage_path('temp/libro_ventas_' . now()->format('YmdHis') . '.xlsx');

        $writer->save($filename);

        return $filename;
    }

    /**
     * Download Excel file
     */
    public function download()
    {
        $filename = $this->generate();

        return response()->download(
            $filename,
            'libro_ventas_' . $this->empresa->nombre . '_' . now()->format('Y-m-d') . '.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }

    /**
     * Add header information
     */
    protected function addHeader($sheet): void
    {
        $sheet->setCellValue('A1', 'LIBRO DE VENTAS');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A2', 'Empresa: ' . $this->empresa->nombre);
        $sheet->mergeCells('A2:H2');

        $sheet->setCellValue('A3', 'NIT: ' . $this->empresa->nit);
        $sheet->mergeCells('A3:H3');

        $sheet->setCellValue('A4', 'Período: ' . $this->fechaInicio->format('d/m/Y') . ' - ' . $this->fechaFin->format('d/m/Y'));
        $sheet->mergeCells('A4:H4');

        $sheet->setCellValue('A5', 'Fecha de generación: ' . now()->format('d/m/Y H:i:s'));
        $sheet->mergeCells('A5:H5');

        // Column headers
        $headers = [
            'No. Factura',
            'Fecha Emisión',
            'Cliente NIT',
            'Cliente Nombre',
            'Subtotal',
            'Descuento',
            'Base IVA',
            'Valor IVA',
            'Total Factura',
            'Estado',
            'UUID DIAN',
        ];

        $col = 'A';
        foreach ($headers as $index => $header) {
            $sheet->setCellValue($col . '7', $header);
            $col++;
        }

        $this->formatHeaderRow($sheet);
    }

    /**
     * Add invoice data
     */
    protected function addData($sheet): void
    {
        $query = Invoice::where('empresa_id', $this->empresa->id)
            ->whereBetween('fecha_emision', [$this->fechaInicio, $this->fechaFin])
            ->with(['taxes']);

        if ($this->estado) {
            $query->where('estado', $this->estado);
        }

        $invoices = $query->orderBy('fecha_emision')->get();

        $row = 8;
        foreach ($invoices as $invoice) {
            $ivaTotal = $invoice->taxes
                ->where('tipo_impuesto.value', 'IVA')
                ->sum('valor');

            $sheet->setCellValue('A' . $row, $invoice->numero);
            $sheet->setCellValue('B' . $row, $invoice->fecha_emision->format('d/m/Y'));
            $sheet->setCellValue('C' . $row, $invoice->cliente_nit);
            $sheet->setCellValue('D' . $row, $invoice->cliente_nombre);
            $sheet->setCellValue('E' . $row, $invoice->subtotal);
            $sheet->setCellValue('F' . $row, $invoice->descuento);
            $sheet->setCellValue('G' . $row, $invoice->subtotal - $invoice->descuento);
            $sheet->setCellValue('H' . $row, $ivaTotal);
            $sheet->setCellValue('I' . $row, $invoice->total);
            $sheet->setCellValue('J' . $row, ucfirst($invoice->estado));
            $sheet->setCellValue('K' . $row, $invoice->uuid_dian ?? '');

            // Format as numbers
            $sheet->getStyle('E' . $row . ':I' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

            $row++;
        }
    }

    /**
     * Add totals row
     */
    protected function addTotals($sheet): void
    {
        $lastRow = $sheet->getHighestRow();
        $totalsRow = $lastRow + 2;

        $sheet->setCellValue('A' . $totalsRow, 'TOTAL');
        $sheet->getStyle('A' . $totalsRow)->getFont()->setBold(true);

        // Sum formulas for each column
        $columns = ['E', 'F', 'G', 'H', 'I'];
        foreach ($columns as $col) {
            $formula = '=SUM(' . $col . '8:' . $col . $lastRow . ')';
            $sheet->setCellValue($col . $totalsRow, $formula);
            $sheet->getStyle($col . $totalsRow)->getFont()->setBold(true);
            $sheet->getStyle($col . $totalsRow)->getNumberFormat()->setFormatCode('#,##0.00');
        }

        // Highlight totals row
        $sheet->getStyle('A' . $totalsRow . ':I' . $totalsRow)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('FFCCCCCC');
    }

    /**
     * Format header row
     */
    protected function formatHeaderRow($sheet): void
    {
        $sheet->getStyle('A7:K7')->getFont()->setBold(true)->setColor('FFFFFFFF');
        $sheet->getStyle('A7:K7')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('FF2C3E50');

        $sheet->getStyle('A7:K7')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);

        $sheet->setRowHeight(7, 25);
    }

    /**
     * Format sheet (columns, borders, etc)
     */
    protected function formatSheet($sheet): void
    {
        $sheet->getColumnDimension('A')->setWidth(12);
        $sheet->getColumnDimension('B')->setWidth(13);
        $sheet->getColumnDimension('C')->setWidth(13);
        $sheet->getColumnDimension('D')->setWidth(25);
        $sheet->getColumnDimension('E')->setWidth(12);
        $sheet->getColumnDimension('F')->setWidth(12);
        $sheet->getColumnDimension('G')->setWidth(12);
        $sheet->getColumnDimension('H')->setWidth(12);
        $sheet->getColumnDimension('I')->setWidth(12);
        $sheet->getColumnDimension('J')->setWidth(12);
        $sheet->getColumnDimension('K')->setWidth(20);

        // Borders
        $border = new Border();
        $border->setStyle(Border::BORDER_THIN);

        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle('A7:K' . $lastRow)->setBorder($border);
    }

    /**
     * Get summary statistics
     */
    public function getSummary(): array
    {
        $query = Invoice::where('empresa_id', $this->empresa->id)
            ->whereBetween('fecha_emision', [$this->fechaInicio, $this->fechaFin]);

        if ($this->estado) {
            $query->where('estado', $this->estado);
        }

        $invoices = $query->with(['taxes'])->get();

        return [
            'total_facturas' => $invoices->count(),
            'total_subtotal' => $invoices->sum('subtotal'),
            'total_descuentos' => $invoices->sum('descuento'),
            'total_impuestos' => $invoices->sum('total_impuestos'),
            'total_ventas' => $invoices->sum('total'),
            'facturas_aceptadas' => $invoices->where('estado', 'aceptada')->count(),
            'facturas_pendientes' => $invoices->where('estado', 'enviada')->count(),
            'facturas_rechazadas' => $invoices->where('estado', 'rechazada')->count(),
        ];
    }
}
