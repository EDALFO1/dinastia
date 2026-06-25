<?php

namespace Tests\Unit\Invoicing;

use App\Domains\Invoicing\Services\XmlValidator;
use PHPUnit\Framework\TestCase;

class XmlValidatorTest extends TestCase
{
    protected XmlValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new XmlValidator();
    }

    public function test_valida_xml_bien_formado(): void
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2">
            <cbc:UBLVersionID xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2">2.1</cbc:UBLVersionID>
            <cbc:ID xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2">123</cbc:ID>
            <cbc:IssueDate xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2">2026-06-25</cbc:IssueDate>
            <cbc:InvoiceTypeCode xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2">01</cbc:InvoiceTypeCode>
            <cac:AccountingSupplierParty xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"/>
            <cac:AccountingCustomerParty xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"/>
            <cac:LegalMonetaryTotal xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"/>
        </Invoice>';

        $isValid = $this->validator->validateAgainstSchema($xml);
        $this->assertTrue($isValid);
    }

    public function test_rechaza_xml_malformado(): void
    {
        $xml = '<?xml version="1.0"?><Invoice><ID>123</ID>';

        $isValid = $this->validator->validateXmlWellFormed($xml);
        $this->assertFalse($isValid);
    }

    public function test_rechaza_root_element_incorrecto(): void
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <Order xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2">
            <ID>123</ID>
        </Order>';

        $isValid = $this->validator->validateAgainstSchema($xml);
        $this->assertFalse($isValid);
    }

    public function test_retorna_errores_validacion(): void
    {
        $xml = '<InvalidXml>';

        $this->validator->validateXmlWellFormed($xml);
        $errors = $this->validator->getValidationErrors();

        $this->assertNotEmpty($errors);
        $this->assertIsArray($errors);
    }
}
