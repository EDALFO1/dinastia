<?php

namespace Tests\Feature\Invoicing;

use App\Domains\Invoicing\Models\Invoice;
use App\Domains\Invoicing\Services\InvoiceXmlGenerator;
use App\Models\Empresa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceXmlGeneratorTest extends TestCase
{
    use RefreshDatabase;

    protected Empresa $empresa;

    protected function setUp(): void
    {
        parent::setUp();
        $this->empresa = Empresa::factory()->create();
    }

    public function test_genera_xml_valido_para_factura(): void
    {
        $invoice = Invoice::factory()->create(['empresa_id' => $this->empresa->id]);

        $generator = new InvoiceXmlGenerator();
        $xml = $generator->generate($invoice);

        $this->assertStringContainsString('<?xml', $xml);
        $this->assertStringContainsString('<Invoice', $xml);
        $this->assertStringContainsString('UBLVersionID', $xml);
        $this->assertStringContainsString('2.1', $xml);
    }

    public function test_xml_contiene_informacion_factura(): void
    {
        $invoice = Invoice::factory()->create([
            'empresa_id' => $this->empresa->id,
            'numero' => 12345,
            'cliente_nombre' => 'Test Client',
        ]);

        $generator = new InvoiceXmlGenerator();
        $xml = $generator->generate($invoice);

        $this->assertStringContainsString('12345', $xml);
        $this->assertStringContainsString('Test Client', $xml);
    }

    public function test_metodo_to_xml_en_modelo(): void
    {
        $invoice = Invoice::factory()->create(['empresa_id' => $this->empresa->id]);

        $xml = $invoice->toXml();

        $this->assertIsString($xml);
        $this->assertStringContainsString('<Invoice', $xml);
    }

    public function test_xml_es_bien_formado(): void
    {
        $invoice = Invoice::factory()->create(['empresa_id' => $this->empresa->id]);

        $generator = new InvoiceXmlGenerator();
        $xml = $generator->generate($invoice);

        $dom = new \DOMDocument();
        $isValid = @$dom->loadXML($xml);

        $this->assertTrue($isValid, 'Generated XML is malformed');
    }
}
