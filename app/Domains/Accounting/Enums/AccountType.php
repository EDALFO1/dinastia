<?php

namespace App\Domains\Accounting\Enums;

enum AccountType: string
{
    case ACTIVO = 'activo';
    case PASIVO = 'pasivo';
    case PATRIMONIO = 'patrimonio';
    case INGRESOS = 'ingresos';
    case GASTOS = 'gastos';
    case COSTO = 'costo';
    case ELIMINACION = 'eliminacion';

    public function label(): string
    {
        return match($this) {
            self::ACTIVO => 'Activo',
            self::PASIVO => 'Pasivo',
            self::PATRIMONIO => 'Patrimonio',
            self::INGRESOS => 'Ingresos',
            self::GASTOS => 'Gastos',
            self::COSTO => 'Costo de Venta',
            self::ELIMINACION => 'Eliminación',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::ACTIVO => 'Bienes y derechos de la empresa',
            self::PASIVO => 'Obligaciones y deudas de la empresa',
            self::PATRIMONIO => 'Capital y reservas del propietario',
            self::INGRESOS => 'Ingresos ordinarios y extraordinarios',
            self::GASTOS => 'Gastos ordinarios y extraordinarios',
            self::COSTO => 'Costos asociados a la venta de productos',
            self::ELIMINACION => 'Cuentas de consolidación',
        };
    }

    public function isBalance(): bool
    {
        return in_array($this, [self::ACTIVO, self::PASIVO, self::PATRIMONIO]);
    }

    public function isIncome(): bool
    {
        return in_array($this, [self::INGRESOS, self::GASTOS, self::COSTO]);
    }

    public static function balance(): array
    {
        return [self::ACTIVO, self::PASIVO, self::PATRIMONIO];
    }

    public static function incomeStatement(): array
    {
        return [self::INGRESOS, self::GASTOS, self::COSTO];
    }
}
