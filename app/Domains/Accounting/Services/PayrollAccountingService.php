<?php

namespace App\Domains\Accounting\Services;

use App\Domains\Accounting\Models\ChartOfAccounts;
use App\Domains\Accounting\Models\JournalEntry;
use App\Domains\Payroll\Models\NominaElectronica;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayrollAccountingService
{
    /**
     * Crear asientos automáticos cuando nómina es aceptada
     * Asientos:
     * - Débito Gastos de Nómina / Crédito Pasivos Laborales (salario a pagar)
     * - Débito Gastos de Aportes / Crédito Pasivos de Aportes
     */
    public function createPayrollAcceptedEntry(NominaElectronica $nomina): ?JournalEntry
    {
        return DB::transaction(function () use ($nomina) {
            if ($nomina->estado !== 'aceptada') {
                Log::warning('Intento de crear asiento para nómina no aceptada', [
                    'nomina_id' => $nomina->id,
                    'estado' => $nomina->estado,
                ]);
                return null;
            }

            // Obtener cuentas contables
            $cuentaGastosNomina = $this->findOrCreatePayrollExpenseAccount($nomina->empresa_id);
            $cuentaPasivosLaborales = $this->findOrCreatePayrollLiabilityAccount($nomina->empresa_id);
            $cuentaAportes = $this->findOrCreatePayrollContributionAccount($nomina->empresa_id);

            if (!$cuentaGastosNomina || !$cuentaPasivosLaborales) {
                Log::error('No se encontraron cuentas contables para nómina', [
                    'nomina_id' => $nomina->id,
                ]);
                return null;
            }

            // Crear asiento consolidado
            $asiento = JournalEntry::create([
                'empresa_id' => $nomina->empresa_id,
                'numero_asiento' => JournalEntry::generateNumber($nomina->empresa_id),
                'fecha' => $nomina->fecha_emision,
                'descripcion' => "Nómina #{$nomina->numero_nomina} - Período {$nomina->periodo_pago_inicio->format('m/Y')}",
                'referencia_documento' => $nomina->numero_nomina,
                'tipo_documento' => 'nomina',
                'usuario_creacion_id' => auth()->id() ?? 1,
                'estado' => 'borrador',
            ]);

            // Línea 1: Débito Gastos de Nómina
            $salarioBruto = (float) $nomina->total_devengado;
            $asiento->lines()->create([
                'empresa_id' => $nomina->empresa_id,
                'account_id' => $cuentaGastosNomina->id,
                'descripcion' => "Gasto nómina período {$nomina->periodo_pago_inicio->format('m/Y')}",
                'tipo_movimiento' => 'debito',
                'valor' => $salarioBruto,
            ]);

            // Línea 2: Crédito Pasivos Laborales (neto a pagar)
            $netoPagar = (float) $nomina->neto_pagar;
            $asiento->lines()->create([
                'empresa_id' => $nomina->empresa_id,
                'account_id' => $cuentaPasivosLaborales->id,
                'descripcion' => "Salarios por pagar",
                'tipo_movimiento' => 'credito',
                'valor' => $netoPagar,
            ]);

            // Línea 3: Crédito Aportes (si existe la cuenta)
            $totalAportes = $salarioBruto - $netoPagar;
            if ($totalAportes > 0 && $cuentaAportes) {
                $asiento->lines()->create([
                    'empresa_id' => $nomina->empresa_id,
                    'account_id' => $cuentaAportes->id,
                    'descripcion' => "Aportes por pagar (AFP, EPS, ARL, Caja)",
                    'tipo_movimiento' => 'credito',
                    'valor' => $totalAportes,
                ]);
            }

            // Aprobar automáticamente
            try {
                $asiento->approve(1);
            } catch (\Exception $e) {
                Log::error('Error aprobando asiento de nómina', [
                    'asiento_id' => $asiento->id,
                    'error' => $e->getMessage(),
                ]);
            }

            Log::info('Asiento contable creado automáticamente desde nómina', [
                'nomina_id' => $nomina->id,
                'asiento_id' => $asiento->id,
                'numero_asiento' => $asiento->numero_asiento,
                'salario_bruto' => $salarioBruto,
                'neto_pagar' => $netoPagar,
            ]);

            return $asiento;
        });
    }

    /**
     * Crear asientos para pagos de aportes
     * Débito Pasivo de Aportes / Crédito Bancos
     */
    public function createAportesPaymentEntry(int $empresaId, float $monto, string $referencia): JournalEntry
    {
        return DB::transaction(function () use ($empresaId, $monto, $referencia) {
            $cuentaAportes = $this->findOrCreatePayrollContributionAccount($empresaId);
            $cuentaBancos = $this->findOrCreateBankAccount($empresaId);

            $asiento = JournalEntry::create([
                'empresa_id' => $empresaId,
                'numero_asiento' => JournalEntry::generateNumber($empresaId),
                'fecha' => now(),
                'descripcion' => "Pago de aportes - {$referencia}",
                'referencia_documento' => $referencia,
                'tipo_documento' => 'aportes_pago',
                'usuario_creacion_id' => auth()->id() ?? 1,
            ]);

            // Débito Pasivo, Crédito Banco
            $asiento->lines()->create([
                'empresa_id' => $empresaId,
                'account_id' => $cuentaAportes->id,
                'descripcion' => 'Cancelación de aportes',
                'tipo_movimiento' => 'debito',
                'valor' => $monto,
            ]);

            $asiento->lines()->create([
                'empresa_id' => $empresaId,
                'account_id' => $cuentaBancos->id,
                'descripcion' => 'Pago efectuado',
                'tipo_movimiento' => 'credito',
                'valor' => $monto,
            ]);

            $asiento->approve(1);

            Log::info('Asiento de pago de aportes creado', [
                'asiento_id' => $asiento->id,
                'monto' => $monto,
            ]);

            return $asiento;
        });
    }

    // Helpers
    private function findOrCreatePayrollExpenseAccount(int $empresaId): ?ChartOfAccounts
    {
        $cuenta = ChartOfAccounts::where('empresa_id', $empresaId)
            ->where('codigo', '510101')
            ->first();

        if ($cuenta) {
            return $cuenta;
        }

        return ChartOfAccounts::create([
            'empresa_id' => $empresaId,
            'codigo' => '510101',
            'nombre' => 'Salarios y Jornales',
            'tipo_cuenta' => 'gastos',
            'nivel' => 3,
            'permite_movimiento' => true,
            'estado' => 'activo',
        ]);
    }

    private function findOrCreatePayrollLiabilityAccount(int $empresaId): ?ChartOfAccounts
    {
        $cuenta = ChartOfAccounts::where('empresa_id', $empresaId)
            ->where('codigo', '260101')
            ->first();

        if ($cuenta) {
            return $cuenta;
        }

        return ChartOfAccounts::create([
            'empresa_id' => $empresaId,
            'codigo' => '260101',
            'nombre' => 'Salarios por Pagar',
            'tipo_cuenta' => 'pasivo',
            'nivel' => 3,
            'permite_movimiento' => true,
            'estado' => 'activo',
        ]);
    }

    private function findOrCreatePayrollContributionAccount(int $empresaId): ?ChartOfAccounts
    {
        $cuenta = ChartOfAccounts::where('empresa_id', $empresaId)
            ->where('codigo', '260502')
            ->first();

        if ($cuenta) {
            return $cuenta;
        }

        return ChartOfAccounts::create([
            'empresa_id' => $empresaId,
            'codigo' => '260502',
            'nombre' => 'Aportes por Pagar',
            'tipo_cuenta' => 'pasivo',
            'nivel' => 3,
            'permite_movimiento' => true,
            'estado' => 'activo',
        ]);
    }

    private function findOrCreateBankAccount(int $empresaId): ?ChartOfAccounts
    {
        $cuenta = ChartOfAccounts::where('empresa_id', $empresaId)
            ->where('codigo', '100501')
            ->first();

        if ($cuenta) {
            return $cuenta;
        }

        return ChartOfAccounts::create([
            'empresa_id' => $empresaId,
            'codigo' => '100501',
            'nombre' => 'Banco - Cuenta Corriente',
            'tipo_cuenta' => 'activo',
            'nivel' => 3,
            'permite_movimiento' => true,
            'estado' => 'activo',
        ]);
    }
}
