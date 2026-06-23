<?php

namespace App\Imports;

use App\Models\Afiliado;
use App\Models\EmpresaLaboral;
use App\Models\Asesor;
use App\Models\Documento;
use App\Models\SubtipoCotizante;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class AfiliadosImport implements 
    ToModel, 
    WithHeadingRow,
    SkipsEmptyRows
{
    protected $empresaId;
    public $duplicados = [];
    public $errores = [];

    public function __construct($empresaId)
    {
        $this->empresaId = $empresaId;
    }

    public function model(array $row)
    {
        // 🔥 NORMALIZAR HEADERS
        $row = collect($row)->mapWithKeys(function ($value, $key) {
            $key = strtolower(trim($key));
            $key = str_replace([' ', '-'], '_', $key);
            return [$key => $value];
        })->toArray();

        // 🔥 FILA VACÍA REAL
        $filaVacia = collect($row)
            ->filter(fn($value) => !is_null($value) && trim($value) !== '')
            ->isEmpty();

        if ($filaVacia) return null;

        // 🔥 NÚMERO DE DOCUMENTO
        $numero = trim($row['numero_documento'] ?? '');

        // 🔥 ignorar filas vacías
        if ($numero === '') {
            return null;
        }

        $erroresFila = [];

        // ✅ documento
        if (!preg_match('/^[0-9]+$/', $numero)) {
            $erroresFila[] = "Documento inválido";
        }

        // ✅ nombre
        if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/', $row['primer_nombre'] ?? '')) {
            $erroresFila[] = "Nombre inválido";
        }

        // ✅ apellido
        if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/', $row['primer_apellido'] ?? '')) {
            $erroresFila[] = "Apellido inválido";
        }

        // ✅ teléfono
        if (!empty($row['telefono']) && !preg_match('/^[0-9]+$/', $row['telefono'])) {
            $erroresFila[] = "Teléfono inválido";
        }

        // 🔥 SI HAY ERRORES → GUARDAR TODOS
        if (!empty($erroresFila)) {
            $this->errores[] = "Documento {$numero}: " . implode(', ', $erroresFila);
            return null;
        }

        // 🔥 DUPLICADOS
        if (Afiliado::where('empresa_id', $this->empresaId)
            ->where('numero_documento', $numero)
            ->exists()) {

            $this->duplicados[] = $numero;
            return null;
        }

        // 🔥 RELACIONES
        $empresaLaboral = EmpresaLaboral::where('empresa_id', $this->empresaId)
            ->whereRaw('LOWER(TRIM(nombre)) = ?', [strtolower(trim($row['empresa_laboral'] ?? ''))])
            ->first();

        $asesor = null;
        if (!empty($row['asesor'])) {
            $asesor = Asesor::where('empresa_id', $this->empresaId)
                ->whereRaw('LOWER(TRIM(nombre)) = ?', [strtolower(trim($row['asesor']))])
                ->first();
        }

        $documento = Documento::where(function($q) use ($row) {
            $tipo = strtolower(trim($row['tipo_documento'] ?? ''));
            $q->whereRaw('LOWER(TRIM(nombre)) = ?', [$tipo])
              ->orWhereRaw('LOWER(TRIM(codigo)) = ?', [$tipo]);
        })->first();

        $subtipo = SubtipoCotizante::whereRaw(
            'LOWER(TRIM(nombre)) = ?', 
            [strtolower(trim($row['subtipo_cotizante'] ?? ''))]
        )->first();

        if (!$empresaLaboral || !$documento || !$subtipo) {
            $this->errores[] = "Relaciones inválidas en documento {$numero}";
            return null;
        }

        // 🔥 FECHA
        $fecha = $row['fecha_nacimiento'] ?? null;
        if (is_numeric($fecha)) {
            $fecha = Date::excelToDateTimeObject($fecha)->format('Y-m-d');
        }

        // 🔥 CREAR AFILIADO
        return Afiliado::create([
            'empresa_id' => $this->empresaId,
            'empresa_laboral_id' => $empresaLaboral->id,
            'asesor_id' => $asesor?->id,
            'documento_id' => $documento->id,
            'subtipo_cotizante_id' => $subtipo->id,

            'numero_documento' => $numero,
            'primer_nombre' => trim($row['primer_nombre']),
            'segundo_nombre' => trim($row['segundo_nombre'] ?? ''),
            'primer_apellido' => trim($row['primer_apellido']),
            'segundo_apellido' => trim($row['segundo_apellido'] ?? ''),

            'fecha_nacimiento' => $fecha,
            'sexo' => trim($row['sexo'] ?? ''),

            'correo' => trim($row['correo'] ?? ''),
            'telefono' => trim($row['telefono'] ?? ''),
            'direccion' => trim($row['direccion'] ?? ''),
            'ciudad' => trim($row['ciudad'] ?? ''),

            'estado' => true,
        ]);
    }
}