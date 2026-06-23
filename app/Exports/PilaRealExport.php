<?php

namespace App\Exports;

use App\Models\Empresa;
use App\Models\Recibo;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PilaRealExport
{
    protected $empresaId;
protected $periodo;
protected $recibos;

public function __construct($empresaId, $periodo, $recibos = null)
{
    $this->empresaId = $empresaId;
    $this->periodo = $periodo;
    $this->recibos = $recibos;
}

    public function exportar($rutaCustom = null)
    {
        // =====================================================
        // CARGAR PLANTILLA ORIGINAL
        // =====================================================

        $spreadsheet = IOFactory::load(
            storage_path('app/templates/libro1.xlsx')
        );

        $sheet = $spreadsheet->getSheetByName('Liquidaciones');

        // =====================================================
        // EMPRESA
        // =====================================================

        $empresa = Empresa::findOrFail(
            $this->empresaId
        );

        // =====================================================
        // FECHA
        // =====================================================

        $dt = Carbon::createFromFormat(
            'Y-m',
            $this->periodo
        );

        // =====================================================
        // ENCABEZADO
        // =====================================================

        $sheet->setCellValue(
            'K1',
            strtoupper($empresa->nombre)
        );

        $sheet->setCellValue(
            'K2',
            'NIT ' .
            (
                $empresa->nit ??
                $empresa->numero_documento
            )
        );

        $sheet->setCellValue(
            'K3',
            'SUCURSAL PRINCIPAL: PRINCIPAL'
        );

        $sheet->setCellValue(
            'K4',
            'TIPO EMPLEADOR: EMPRESA'
        );

        $sheet->setCellValue(
            'K5',
            'PERFIL: NOMINA/TESORERIA'
        );

        $sheet->setCellValue(
            'K6',
            'ÚLTIMO ACCESO: ' .
            now()->format('Y/m/d H:i:s')
        );

        // =====================================================
        // PERIODOS
        // =====================================================

        // PENSION
        $sheet->setCellValue(
            'A10',
            $dt->copy()->subMonth()->format('Y-m')
        );

        // SALUD
        $sheet->setCellValue(
            'C10',
            $dt->format('Y-m')
        );

        // TIPO PLANILLA
        $sheet->setCellValue(
            'D10',
            'E'
        );

        // CODIGO SUCURSAL
        $sheet->setCellValue(
            'G10',
            '1'
        );

        // TIPO APORTANTE
        $sheet->setCellValue(
            'J10',
            'EMPLEADOR'
        );

        // =====================================================
        // RECIBOS
        // =====================================================

        if ($this->recibos) {

    $recibos = $this->recibos;

} else {

    $recibos = Recibo::with([

        'afiliado',
        'afiliado.documento',
        'afiliado.subtipoCotizante',

        'afiliado.afiliaciones.eps',
        'afiliado.afiliaciones.arl',
        'afiliado.afiliaciones.pension',
        'afiliado.afiliaciones.caja',

    ])
        ->where('empresa_id', $this->empresaId)
        ->whereYear('fecha', $dt->year)
        ->whereMonth('fecha', $dt->month)
        ->orderBy('id')
        ->get();
}
        // =====================================================
        // FILA INICIAL
        // =====================================================

        $fila = 19;
        $contador = 1;

        foreach ($recibos as $r) {

            $a = $r->afiliado;

            if (!$a) {
                continue;
            }

            $afiliacion = $a->afiliaciones
                ->where('estado', 1)
                ->first();

            if (!$afiliacion) {
                continue;
            }

            // =====================================================
            // TARIFAS
            // =====================================================

            $tarifaPension =
                (float) (
                    $afiliacion->pension?->porcentaje ?? 16
                );

            $tarifaSalud =
                (float) (
                    $afiliacion->eps?->porcentaje ?? 4
                );

            $tarifaArl =
                (float) (
                    $afiliacion->arl?->porcentaje ?? 0.522
                );

            $tarifaCaja =
                (float) (
                    $afiliacion->caja?->porcentaje ?? 4
                );

            // =====================================================
            // IBC
            // =====================================================

            $ibc = (float) $r->ibc;

            // =====================================================
            // IBC CAJA
            // =====================================================

            $ibcCaja =
                str_contains(
                    strtoupper(
                        $afiliacion->caja?->nombre ?? ''
                    ),
                    'COMFIAR'
                )
                ? 100
                : $ibc;

            // =====================================================
            // DIAS
            // =====================================================

            $dias = (int) ($r->dias_liquidar ?? 30);

            if ($dias <= 0) {
                $dias = 30;
            }

            $horas = $dias * 8;

            // =====================================================
            // FACTOR PROPORCIONAL
            // =====================================================

            $factorDias = $dias / 30;

            // =====================================================
            // IBC PROPORCIONAL
            // =====================================================

        $ibcProporcional =
    ceil(
        (
            $ibc * $factorDias
        ) / 100
    ) * 100;

$ibcCajaProporcional =
    ceil(
        (
            $ibcCaja * $factorDias
        ) / 100
    ) * 100;

            // =====================================================
            // VALIDAR PENSION
            // =====================================================

            $tienePension =
                $afiliacion->pension
                &&
                strtoupper(
                    trim(
                        $afiliacion->pension->nombre ?? ''
                    )
                ) !== 'NINGUNA';

            // =====================================================
            // CALCULOS
            // =====================================================

            $valorPension = 0;

            if ($tienePension) {

                $valorPension =
                    round(
                        $ibcProporcional *
                        ($tarifaPension / 100)
                    );
            }

            $valorSalud =
                round(
                    $ibcProporcional *
                    ($tarifaSalud / 100)
                );

            $valorArl =
                round(
                    $ibcProporcional *
                    ($tarifaArl / 100)
                );

            $valorCaja =
                round(
                    $ibcCajaProporcional *
                    ($tarifaCaja / 100)
                );

            // =====================================================
            // DATOS BASICOS
            // =====================================================

            $sheet->setCellValue(
                "A{$fila}",
                $contador
            );

            $sheet->setCellValue(
                "B{$fila}",
                strtoupper(
                    $a->documento->nombre ?? 'CC'
                )
            );

            $sheet->setCellValueExplicit(
                "C{$fila}",
                $a->numero_documento,
                DataType::TYPE_STRING
            );

            $sheet->setCellValue(
                "D{$fila}",
                strtoupper(
                    $a->primer_apellido
                )
            );

            $sheet->setCellValue(
                "E{$fila}",
                strtoupper(
                    $a->segundo_apellido
                )
            );

            $sheet->setCellValue(
                "F{$fila}",
                strtoupper(
                    $a->primer_nombre
                )
            );

            $sheet->setCellValue(
                "G{$fila}",
                strtoupper(
                    $a->segundo_nombre
                )
            );

            $sheet->setCellValue(
                "H{$fila}",
                'VALLE'
            );

            $sheet->setCellValue(
                "I{$fila}",
                strtoupper(
                    $a->ciudad ?? 'CALI'
                )
            );

            $sheet->setCellValue(
                "J{$fila}",
                '1. DEPENDIENTE'
            );

            $subtipo = 'NINGUNO';

            if ($a->subtipoCotizante) {

                $subtipo = strtoupper(
                    trim(
                        $a->subtipoCotizante->nombre ?? 'NINGUNO'
                    )
                );
            }

            $sheet->setCellValue(
                "K{$fila}",
                $subtipo
            );

            // =====================================================
            // DIAS Y HORAS
            // =====================================================

            $sheet->setCellValue(
                "L{$fila}",
                $horas
            );

            $sheet->setCellValue(
                "AQ{$fila}",
                $dias
            );

            $sheet->setCellValue(
                "M{$fila}",
                'NO'
            );

            $sheet->setCellValue(
                "N{$fila}",
                'NO'
            );

            // =====================================================
            // NOVEDADES
            // =====================================================

            $sheet->setCellValue("P{$fila}", 'NO');
            $sheet->setCellValue("Q{$fila}", '');

            $sheet->setCellValue("R{$fila}", 'NO');
            $sheet->setCellValue("S{$fila}", '');

            $sheet->setCellValue("T{$fila}", 'NO');
            $sheet->setCellValue("U{$fila}", 'NO');
            $sheet->setCellValue("V{$fila}", 'NO');
            $sheet->setCellValue("W{$fila}", 'NO');
            $sheet->setCellValue("X{$fila}", 'NO');

            $sheet->setCellValue("Z{$fila}", 'NO');
            $sheet->setCellValue("AA{$fila}", 'NO');

            $sheet->setCellValue("AD{$fila}", 'NO');
            $sheet->setCellValue("AG{$fila}", 'NO');

            $sheet->setCellValue("AJ{$fila}", 'NO');
            $sheet->setCellValue("AM{$fila}", 'NO');

            $sheet->setCellValue("AN{$fila}", 'NO');
            $sheet->setCellValue("AQ{$fila}", '0');
            $sheet->setCellValue("AT{$fila}", 'NO');

// =====================================================
// INGRESO AUTOMATICO SEGUN FECHA AFILIACION
// =====================================================

$fechaAfiliacion =
    Carbon::parse(
        $afiliacion->fecha_afiliacion
    );

// periodo liquidado
$periodoLiquidado =
    $dt->copy()->subMonth();

// si ingreso en el mes liquidado
if (

    $fechaAfiliacion->year
    === $periodoLiquidado->year

    &&

    $fechaAfiliacion->month
    === $periodoLiquidado->month

) {

    $sheet->setCellValue(
        "P{$fila}",
        'Todos los sistemas (ARL, AFP, CCF, EPS)'
    );

    $sheet->setCellValue(
        "Q{$fila}",
        $fechaAfiliacion->format('Y-m-d')
    );
}

            // =====================================================
            // RETIRO
            // =====================================================

            if (
    strtoupper(
        trim($r->novedad ?? '')
    ) === 'RETIRO'
    &&
    $r->fecha_retiro
)
            {

                $fechaRetiro =
                    Carbon::parse($r->fecha_retiro);

                $sheet->setCellValue(
                    "R{$fila}",
                    'Todos los sistemas (ARL, AFP, CCF, EPS)'
                );

                $sheet->setCellValue(
                    "S{$fila}",
                    $fechaRetiro->format('Y-m-d')
                );
            }

            // =====================================================
            // CAMPOS INTERNOS PILA
            // =====================================================

            $sheet->setCellValue("AV{$fila}", 'NO');
            $sheet->setCellValue("AW{$fila}", 'NO');

            $sheet->setCellValue("BC{$fila}", 'Sin Riesgo');

            $sheet->setCellValue("BJ{$fila}", 'NINGUNA');

            $sheet->setCellValue("BP{$fila}", 0);

            $sheet->setCellValue("BU{$fila}", 'NINGUNA');

            $sheet->setCellValue("CJ{$fila}", 0);
            $sheet->setCellValue("CL{$fila}", 0);
            $sheet->setCellValue("CN{$fila}", 0);
            $sheet->setCellValue("CP{$fila}", 0);

            $sheet->setCellValue("CS{$fila}", 'CC');

            // =====================================================
            // SALARIO / IBC SALUD
            // =====================================================

            $sheet->setCellValue(
                "AU{$fila}",
                $ibcProporcional
            );

            // =====================================================
            // AFP
            // =====================================================

            if ($tienePension) {

                $sheet->setCellValue(
                    "AX{$fila}",
                    $afiliacion->pension?->nombre ?? ''
                );

                $sheet->setCellValue(
                    "AY{$fila}",
                    $dias
                );

                $sheet->setCellValue(
                    "AZ{$fila}",
                    $ibcProporcional
                );

                $sheet->setCellValue(
                    "BA{$fila}",
                    0.16
                );

                $sheet->setCellValue(
                    "BB{$fila}",
                    $valorPension
                );

            } else {

                $sheet->setCellValue(
                    "AX{$fila}",
                    'NINGUNA'
                );

                $sheet->setCellValue(
                    "AY{$fila}",
                    0
                );

                $sheet->setCellValue(
                    "AZ{$fila}",
                    0
                );

                $sheet->setCellValue(
                    "BA{$fila}",
                    0
                );

                $sheet->setCellValue(
                    "BB{$fila}",
                    0
                );
            }

            // =====================================================
            // EPS
            // =====================================================

            $sheet->setCellValue(
                "BK{$fila}",
                $afiliacion->eps?->nombre ?? ''
            );

            $sheet->setCellValue(
                "BL{$fila}",
                $dias
            );

            $sheet->setCellValue(
                "BM{$fila}",
                $ibcProporcional
            );

            $sheet->setCellValue(
                "BN{$fila}",
                ($tarifaSalud / 100)
            );

            $sheet->setCellValue(
                "BO{$fila}",
                $valorSalud
            );

            // =====================================================
            // ARL
            // =====================================================

            $sheet->setCellValue(
                "BV{$fila}",
                $afiliacion->arl?->nombre ?? ''
            );

            $sheet->setCellValue(
                "BW{$fila}",
                $dias
            );

            $sheet->setCellValue(
                "BX{$fila}",
                $ibcProporcional
            );

            $sheet->setCellValue(
                "BY{$fila}",
                ($tarifaArl / 100)
            );

            $sheet->setCellValue(
                "BZ{$fila}",
                $afiliacion->nivel_arl ?? 1
            );

            $sheet->setCellValue(
                "CA{$fila}",
                1
            );

            $sheet->setCellValue(
                "CB{$fila}",
                $afiliacion->arl?->actividad_economica ?? ''
            );

            $sheet->setCellValue(
                "CC{$fila}",
                $valorArl
            );

            // =====================================================
            // CAJA
            // =====================================================

            $sheet->setCellValue(
                "CD{$fila}",
                $dias
            );

            $sheet->setCellValue(
                "CE{$fila}",
                $afiliacion->caja?->nombre ?? ''
            );

            $sheet->setCellValue(
                "CF{$fila}",
                $ibcCajaProporcional
            );

            $sheet->setCellValue(
                "CG{$fila}",
                ($tarifaCaja / 100)
            );

            $sheet->setCellValue(
                "CH{$fila}",
                $valorCaja
            );

            // =====================================================
            // EXONERADO
            // =====================================================

            $sheet->setCellValue(
                "CR{$fila}",
                'SI'
            );

            // =====================================================
            // FORMATOS %
            // =====================================================

            $sheet->getStyle("BA{$fila}")
                ->getNumberFormat()
                ->setFormatCode('0.00%');

            $sheet->getStyle("BN{$fila}")
                ->getNumberFormat()
                ->setFormatCode('0.00%');

            $sheet->getStyle("BY{$fila}")
                ->getNumberFormat()
                ->setFormatCode('0.000%');

            $sheet->getStyle("CG{$fila}")
                ->getNumberFormat()
                ->setFormatCode('0.00%');

            $fila++;
            $contador++;
        }

        // =====================================================
        // GUARDAR
        // =====================================================

        if ($rutaCustom) {

    $ruta = $rutaCustom;

} else {

    $nombreArchivo =
        'Liquidaciones_' .
        $dt->format('m_Y') .
        '.xlsx';

    $ruta = storage_path(
        'app/temp/' . $nombreArchivo
    );
}

        if (!file_exists(storage_path('app/temp'))) {

            mkdir(
                storage_path('app/temp'),
                0777,
                true
            );
        }

        $writer = new Xlsx($spreadsheet);

        $writer->save($ruta);

        return $ruta;
    }
    
}