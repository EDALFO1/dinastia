<?php

namespace App\Domains\Invoicing\Services;

use App\Domains\Invoicing\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class InvoicePdfGenerator
{
    protected Invoice $invoice;
    protected string $template = 'invoices.pdf-template';

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice->load(['sequence', 'lineItems', 'taxes']);
    }

    /**
     * Generate PDF from invoice
     */
    public function generate(): string
    {
        $html = view($this->template, [
            'invoice' => $this->invoice,
            'empresa' => $this->invoice->sequence->empresa ?? null,
            'lineItems' => $this->invoice->lineItems,
            'taxes' => $this->invoice->taxes,
        ])->render();

        $pdf = Pdf::loadHTML($html)
            ->setPaper('a4')
            ->setOptions([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'defaultFont' => 'Arial',
                'DPI' => 96,
                'dpi' => 96,
            ]);

        return $pdf->output();
    }

    /**
     * Download PDF file
     */
    public function download()
    {
        $filename = sprintf('factura_%s_%s.pdf',
            $this->invoice->numero,
            now()->format('Y-m-d_His')
        );

        return Pdf::loadHTML($this->getHtml())
            ->setPaper('a4')
            ->setOptions([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'defaultFont' => 'Arial',
                'DPI' => 96,
            ])
            ->download($filename);
    }

    /**
     * Save PDF to storage
     */
    public function save(string $path = 'invoices'): string
    {
        $filename = sprintf('factura_%s.pdf', $this->invoice->uuid_dian ?? $this->invoice->id);
        $filePath = "$path/$filename";

        $pdf = $this->generate();
        \Storage::disk('local')->put($filePath, $pdf);

        return $filePath;
    }

    /**
     * Get HTML for PDF
     */
    protected function getHtml(): string
    {
        return view($this->template, [
            'invoice' => $this->invoice,
            'empresa' => $this->invoice->sequence->empresa,
            'lineItems' => $this->invoice->lineItems,
            'taxes' => $this->invoice->taxes,
        ])->render();
    }

    /**
     * Calculate totals by tax type
     */
    public function getTaxTotals(): array
    {
        $totals = [];

        foreach ($this->invoice->taxes as $tax) {
            $tipo = $tax->tipo_impuesto->value;

            if (!isset($totals[$tipo])) {
                $totals[$tipo] = [
                    'porcentaje' => $tax->porcentaje,
                    'valor' => 0,
                    'base' => 0,
                ];
            }

            $totals[$tipo]['valor'] += $tax->valor;
            $totals[$tipo]['base'] = $this->invoice->subtotal - $this->invoice->descuento;
        }

        return $totals;
    }

    /**
     * Get formatted totals for display
     */
    public function getFormattedTotals(): array
    {
        return [
            'subtotal' => number_format($this->invoice->subtotal, 2, ',', '.'),
            'descuento' => number_format($this->invoice->descuento, 2, ',', '.'),
            'subtotal_after_discount' => number_format(
                $this->invoice->subtotal - $this->invoice->descuento,
                2,
                ',',
                '.'
            ),
            'total_impuestos' => number_format($this->invoice->total_impuestos, 2, ',', '.'),
            'total' => number_format($this->invoice->total, 2, ',', '.'),
        ];
    }
}
