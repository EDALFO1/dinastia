<?php

namespace Tests\Unit\Invoicing;

use App\Domains\Invoicing\Services\DianValidator;
use PHPUnit\Framework\TestCase;

class DianValidatorTest extends TestCase
{
    protected DianValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new DianValidator();
    }

    public function test_validar_nit_rechaza_nit_corto(): void
    {
        $this->assertFalse($this->validator->validarNit('12345'));
    }

    public function test_validar_nit_rechaza_nit_muy_largo(): void
    {
        $this->assertFalse($this->validator->validarNit('123456789012'));
    }

    public function test_validar_nit_acepta_nit_valido(): void
    {
        // Using a 6-11 digit NIT
        $this->assertTrue($this->validator->validarNit('123456'));
    }

    public function test_validar_cedula_rechaza_cedula_con_longitud_incorrecta(): void
    {
        $this->assertFalse($this->validator->validarCedula('123456789'));
    }

    public function test_validar_cedula_rechaza_cedula_con_primer_digito_invalido(): void
    {
        $this->assertFalse($this->validator->validarCedula('0123456789'));
    }

    public function test_validar_referencia_tributaria_rechaza_referencia_invalida(): void
    {
        $this->assertFalse($this->validator->validarReferenciaTributaria('inv-123'));
    }

    public function test_validar_referencia_tributaria_acepta_referencia_valida(): void
    {
        $this->assertTrue($this->validator->validarReferenciaTributaria('INV-2026-001'));
    }

    public function test_validar_codigo_producto_rechaza_codigo_vacio(): void
    {
        $this->assertFalse($this->validator->validarCodigoProducto(''));
    }

    public function test_validar_codigo_producto_acepta_codigo_valido(): void
    {
        $this->assertTrue($this->validator->validarCodigoProducto('PROD-123'));
    }

    public function test_validar_unidad_medida_acepta_unidades_validas(): void
    {
        $this->assertTrue($this->validator->validarUnidadMedida('unidad'));
        $this->assertTrue($this->validator->validarUnidadMedida('kilogramo'));
        $this->assertTrue($this->validator->validarUnidadMedida('litro'));
    }

    public function test_validar_unidad_medida_rechaza_unidad_invalida(): void
    {
        $this->assertFalse($this->validator->validarUnidadMedida('tonelada'));
    }

    public function test_validar_porcentaje_iva_acepta_porcentajes_validos_colombianos(): void
    {
        $this->assertTrue($this->validator->validarPorcentajeIVA(0));
        $this->assertTrue($this->validator->validarPorcentajeIVA(5));
        $this->assertTrue($this->validator->validarPorcentajeIVA(19));
    }

    public function test_validar_porcentaje_iva_rechaza_porcentajes_invalidos(): void
    {
        $this->assertFalse($this->validator->validarPorcentajeIVA(10));
        $this->assertFalse($this->validator->validarPorcentajeIVA(25));
    }
}
