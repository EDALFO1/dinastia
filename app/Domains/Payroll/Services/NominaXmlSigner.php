<?php

namespace App\Domains\Payroll\Services;

use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;

class NominaXmlSigner
{
    protected ?string $signatureInfo = null;

    /**
     * Firmar nómina XML con certificado
     */
    public function sign(string $xmlContent, string $certificatePath, string $password = ''): string
    {
        $doc = new \DOMDocument();
        $doc->load($xmlContent, LIBXML_NOENT | LIBXML_DTDLOAD);

        // Crear objeto de seguridad
        $objDSig = new XMLSecurityDSig();
        $objDSig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);

        // Agregar firma al documento
        $objDSig->addReference(
            $doc,
            XMLSecurityDSig::SHA256,
            ['http://www.w3.org/2000/09/xmldsig#enveloped-signature'],
            ['force_uri' => true]
        );

        // Cargar certificado privado
        $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'private']);

        // Cargar el certificado PKCS12
        if (!file_exists($certificatePath)) {
            throw new \Exception("Certificado no encontrado: $certificatePath");
        }

        // Cargar PKCS12
        $pkcs12 = file_get_contents($certificatePath);
        $certs = [];

        if (!openssl_pkcs12_read($pkcs12, $certs, $password)) {
            throw new \Exception("No se pudo leer el certificado. Verifique la contraseña.");
        }

        // Cargar clave privada
        $objKey->loadKey($certs['pkey']);

        // Firmar el documento
        $objDSig->sign($objKey);

        // Agregar firma al documento
        $objDSig->appendSignature($doc->documentElement);

        // Guardar firma info
        $this->signatureInfo = [
            'algorithm' => 'RSA-SHA256',
            'timestamp' => now()->toIso8601String(),
            'thumbprint' => $this->getThumbprint($certs),
        ];

        return $doc->saveXML();
    }

    /**
     * Validar que XML está firmado correctamente
     */
    public function validateSignature(string $xmlContent): bool
    {
        $doc = new \DOMDocument();
        $doc->load($xmlContent, LIBXML_NOENT | LIBXML_DTDLOAD);

        $objDSig = new XMLSecurityDSig();

        // Buscar firma
        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');

        $signature = $xpath->query('//ds:Signature')->item(0);

        if (!$signature) {
            throw new \Exception('XML no contiene firma digital');
        }

        $objDSig->setReference($signature);

        // Validar firma
        $objKey = $objDSig->getValidatingKey();

        if (!$objKey) {
            return false;
        }

        return $objDSig->validateReference();
    }

    /**
     * Obtener información de la firma
     */
    public function getSignatureInfo(string $xmlContent): array
    {
        return $this->signatureInfo ?? [];
    }

    /**
     * Calcular thumbprint del certificado
     */
    private function getThumbprint(array $certs): string
    {
        if (!isset($certs['cert'])) {
            return '';
        }

        // Extraer certificado PEM
        $certData = $certs['cert'];
        $thumbprint = sha1($certData);

        return $thumbprint;
    }
}
