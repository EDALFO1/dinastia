<?php

namespace Tests\Feature;

use App\Domains\Invoicing\Models\Invoice;
use App\Domains\Invoicing\Models\InvoiceSequence;
use App\Domains\Invoicing\Services\InvoicePdfGenerator;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoicePdfTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Empresa $empresa;
    protected Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();

        $this->empresa = Empresa::factory()->create();
        $this->user = User::create([
            'empresa_id' => $this->empresa->id,
            'rol_id' => 1,
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
            'estado' => 1,
        ]);

        $sequence = InvoiceSequence::factory()->create([
            'empresa_id' => $this->empresa->id,
        ]);

        $this->invoice = Invoice::factory()->create([
            'empresa_id' => $this->empresa->id,
            'invoice_sequence_id' => $sequence->id,
        ]);
    }

    public function test_pdf_generator_creates_output()
    {
        $generator = new InvoicePdfGenerator($this->invoice);
        $pdf = $generator->generate();

        $this->assertNotEmpty($pdf);
        $this->assertStringContainsString('%PDF', $pdf);
    }

    public function test_pdf_contains_invoice_data()
    {
        $generator = new InvoicePdfGenerator($this->invoice);
        $html = view('invoices.pdf-template', [
            'invoice' => $this->invoice->load(['sequence', 'lineItems', 'taxes']),
            'empresa' => $this->invoice->sequence->empresa,
            'lineItems' => $this->invoice->lineItems,
            'taxes' => $this->invoice->taxes,
        ])->render();

        $this->assertStringContainsString($this->invoice->numero, $html);
        $this->assertStringContainsString($this->invoice->cliente_nombre, $html);
    }

    public function test_download_pdf_endpoint()
    {
        $response = $this
            ->actingAs($this->user)
            ->withHeader('X-Empresa-ID', $this->empresa->id)
            ->get("/api/v1/invoices/{$this->invoice->id}/pdf");

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_formatted_totals()
    {
        $generator = new InvoicePdfGenerator($this->invoice);
        $totals = $generator->getFormattedTotals();

        $this->assertArrayHasKey('subtotal', $totals);
        $this->assertArrayHasKey('total', $totals);
        $this->assertArrayHasKey('total_impuestos', $totals);
    }

    public function test_tax_totals_by_type()
    {
        $generator = new InvoicePdfGenerator($this->invoice);
        $taxTotals = $generator->getTaxTotals();

        // Should have tax breakdown by type
        $this->assertIsArray($taxTotals);
    }
}
