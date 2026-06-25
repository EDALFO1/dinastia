<?php

namespace Tests\Unit\Invoicing;

use App\Domains\Invoicing\Services\CertificateManager;
use PHPUnit\Framework\TestCase;

class CertificateManagerTest extends TestCase
{
    protected CertificateManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = new CertificateManager();
    }

    public function test_rechaza_certificado_no_existente(): void
    {
        $this->expectException(\Exception::class);
        $this->manager->loadCertificate('/nonexistent/certificate.p12');
    }

    public function test_valida_certificado_estructura(): void
    {
        $cert = [
            'cert' => null,
            'key' => null,
        ];

        $isValid = $this->manager->validateCertificate($cert);
        $this->assertFalse($isValid);
    }

    public function test_obtiene_info_certificado_vacio(): void
    {
        $cert = [
            'cert' => '',
        ];

        $info = $this->manager->getCertificateInfo($cert);
        $this->assertEmpty($info);
    }

    public function test_retorna_array_vacio_para_certificado_invalido(): void
    {
        $cert = [
            'cert' => 'invalid_cert_data',
        ];

        $info = $this->manager->getCertificateInfo($cert);
        $this->assertIsArray($info);
    }
}
