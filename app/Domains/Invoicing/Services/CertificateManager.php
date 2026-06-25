<?php

namespace App\Domains\Invoicing\Services;

class CertificateManager
{
    public function loadCertificate(string $path, string $password = ''): ?array
    {
        if (!file_exists($path)) {
            throw new \Exception("Certificado no encontrado: $path");
        }

        $certs = [];
        $cert_data = file_get_contents($path);

        // Try to parse as PKCS12
        if (openssl_pkcs12_read($cert_data, $certs, $password)) {
            return [
                'cert' => $certs['cert'] ?? null,
                'key' => $certs['pkey'] ?? null,
                'extracerts' => $certs['extracerts'] ?? [],
            ];
        }

        // Try to parse as PEM certificate
        if (strpos($cert_data, '-----BEGIN CERTIFICATE-----') !== false) {
            return [
                'cert' => $cert_data,
                'key' => null,
                'extracerts' => [],
            ];
        }

        throw new \Exception("Formato de certificado no soportado");
    }

    public function validateCertificate(array $cert): bool
    {
        if (!isset($cert['cert']) || empty($cert['cert'])) {
            return false;
        }

        $certData = openssl_x509_parse($cert['cert']);

        if ($certData === false) {
            return false;
        }

        // Check if certificate is still valid
        $now = time();
        $validFrom = $certData['validFrom_time_t'] ?? 0;
        $validTo = $certData['validTo_time_t'] ?? 0;

        return $now >= $validFrom && $now <= $validTo;
    }

    public function getCertificateInfo(array $cert): array
    {
        if (!isset($cert['cert']) || empty($cert['cert'])) {
            return [];
        }

        $certData = openssl_x509_parse($cert['cert']);

        if ($certData === false) {
            return [];
        }

        return [
            'subject' => $certData['subject'] ?? [],
            'issuer' => $certData['issuer'] ?? [],
            'version' => $certData['version'] ?? null,
            'serialNumber' => $certData['serialNumber'] ?? null,
            'validFrom' => date('Y-m-d H:i:s', $certData['validFrom_time_t'] ?? 0),
            'validTo' => date('Y-m-d H:i:s', $certData['validTo_time_t'] ?? 0),
            'validFromTimeT' => $certData['validFrom_time_t'] ?? 0,
            'validToTimeT' => $certData['validTo_time_t'] ?? 0,
            'isValid' => $this->validateCertificate($cert),
        ];
    }

    public function getCertificateThumbprint(array $cert): ?string
    {
        if (!isset($cert['cert']) || empty($cert['cert'])) {
            return null;
        }

        $cert_data = $cert['cert'];

        // Remove PEM header/footer if present
        $cert_data = preg_replace("/-----BEGIN CERTIFICATE-----/", '', $cert_data);
        $cert_data = preg_replace("/-----END CERTIFICATE-----/", '', $cert_data);
        $cert_data = preg_replace("/\n/", '', $cert_data);

        // Decode and calculate SHA1 hash
        $binary = base64_decode($cert_data);
        $thumbprint = sha1($binary, true);

        // Convert to hex string
        return strtoupper(bin2hex($thumbprint));
    }
}
