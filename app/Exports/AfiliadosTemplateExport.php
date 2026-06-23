<?php

namespace App\Exports;

use App\Models\EmpresaLaboral;
use App\Models\Asesor;
use App\Models\Documento;
use App\Models\SubtipoCotizante;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AfiliadosTemplateExport
{
    private int $empresaId;

    public function __construct(int $empresaId)
    {
        $this->empresaId = $empresaId;
    }

    public function build(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Afiliados');

        // --- Cabeceras ---
        $headers = [
            'A' => 'empresa_laboral',
            'B' => 'asesor',
            'C' => 'tipo_documento',
            'D' => 'subtipo_cotizante',
            'E' => 'numero_documento',
            'F' => 'primer_nombre',
            'G' => 'segundo_nombre',
            'H' => 'primer_apellido',
            'I' => 'segundo_apellido',
            'J' => 'fecha_nacimiento',
            'K' => 'sexo',
            'L' => 'correo',
            'M' => 'telefono',
            'N' => 'direccion',
            'O' => 'ciudad',
        ];

        foreach ($headers as $col => $label) {
            $sheet->setCellValue($col . '1', $label);
        }

        $sheet->getStyle('A1:O1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']],
        ]);

        // --- Cargar listas desde BD ---
        $empresasLaborales = EmpresaLaboral::withoutGlobalScopes()
            ->where('empresa_id', $this->empresaId)
            ->orderBy('nombre')->pluck('nombre')->toArray();

        $asesores = Asesor::withoutGlobalScopes()
            ->where('empresa_id', $this->empresaId)
            ->orderBy('nombre')->pluck('nombre')->toArray();

        $documentos = Documento::orderBy('nombre')->pluck('nombre')->toArray();
        $subtipos   = SubtipoCotizante::orderBy('nombre')->pluck('nombre')->toArray();
        $sexo       = ['M', 'F', 'Otro'];

        // --- Fila de ejemplo con primer valor real de cada lista ---
        $sheet->setCellValue('A2', $empresasLaborales[0] ?? '');
        $sheet->setCellValue('B2', $asesores[0]          ?? '');
        $sheet->setCellValue('C2', $documentos[0]        ?? '');
        $sheet->setCellValue('D2', $subtipos[0]          ?? '');
        $sheet->setCellValue('E2', '123456789');
        $sheet->setCellValue('F2', 'Carlos');
        $sheet->setCellValue('G2', 'Andrés');
        $sheet->setCellValue('H2', 'García');
        $sheet->setCellValue('I2', 'López');
        $sheet->setCellValue('J2', '1990-05-15');
        $sheet->setCellValue('K2', 'M');
        $sheet->setCellValue('L2', 'correo@demo.com');
        $sheet->setCellValue('M2', '3001234567');
        $sheet->setCellValue('N2', 'Calle 123 #45-67');
        $sheet->setCellValue('O2', 'Cali');

        // --- Hoja oculta "Listas" con los datos para los dropdowns ---
        $listas = $spreadsheet->createSheet();
        $listas->setTitle('Listas');

        $fillCol = function (string $col, array $data) use ($listas) {
            foreach ($data as $i => $val) {
                $listas->setCellValue($col . ($i + 1), $val);
            }
        };

        $fillCol('A', $empresasLaborales);
        $fillCol('B', $asesores);
        $fillCol('C', $documentos);
        $fillCol('D', $subtipos);
        $fillCol('E', $sexo);

        $listas->setSheetState(Worksheet::SHEETSTATE_HIDDEN);

        // --- Dropdowns en hoja principal ---
        // IMPORTANTE: setShowDropDown(true) = mostrar flecha (PhpSpreadsheet invierte la lógica)
        $dropdowns = [];
        if (!empty($empresasLaborales)) $dropdowns['A'] = '=Listas!$A$1:$A$' . count($empresasLaborales);
        if (!empty($asesores))          $dropdowns['B'] = '=Listas!$B$1:$B$' . count($asesores);
        if (!empty($documentos))        $dropdowns['C'] = '=Listas!$C$1:$C$' . count($documentos);
        if (!empty($subtipos))          $dropdowns['D'] = '=Listas!$D$1:$D$' . count($subtipos);
        $dropdowns['K'] = '=Listas!$E$1:$E$' . count($sexo);

        foreach ($dropdowns as $col => $formula) {
            for ($row = 2; $row <= 500; $row++) {
                $v = $sheet->getCell($col . $row)->getDataValidation();
                $v->setType(DataValidation::TYPE_LIST);
                $v->setAllowBlank(true);
                $v->setShowDropDown(true); // true = mostrar flecha (PhpSpreadsheet invierte vs OOXML)
                $v->setFormula1($formula);
            }
        }

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }
}
