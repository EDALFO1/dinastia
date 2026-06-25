<?php

namespace App\Domains\Invoicing\Services;

use DOMDocument;
use DOMElement;

class XmlBuilder
{
    protected DOMDocument $dom;
    protected DOMElement $root;

    public const UBL_NAMESPACE = 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2';
    public const CBC_NAMESPACE = 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2';
    public const CAC_NAMESPACE = 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2';
    public const DS_NAMESPACE = 'http://www.w3.org/2000/09/xmldsig#';
    public const EXT_NAMESPACE = 'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2';

    public function __construct()
    {
        $this->dom = new DOMDocument('1.0', 'UTF-8');
        $this->dom->formatOutput = true;

        $this->root = $this->dom->createElementNS(self::UBL_NAMESPACE, 'Invoice');
        $this->root->setAttributeNS(
            'http://www.w3.org/2000/xmlns/',
            'xmlns:cbc',
            self::CBC_NAMESPACE
        );
        $this->root->setAttributeNS(
            'http://www.w3.org/2000/xmlns/',
            'xmlns:cac',
            self::CAC_NAMESPACE
        );
        $this->root->setAttributeNS(
            'http://www.w3.org/2000/xmlns/',
            'xmlns:ds',
            self::DS_NAMESPACE
        );
        $this->root->setAttributeNS(
            'http://www.w3.org/2000/xmlns/',
            'xmlns:ext',
            self::EXT_NAMESPACE
        );

        $this->dom->appendChild($this->root);
    }

    public function addCbcElement(string $elementName, string $value, array $attributes = []): DOMElement
    {
        $element = $this->dom->createElementNS(self::CBC_NAMESPACE, "cbc:$elementName");
        $element->nodeValue = htmlspecialchars($value, ENT_XML1, 'UTF-8');

        foreach ($attributes as $attrName => $attrValue) {
            $element->setAttribute($attrName, $attrValue);
        }

        $this->root->appendChild($element);
        return $element;
    }

    public function addCacElement(string $elementName): DOMElement
    {
        $element = $this->dom->createElementNS(self::CAC_NAMESPACE, "cac:$elementName");
        $this->root->appendChild($element);
        return $element;
    }

    public function addNestedCbcElement(DOMElement $parent, string $elementName, string $value, array $attributes = []): DOMElement
    {
        $element = $this->dom->createElementNS(self::CBC_NAMESPACE, "cbc:$elementName");
        $element->nodeValue = htmlspecialchars($value, ENT_XML1, 'UTF-8');

        foreach ($attributes as $attrName => $attrValue) {
            $element->setAttribute($attrName, $attrValue);
        }

        $parent->appendChild($element);
        return $element;
    }

    public function addNestedCacElement(DOMElement $parent, string $elementName): DOMElement
    {
        $element = $this->dom->createElementNS(self::CAC_NAMESPACE, "cac:$elementName");
        $parent->appendChild($element);
        return $element;
    }

    public function addPartyIdentification(DOMElement $parent, string $identificationType, string $identificationValue): DOMElement
    {
        $partyId = $this->addNestedCacElement($parent, 'PartyIdentification');
        $this->addNestedCbcElement($partyId, 'ID', $identificationValue, ['schemeID' => $identificationType]);
        return $partyId;
    }

    public function addPartyName(DOMElement $parent, string $name): DOMElement
    {
        $partyName = $this->addNestedCacElement($parent, 'PartyName');
        $this->addNestedCbcElement($partyName, 'Name', $name);
        return $partyName;
    }

    public function addPartyLegalEntity(DOMElement $parent, string $registrationName, string $companyId, string $schemeId = 'NIT'): DOMElement
    {
        $legalEntity = $this->addNestedCacElement($parent, 'PartyLegalEntity');
        $this->addNestedCbcElement($legalEntity, 'RegistrationName', $registrationName);
        $this->addNestedCbcElement($legalEntity, 'CompanyID', $companyId, ['schemeID' => $schemeId]);
        return $legalEntity;
    }

    public function addAddress(DOMElement $parent, array $addressData): DOMElement
    {
        $address = $this->addNestedCacElement($parent, 'PostalAddress');

        if (isset($addressData['street'])) {
            $this->addNestedCbcElement($address, 'StreetName', $addressData['street']);
        }
        if (isset($addressData['city'])) {
            $this->addNestedCbcElement($address, 'CityName', $addressData['city']);
        }
        if (isset($addressData['country'])) {
            $this->addNestedCbcElement($address, 'Country', $addressData['country']);
        }

        return $address;
    }

    public function getXmlString(): string
    {
        return $this->dom->saveXML();
    }

    public function getDomDocument(): DOMDocument
    {
        return $this->dom;
    }

    public function getRoot(): DOMElement
    {
        return $this->root;
    }
}
