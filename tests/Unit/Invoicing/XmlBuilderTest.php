<?php

namespace Tests\Unit\Invoicing;

use App\Domains\Invoicing\Services\XmlBuilder;
use PHPUnit\Framework\TestCase;

class XmlBuilderTest extends TestCase
{
    public function test_crea_documento_xml_valido(): void
    {
        $builder = new XmlBuilder();

        $xml = $builder->getXmlString();

        $this->assertStringContainsString('<?xml', $xml);
        $this->assertStringContainsString('<Invoice', $xml);
        $this->assertStringContainsString('</Invoice>', $xml);
    }

    public function test_agrega_elementos_cbc_correctamente(): void
    {
        $builder = new XmlBuilder();

        $builder->addCbcElement('UBLVersionID', '2.1');
        $builder->addCbcElement('ID', '123456');

        $xml = $builder->getXmlString();

        $this->assertStringContainsString('UBLVersionID', $xml);
        $this->assertStringContainsString('2.1', $xml);
        $this->assertStringContainsString('ID', $xml);
        $this->assertStringContainsString('123456', $xml);
    }

    public function test_agrega_elementos_cac_correctamente(): void
    {
        $builder = new XmlBuilder();

        $partyElement = $builder->addCacElement('AccountingSupplierParty');
        $this->assertNotNull($partyElement);

        $xml = $builder->getXmlString();
        $this->assertStringContainsString('AccountingSupplierParty', $xml);
    }

    public function test_respeta_namespaces_ubl(): void
    {
        $builder = new XmlBuilder();

        $xml = $builder->getXmlString();

        $this->assertStringContainsString('urn:oasis:names:specification:ubl:schema:xsd:Invoice-2', $xml);
        $this->assertStringContainsString('urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2', $xml);
        $this->assertStringContainsString('urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2', $xml);
    }
}
