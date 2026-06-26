<?php

namespace Tests\Feature;

use App\Domains\Invoicing\Models\Invoice;
use App\Domains\Invoicing\Models\InvoiceSequence;
use App\Domains\Invoicing\Services\DianApiClient;
use App\Models\Empresa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DianApiClientTest extends TestCase
{
    use RefreshDatabase;
    protected DianApiClient $client;
    protected Empresa $empresa;
    protected InvoiceSequence $sequence;

    protected function setUp(): void
    {
        parent::setUp();

        config(['invoicing.dian' => [
            'client_id' => 'test-client-id',
            'client_secret' => 'test-client-secret',
            'api_url' => 'https://api.test.dian.gov.co',
        ]]);

        $this->client = new DianApiClient();

        $this->empresa = Empresa::factory()->create();
        session(['empresa_id' => $this->empresa->id]);

        $this->sequence = InvoiceSequence::factory()->create([
            'empresa_id' => $this->empresa->id,
            'estado' => 'activa',
        ]);
    }

    public function test_authenticate_returns_access_token()
    {
        Http::fake([
            'https://api.test.dian.gov.co/oauth2/token' => Http::response([
                'access_token' => 'test-token-123',
                'expires_in' => 3600,
            ]),
        ]);

        $token = $this->client->authenticate();

        $this->assertEquals('test-token-123', $token);
    }

    public function test_send_invoice_successfully()
    {
        Http::fake([
            'https://api.test.dian.gov.co/oauth2/token' => Http::response([
                'access_token' => 'test-token-123',
            ]),
            'https://api.test.dian.gov.co/invoice/submit' => Http::response([
                'uuid_dian' => 'uuid-123-456',
                'tracking_number' => 'TRK-001',
                'status_url' => 'https://status.dian.gov.co/uuid-123-456',
            ]),
        ]);

        $invoice = Invoice::factory()->create([
            'empresa_id' => $this->empresa->id,
            'invoice_sequence_id' => $this->sequence->id,
            'xml_factura' => '<xml>test</xml>',
            'firma_digital' => 'signature-data',
        ]);

        $result = $this->client->sendInvoice($invoice);

        $this->assertTrue($result['success']);
        $this->assertEquals('uuid-123-456', $result['uuid_dian']);
        $this->assertEquals('TRK-001', $result['tracking_number']);
    }

    public function test_send_invoice_throws_exception_if_not_signed()
    {
        $invoice = Invoice::factory()->create([
            'empresa_id' => $this->empresa->id,
            'invoice_sequence_id' => $this->sequence->id,
            'xml_factura' => null,
            'firma_digital' => null,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invoice must be signed before sending');

        $this->client->sendInvoice($invoice);
    }

    public function test_get_invoice_status()
    {
        Http::fake([
            'https://api.test.dian.gov.co/oauth2/token' => Http::response([
                'access_token' => 'test-token-123',
            ]),
            'https://api.test.dian.gov.co/invoice/status/uuid-123-456' => Http::response([
                'status' => 'accepted',
                'message' => 'Invoice accepted',
            ]),
        ]);

        $result = $this->client->getInvoiceStatus('uuid-123-456');

        $this->assertEquals('uuid-123-456', $result['uuid_dian']);
        $this->assertEquals('accepted', $result['status']);
        $this->assertEquals('Invoice accepted', $result['message']);
    }

    public function test_receive_ack_updates_invoice_status()
    {
        $invoice = Invoice::factory()->create([
            'empresa_id' => $this->empresa->id,
            'invoice_sequence_id' => $this->sequence->id,
            'uuid_dian' => 'uuid-123-456',
            'estado' => 'enviada',
        ]);

        $this->client->receiveAck([
            'uuid_dian' => 'uuid-123-456',
            'status' => 'ACEPTACION',
        ]);

        $this->assertEquals('aceptada', $invoice->fresh()->estado);
    }

    public function test_receive_ack_marks_rejected_invoices()
    {
        $invoice = Invoice::factory()->create([
            'empresa_id' => $this->empresa->id,
            'invoice_sequence_id' => $this->sequence->id,
            'uuid_dian' => 'uuid-789',
            'estado' => 'enviada',
        ]);

        $this->client->receiveAck([
            'uuid_dian' => 'uuid-789',
            'status' => 'RECHAZO',
            'rejection_reason' => 'Invalid total amount',
        ]);

        $this->assertEquals('rechazada', $invoice->fresh()->estado);
    }

    public function test_validate_invoice()
    {
        Http::fake([
            'https://api.test.dian.gov.co/oauth2/token' => Http::response([
                'access_token' => 'test-token-123',
            ]),
            'https://api.test.dian.gov.co/invoice/validate' => Http::response([
                'is_valid' => true,
                'errors' => [],
                'warnings' => [],
            ]),
        ]);

        $invoice = Invoice::factory()->create([
            'empresa_id' => $this->empresa->id,
            'invoice_sequence_id' => $this->sequence->id,
            'xml_factura' => '<xml>test</xml>',
            'estado' => 'borrador',
        ]);

        $result = $this->client->validateInvoice($invoice);

        $this->assertTrue($result['is_valid']);
        $this->assertEmpty($result['errors']);
    }

    public function test_revoke_invoice()
    {
        Http::fake([
            'https://api.test.dian.gov.co/oauth2/token' => Http::response([
                'access_token' => 'test-token-123',
            ]),
            'https://api.test.dian.gov.co/invoice/revoke/uuid-123-456' => Http::response([
                'revocation_id' => 'REV-001',
            ]),
        ]);

        $invoice = Invoice::factory()->create([
            'empresa_id' => $this->empresa->id,
            'invoice_sequence_id' => $this->sequence->id,
            'uuid_dian' => 'uuid-123-456',
            'estado' => 'aceptada',
        ]);

        $result = $this->client->revokeInvoice($invoice, 'Test revocation');

        $this->assertTrue($result['success']);
        $this->assertEquals('REV-001', $result['revocation_id']);
    }

    public function test_send_invoice_retries_on_failure()
    {
        Http::fake([
            'https://api.test.dian.gov.co/oauth2/token' => Http::response([
                'access_token' => 'test-token-123',
            ]),
            'https://api.test.dian.gov.co/invoice/submit' => Http::sequence()
                ->push([], 500)
                ->push([], 500)
                ->push([
                    'uuid_dian' => 'uuid-123',
                    'tracking_number' => 'TRK-001',
                ]),
        ]);

        $invoice = Invoice::factory()->create([
            'empresa_id' => $this->empresa->id,
            'invoice_sequence_id' => $this->sequence->id,
            'xml_factura' => '<xml>test</xml>',
            'firma_digital' => 'signature-data',
        ]);

        $result = $this->client->sendInvoice($invoice);

        $this->assertEquals('uuid-123', $result['uuid_dian']);
        Http::assertSentCount(4); // 3 attempts + 1 auth
    }
}
