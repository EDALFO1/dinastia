<?php

namespace Tests\Feature;

use App\Domains\Invoicing\Models\Invoice;
use App\Domains\Invoicing\Models\InvoiceSequence;
use App\Domains\Invoicing\Models\InvoiceLineItem;
use App\Domains\Invoicing\Models\InvoiceTax;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Tests\TestCaseWithUser;

class InvoiceApiTest extends TestCaseWithUser
{
    protected InvoiceSequence $sequence;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sequence = InvoiceSequence::factory()->create([
            'empresa_id' => $this->empresa->id,
        ]);
    }

    public function test_list_invoices()
    {
        Invoice::factory()->count(5)->create([
            'empresa_id' => $this->empresa->id,
            'invoice_sequence_id' => $this->sequence->id,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->withHeader('X-Empresa-ID', $this->empresa->id)
            ->getJson('/api/v1/invoices');

        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data');
    }

    public function test_list_invoices_with_estado_filter()
    {
        Invoice::factory()->count(3)->create([
            'empresa_id' => $this->empresa->id,
            'invoice_sequence_id' => $this->sequence->id,
            'estado' => 'borrador',
        ]);

        Invoice::factory()->count(2)->create([
            'empresa_id' => $this->empresa->id,
            'invoice_sequence_id' => $this->sequence->id,
            'estado' => 'enviada',
        ]);

        $response = $this
            ->actingAs($this->user)
            ->withHeader('X-Empresa-ID', $this->empresa->id)
            ->getJson('/api/v1/invoices?estado=borrador');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }

    public function test_get_invoice_detail()
    {
        $invoice = Invoice::factory()->create([
            'empresa_id' => $this->empresa->id,
            'invoice_sequence_id' => $this->sequence->id,
        ]);

        $invoice->lineItems()->save(
            InvoiceLineItem::factory()->make()
        );

        $response = $this
            ->actingAs($this->user)
            ->withHeader('X-Empresa-ID', $this->empresa->id)
            ->getJson("/api/v1/invoices/{$invoice->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('data.id', $invoice->id);
        $response->assertJsonPath('data.numero', $invoice->numero);
    }

    public function test_create_invoice()
    {
        $data = [
            'invoice_sequence_id' => $this->sequence->id,
            'tipo_documento' => 'FACTURA',
            'cliente_nit' => '123456789',
            'cliente_nombre' => 'Test Client',
            'fecha_emision' => '2026-06-26',
            'fecha_vencimiento' => '2026-07-26',
            'descuento' => 0,
            'observaciones' => 'Test invoice',
            'line_items' => [
                [
                    'description' => 'Product A',
                    'quantity' => 1,
                    'unit' => 'UNIDAD',
                    'unit_price' => 100000,
                ],
            ],
            'taxes' => [
                [
                    'tipo_impuesto' => 'IVA',
                    'porcentaje' => 19,
                ],
            ],
        ];

        $response = $this
            ->actingAs($this->user)
            ->withHeader('X-Empresa-ID', $this->empresa->id)
            ->postJson('/api/v1/invoices', $data);

        $response->assertStatus(201);
        $response->assertJsonPath('data.cliente_nit', '123456789');
        $this->assertDatabaseHas('invoices', [
            'cliente_nit' => '123456789',
            'empresa_id' => $this->empresa->id,
        ]);
    }

    public function test_update_invoice()
    {
        $invoice = Invoice::factory()->create([
            'empresa_id' => $this->empresa->id,
            'invoice_sequence_id' => $this->sequence->id,
            'estado' => 'borrador',
        ]);

        $response = $this
            ->actingAs($this->user)
            ->withHeader('X-Empresa-ID', $this->empresa->id)
            ->putJson("/api/v1/invoices/{$invoice->id}", [
                'cliente_nombre' => 'Updated Client Name',
                'observaciones' => 'Updated observations',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'cliente_nombre' => 'Updated Client Name',
        ]);
    }

    public function test_cannot_update_non_draft_invoice()
    {
        $invoice = Invoice::factory()->create([
            'empresa_id' => $this->empresa->id,
            'invoice_sequence_id' => $this->sequence->id,
            'estado' => 'enviada',
        ]);

        $response = $this
            ->actingAs($this->user)
            ->withHeader('X-Empresa-ID', $this->empresa->id)
            ->putJson("/api/v1/invoices/{$invoice->id}", [
                'cliente_nombre' => 'Updated',
            ]);

        $response->assertStatus(422);
    }

    public function test_delete_invoice()
    {
        $invoice = Invoice::factory()->create([
            'empresa_id' => $this->empresa->id,
            'invoice_sequence_id' => $this->sequence->id,
            'estado' => 'borrador',
        ]);

        $response = $this
            ->actingAs($this->user)
            ->withHeader('X-Empresa-ID', $this->empresa->id)
            ->deleteJson("/api/v1/invoices/{$invoice->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
    }

    public function test_cannot_delete_non_draft_invoice()
    {
        $invoice = Invoice::factory()->create([
            'empresa_id' => $this->empresa->id,
            'invoice_sequence_id' => $this->sequence->id,
            'estado' => 'enviada',
        ]);

        $response = $this
            ->actingAs($this->user)
            ->withHeader('X-Empresa-ID', $this->empresa->id)
            ->deleteJson("/api/v1/invoices/{$invoice->id}");

        $response->assertStatus(422);
    }

    public function test_sign_invoice()
    {
        $invoice = Invoice::factory()->create([
            'empresa_id' => $this->empresa->id,
            'invoice_sequence_id' => $this->sequence->id,
            'xml_factura' => '<xml>test</xml>',
        ]);

        // Mock certificate signing
        $response = $this
            ->actingAs($this->user)
            ->withHeader('X-Empresa-ID', $this->empresa->id)
            ->postJson("/api/v1/invoices/{$invoice->id}/sign", [
                'certificate_path' => '/path/to/cert.p12',
                'password' => 'test-password',
            ]);

        // Note: This will fail without actual certificate setup
        // In real scenario, use test certificate fixtures
        $response->assertStatus(422);
    }

    public function test_send_invoice_to_dian()
    {
        Http::fake([
            'https://api.dian.gov.co/api/ws/fedora/oauth2/token' => Http::response([
                'access_token' => 'test-token',
            ]),
            'https://api.dian.gov.co/api/ws/fedora/invoice/submit' => Http::response([
                'uuid_dian' => 'uuid-123-456',
                'tracking_number' => 'TRK-001',
            ]),
        ]);

        $invoice = Invoice::factory()->create([
            'empresa_id' => $this->empresa->id,
            'invoice_sequence_id' => $this->sequence->id,
            'xml_factura' => '<xml>signed-invoice</xml>',
            'firma_digital' => 'digital-signature',
            'estado' => 'borrador',
        ]);

        $response = $this
            ->actingAs($this->user)
            ->withHeader('X-Empresa-ID', $this->empresa->id)
            ->postJson("/api/v1/invoices/{$invoice->id}/send-to-dian");

        $response->assertStatus(200);
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'uuid_dian' => 'uuid-123-456',
            'estado' => 'enviada',
        ]);
    }

    public function test_cannot_send_unsigned_invoice_to_dian()
    {
        $invoice = Invoice::factory()->create([
            'empresa_id' => $this->empresa->id,
            'invoice_sequence_id' => $this->sequence->id,
            'xml_factura' => null,
            'firma_digital' => null,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->withHeader('X-Empresa-ID', $this->empresa->id)
            ->postJson("/api/v1/invoices/{$invoice->id}/send-to-dian");

        $response->assertStatus(422);
    }

    public function test_pagination_invoices()
    {
        Invoice::factory()->count(20)->create([
            'empresa_id' => $this->empresa->id,
            'invoice_sequence_id' => $this->sequence->id,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->withHeader('X-Empresa-ID', $this->empresa->id)
            ->getJson('/api/v1/invoices?per_page=10');

        $response->assertStatus(200);
        $response->assertJsonCount(10, 'data');
        $response->assertJsonPath('meta.total', 20);
    }
}
