<?php

namespace App\Domains\Invoicing\Services;

use DOMDocument;

class XmlValidator
{
    private array $validationErrors = [];

    public function validateAgainstSchema(string $xmlContent, ?string $schemaPath = null): bool
    {
        $this->validationErrors = [];

        try {
            $dom = new DOMDocument();
            $dom->preserveWhiteSpace = false;
            $dom->loadXML($xmlContent);

            // Basic structural validation
            if (!$this->validateStructure($dom)) {
                return false;
            }

            // If schema path provided, validate against XSD
            if ($schemaPath && file_exists($schemaPath)) {
                return $this->validateWithSchema($dom, $schemaPath);
            }

            // Default validation (checks required elements exist)
            return $this->validateRequiredElements($dom);
        } catch (\Exception $e) {
            $this->validationErrors[] = $e->getMessage();
            return false;
        }
    }

    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }

    private function validateStructure(DOMDocument $dom): bool
    {
        $root = $dom->documentElement;

        if (!$root || $root->nodeName !== 'Invoice') {
            $this->validationErrors[] = 'Root element must be Invoice';
            return false;
        }

        return true;
    }

    private function validateRequiredElements(DOMDocument $dom): bool
    {
        $xpath = new \DOMXPath($dom);

        // Register namespaces
        $xpath->registerNamespace('cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $xpath->registerNamespace('cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');

        $requiredElements = [
            '//cbc:UBLVersionID' => 'UBLVersionID',
            '//cbc:ID' => 'Invoice ID',
            '//cbc:IssueDate' => 'Issue Date',
            '//cbc:InvoiceTypeCode' => 'Invoice Type Code',
            '//cac:AccountingSupplierParty' => 'Supplier Party',
            '//cac:AccountingCustomerParty' => 'Customer Party',
            '//cac:LegalMonetaryTotal' => 'Legal Monetary Total',
        ];

        foreach ($requiredElements as $xpath_expr => $elementName) {
            $nodes = $xpath->query($xpath_expr);
            if ($nodes->length === 0) {
                $this->validationErrors[] = "Required element missing: $elementName";
                return false;
            }
        }

        return true;
    }

    private function validateWithSchema(DOMDocument $dom, string $schemaPath): bool
    {
        set_error_handler(function ($errno, $errstr) {
            if ($errno === E_WARNING) {
                $this->validationErrors[] = $errstr;
            }
        });

        $isValid = $dom->schemaValidate($schemaPath);

        restore_error_handler();

        return $isValid;
    }

    public function validateXmlWellFormed(string $xmlContent): bool
    {
        try {
            $dom = new DOMDocument();
            $dom->loadXML($xmlContent);
            return true;
        } catch (\Exception $e) {
            $this->validationErrors[] = 'Invalid XML: ' . $e->getMessage();
            return false;
        }
    }
}
