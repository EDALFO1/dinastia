<?php

namespace App\Domains\Payroll\Enums;

enum TipoRetencion: string
{
    case RENTA = 'renta';
    case SALUD = 'salud';
    case SOLIDARIDAD = 'solidaridad';
    case EDUCACION = 'educacion';
    case VOLUNTARIA = 'voluntaria';
    case EMBARGO = 'embargo';

    public function label(): string
    {
        return match ($this) {
            self::RENTA => 'Retención en la Fuente (Renta)',
            self::SALUD => 'Retención Salud',
            self::SOLIDARIDAD => 'Aporte de Solidaridad',
            self::EDUCACION => 'Fondo de Educación',
            self::VOLUNTARIA => 'Deducción Voluntaria',
            self::EMBARGO => 'Embargo',
        };
    }

    /**
     * Determine if this is a percentage-based retention
     */
    public function isPercentageBased(): bool
    {
        return match ($this) {
            self::RENTA => true,
            self::SALUD => true,
            self::SOLIDARIDAD => true,
            self::EDUCACION => false,
            self::VOLUNTARIA => false,
            self::EMBARGO => false,
        };
    }
}
