<?php

namespace App\Domains\Payroll\Enums;

enum TipoAporte: string
{
    case AFP = 'afp';
    case EPS = 'eps';
    case ARL = 'arl';
    case CAJA_COMPENSACION = 'caja_compensacion';
    case ICBF = 'icbf';
    case SENA = 'sena';
    case SALUD_COMPLEMENTARIA = 'salud_complementaria';

    public function label(): string
    {
        return match ($this) {
            self::AFP => 'Fondo de Pensiones (AFP)',
            self::EPS => 'Entidad Promotora de Salud (EPS)',
            self::ARL => 'Aseguradora de Riesgos Laborales (ARL)',
            self::CAJA_COMPENSACION => 'Caja de Compensación',
            self::ICBF => 'Instituto Colombiano de Bienestar Familiar (ICBF)',
            self::SENA => 'Servicio Nacional de Aprendizaje (SENA)',
            self::SALUD_COMPLEMENTARIA => 'Salud Complementaria',
        };
    }

    public function porcentajeEmpleador(): float
    {
        return match ($this) {
            self::AFP => 12.0, // Contribución del empleador
            self::EPS => 8.5,
            self::ARL => 0.52, // Varía según riesgo
            self::CAJA_COMPENSACION => 4.0,
            self::ICBF => 3.0,
            self::SENA => 2.0,
            self::SALUD_COMPLEMENTARIA => 0.0,
        };
    }

    public function porcentajeEmpleado(): float
    {
        return match ($this) {
            self::AFP => 4.0,
            self::EPS => 4.0,
            self::ARL => 0.0,
            self::CAJA_COMPENSACION => 0.0,
            self::ICBF => 0.0,
            self::SENA => 0.0,
            self::SALUD_COMPLEMENTARIA => 0.0,
        };
    }
}
