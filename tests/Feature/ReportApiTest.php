<?php

namespace Tests\Feature;

use App\Domains\Invoicing\Models\Invoice;
use App\Domains\Invoicing\Models\InvoiceSequence;
use App\Models\Empresa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Empresa $empresa;

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
    }

    public function test_sales_book_summary()
    {
        $sequence = InvoiceSequence::factory()->create([
            'empresa_id' => $this->empresa->id,
        ]);

        Invoice::factory()->count(5)->create([
            'empresa_id' => $this->empresa->id,
            'invoice_sequence_id' => $sequence->id,
            'estado' => 'aceptada',
            'fecha_emision' => now(),
        ]);

        $response = $this
            ->actingAs($this->user)
            ->withHeader('X-Empresa-ID', $this->empresa->id)
            ->getJson('/api/v1/reports/sales-book-summary', [
                'fecha_inicio' => now()->format('Y-m-d'),
                'fecha_fin' => now()->addDay()->format('Y-m-d'),
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('summary.total_facturas', 5);
        $response->assertJsonPath('summary.facturas_aceptadas', 5);
    }

    public function test_monthly_summary()
    {
        $sequence = InvoiceSequence::factory()->create([
            'empresa_id' => $this->empresa->id,
        ]);

        Invoice::factory()->count(3)->create([
            'empresa_id' => $this->empresa->id,
            'invoice_sequence_id' => $sequence->id,
            'estado' => 'aceptada',
            'fecha_emision' => now(),
        ]);

        $response = $this
            ->actingAs($this->user)
            ->withHeader('X-Empresa-ID', $this->empresa->id)
            ->getJson('/api/v1/reports/monthly-summary', [
                'anio' => now()->year,
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('ano', now()->year);
        $this->assertNotNull($response->json('meses'));
    }

    public function test_sales_book_download()
    {
        $sequence = InvoiceSequence::factory()->create([
            'empresa_id' => $this->empresa->id,
        ]);

        Invoice::factory()->count(2)->create([
            'empresa_id' => $this->empresa->id,
            'invoice_sequence_id' => $sequence->id,
            'estado' => 'aceptada',
            'fecha_emision' => now(),
        ]);

        $response = $this
            ->actingAs($this->user)
            ->withHeader('X-Empresa-ID', $this->empresa->id)
            ->getJson('/api/v1/reports/sales-book', [
                'fecha_inicio' => now()->subMonth()->format('Y-m-d'),
                'fecha_fin' => now()->addMonth()->format('Y-m-d'),
            ]);

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_audit_log_endpoint()
    {
        $sequence = InvoiceSequence::factory()->create([
            'empresa_id' => $this->empresa->id,
        ]);

        $invoice = Invoice::factory()->create([
            'empresa_id' => $this->empresa->id,
            'invoice_sequence_id' => $sequence->id,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->withHeader('X-Empresa-ID', $this->empresa->id)
            ->getJson('/api/v1/reports/invoice-audit-log', [
                'invoice_id' => $invoice->id,
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['data', 'pagination']);
    }

    public function test_sales_book_with_estado_filter()
    {
        $sequence = InvoiceSequence::factory()->create([
            'empresa_id' => $this->empresa->id,
        ]);

        Invoice::factory()->count(3)->create([
            'empresa_id' => $this->empresa->id,
            'invoice_sequence_id' => $sequence->id,
            'estado' => 'aceptada',
            'fecha_emision' => now(),
        ]);

        Invoice::factory()->count(2)->create([
            'empresa_id' => $this->empresa->id,
            'invoice_sequence_id' => $sequence->id,
            'estado' => 'borrador',
            'fecha_emision' => now(),
        ]);

        // Download with filter for 'aceptada' only
        $response = $this
            ->actingAs($this->user)
            ->withHeader('X-Empresa-ID', $this->empresa->id)
            ->getJson('/api/v1/reports/sales-book', [
                'fecha_inicio' => now()->subMonth()->format('Y-m-d'),
                'fecha_fin' => now()->addMonth()->format('Y-m-d'),
                'estado' => 'aceptada',
            ]);

        $response->assertStatus(200);
    }
}
