<?php

namespace App\Domains\Invoicing\Services;

use DOMDocument;
use XMLSecurityDSig;
use XMLSecurityKey;

class XmlSigner
{
    private XMLSecurityDSig $xmlDSig;

    public function sign(string $xmlContent, string $certificatePath, string $privateKeyPassword = ''): string
    {
        if (!file_exists($certificatePath)) {
            throw new \Exception("Certificado no encontrado: $certificatePath");
        }

        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->load($certificatePath);

        if ($dom->load($certificatePath) === false) {
            $dom = new DOMDocument();
            $dom->preserveWhiteSpace = false;
            $dom->loadXML($xmlContent);
        } else {
            // It's a file path to XML
            $dom = new DOMDocument();
            $dom->preserveWhiteSpace = false;
            $dom->loadXML($xmlContent);
        }

        $this->xmlDSig = new XMLSecurityDSig();
        $this->xmlDSig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);

        $key = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'private']);

        // Load private key
        if (!file_exists($certificatePath)) {
            throw new \Exception("Certificado o clave privada no encontrado");
        }

        $key->loadKey($certificatePath, true);

        // Sign with reference to root element
        $root = $dom->documentElement;
        if ($root) {
            $this->xmlDSig->addReferenceList([$root], XMLSecurityDSig::SHA256, null, true);
            $this->xmlDSig->sign($key, $root);
        }

        return $dom->saveXML();
    }

    public function validate(string $signedXmlContent): bool
    {
        try {
            $dom = new DOMDocument();
            $dom->preserveWhiteSpace = false;
            $dom->loadXML($signedXmlContent);

            $dsig = new XMLSecurityDSig();
            $dsigNode = $dsig->locateSignature($dom);

            if (!$dsigNode) {
                return false;
            }

            $dsig->loadSignatureNode($dsigNode);

            return $dsig->validateReference();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getSignatureInfo(string $signedXmlContent): array
    {
        try {
            $dom = new DOMDocument();
            $dom->preserveWhiteSpace = false;
            $dom->loadXML($signedXmlContent);

            $dsig = new XMLSecurityDSig();
            $dsigNode = $dsig->locateSignature($dom);

            if (!$dsigNode) {
                return [];
            }

            $dsig->loadSignatureNode($dsigNode);

            return [
                'algorithm' => 'RSA-SHA256',
                'method' => $dsig->signatureMethod ?? 'unknown',
                'valid' => $dsig->validateReference(),
                'timestamp' => date('Y-m-d H:i:s'),
            ];
        } catch (\Exception $e) {
            return [];
        }
    }
}
