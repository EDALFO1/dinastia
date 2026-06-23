<?php

namespace App\Imports;

use App\Models\Afiliado;
use App\Models\Afiliacion;
use App\Models\Eps;
use App\Models\Arl;
use App\Models\Pension;
use App\Models\Caja;
use App\Models\ParametroAnual;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;

class AfiliacionesImport implements
    ToModel,
    WithHeadingRow,
    SkipsEmptyRows
{
    protected $empresaId;
    public $duplicados = [];
    public $errores    = [];

    public function __construct($empresaId)
    {
        $this->empresaId = $empresaId;
    }

    public function model(array $row)
    {
        $row = collect($row)->mapWithKeys(function ($value, $key) {
            $key = strtolower(trim($key));
            $key = str_replace([' ', '-'], '_', $key);
            return [$key => $value];
        })->toArray();

        $filaVacia = collect($row)
            ->filter(fn($v) => !is_null($v) && trim((string)$v) !== '')
            ->isEmpty();

        if ($filaVacia) return null;

        $doc = trim($row['numero_documento'] ?? '');
        if ($doc === '') return null;

        $erroresFila = [];

        // Buscar afiliado
        $afiliado = Afiliado::where('empresa_id', $this->empresaId)
            ->where('numero_documento', $doc)
            ->first();

        if (!$afiliado) {
            $this->errores[] = "Documento {$doc}: afiliado no encontrado en esta empresa.";
            return null;
        }

        if ((int)$afiliado->estado !== 1) {
            $this->errores[] = "Documento {$doc}: el afiliado está inactivo.";
            return null;
        }

        // Verificar afiliación activa existente
        if (Afiliacion::where('afiliado_id', $afiliado->id)->where('estado', 1)->exists()) {
            $this->duplicados[] = $doc;
            return null;
        }

        // Resolver EPS
        $eps = Eps::whereRaw('LOWER(TRIM(nombre)) = ?', [strtolower(trim($row['eps'] ?? ''))])->first();
        if (!$eps) $erroresFila[] = "EPS «{$row['eps']}» no encontrada";

        // Resolver ARL — formato "Nombre N1" o solo "Nombre"
        $arlRaw   = trim($row['arl'] ?? '');
        $arlNivel = null;
        if (preg_match('/^(.+?)\s+N(\d)$/i', $arlRaw, $m)) {
            $arlNombre = trim($m[1]);
            $arlNivel  = (int)$m[2];
        } else {
            $arlNombre = $arlRaw;
        }
        $arlQuery = Arl::whereRaw('LOWER(TRIM(nombre)) = ?', [strtolower($arlNombre)]);
        if ($arlNivel !== null) {
            $arlQuery->where('nivel', $arlNivel);
        }
        $arl = $arlQuery->first();
        if (!$arl) $erroresFila[] = "ARL «{$arlRaw}» no encontrada";

        // Resolver Pensión
        $pension = Pension::whereRaw('LOWER(TRIM(nombre)) = ?', [strtolower(trim($row['pension'] ?? ''))])->first();
        if (!$pension) $erroresFila[] = "Pensión «{$row['pension']}» no encontrada";

        // Resolver Caja
        $caja = Caja::whereRaw('LOWER(TRIM(nombre)) = ?', [strtolower(trim($row['caja'] ?? ''))])->first();
        if (!$caja) $erroresFila[] = "Caja «{$row['caja']}» no encontrada";

        // Fecha afiliación
        $fechaAfil = $row['fecha_afiliacion'] ?? null;
        if (is_numeric($fechaAfil)) {
            $fechaAfil = Date::excelToDateTimeObject($fechaAfil)->format('Y-m-d');
        } else {
            $fechaAfil = trim((string)$fechaAfil);
        }
        if (!$fechaAfil) $erroresFila[] = "Fecha de afiliación requerida";

        // Fecha retiro (opcional)
        $fechaRetiro = $row['fecha_retiro'] ?? null;
        if (is_numeric($fechaRetiro) && $fechaRetiro > 0) {
            $fechaRetiro = Date::excelToDateTimeObject($fechaRetiro)->format('Y-m-d');
        } else {
            $fechaRetiro = trim((string)($fechaRetiro ?? '')) ?: null;
        }

        // tipo_ibc
        $tipoIbc = strtoupper(trim($row['tipo_ibc'] ?? 'SMMLV'));
        if (!in_array($tipoIbc, ['SMMLV', 'FIJO'])) {
            $erroresFila[] = "tipo_ibc debe ser SMMLV o FIJO";
        }

        if (!empty($erroresFila)) {
            $this->errores[] = "Documento {$doc}: " . implode(', ', $erroresFila);
            return null;
        }

        // Calcular IBC
        $anio      = Carbon::parse($fechaAfil)->year;
        $parametro = ParametroAnual::where('anio', $anio)->first();
        if (!$parametro) {
            $this->errores[] = "Documento {$doc}: no hay parámetros para el año {$anio}";
            return null;
        }

        $ibc = $tipoIbc === 'SMMLV'
            ? $parametro->salario_minimo
            : (float)($row['ibc'] ?? 0);

        return Afiliacion::create([
            'empresa_id'       => $this->empresaId,
            'afiliado_id'      => $afiliado->id,
            'eps_id'           => $eps->id,
            'arl_id'           => $arl->id,
            'pension_id'       => $pension->id,
            'caja_id'          => $caja->id,
            'nivel_arl'        => $arl->nivel,
            'tipo_ibc'         => $tipoIbc,
            'ibc'              => $ibc,
            'fecha_afiliacion' => $fechaAfil,
            'fecha_retiro'     => $fechaRetiro,
            'estado'           => 1,
        ]);
    }
}
