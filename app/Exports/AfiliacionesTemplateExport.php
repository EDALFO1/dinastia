<?php

namespace App\Exports;

use App\Models\Afiliado;
use App\Models\Eps;
use App\Models\Arl;
use App\Models\Pension;
use App\Models\Caja;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use Maatwebsite\Excel\Concerns\FromArray;

class AfiliacionesTemplateExport implements WithHeadings, WithEvents, FromArray
{
    public function headings(): array
    {
        return [
            'numero_documento',
            'eps',
            'arl',
            'pension',
            'caja',
            'fecha_afiliacion',
            'fecha_retiro',
            'tipo_ibc',
            'ibc',
        ];
    }

    public function array(): array
    {
        return [
            [
                '123456789',
                'Nueva EPS',
                'Sura N1',
                'Porvenir',
                'Compensar',
                '2026-01-01',
                '',
                'SMMLV',
                '',
            ]
        ];
    }

    public function registerEvents(): array
    {
        return [
            \Maatwebsite\Excel\Events\AfterSheet::class => function ($event) {

                $spreadsheet = $event->sheet->getDelegate()->getParent();
                $sheet       = $event->sheet->getDelegate();

                $empresaId = session('empresa_id');

                $afiliados = Afiliado::where('empresa_id', $empresaId)
                    ->get()
                    ->map(fn($a) => $a->numero_documento . ' - ' . $a->primer_nombre . ' ' . $a->primer_apellido)
                    ->toArray();

                $epsList     = Eps::orderBy('nombre')->pluck('nombre')->toArray();
                $arlList     = Arl::orderBy('nombre')->orderBy('nivel')
                    ->get()
                    ->map(fn($a) => $a->nombre . ' N' . $a->nivel)
                    ->toArray();
                $pensionList = Pension::orderBy('nombre')->pluck('nombre')->toArray();
                $cajaList    = Caja::orderBy('nombre')->pluck('nombre')->toArray();
                $tipoIbc     = ['SMMLV', 'FIJO'];

                // Hoja oculta con las listas
                $listasSheet = new Worksheet($spreadsheet, 'Listas');
                $spreadsheet->addSheet($listasSheet);

                $fillColumn = function ($col, $data) use ($listasSheet) {
                    foreach ($data as $i => $value) {
                        $listasSheet->setCellValue($col . ($i + 1), $value);
                    }
                };

                $fillColumn('A', $afiliados);
                $fillColumn('B', $epsList);
                $fillColumn('C', $arlList);
                $fillColumn('D', $pensionList);
                $fillColumn('E', $cajaList);
                $fillColumn('F', $tipoIbc);

                $listasSheet->setSheetState(Worksheet::SHEETSTATE_HIDDEN);

                $setDropdown = function ($column, $range) use ($sheet) {
                    for ($row = 2; $row <= 500; $row++) {
                        $validation = $sheet->getCell($column . $row)->getDataValidation();
                        $validation->setType(DataValidation::TYPE_LIST);
                        $validation->setAllowBlank(true);
                        $validation->setShowDropDown(true); // true = mostrar flecha (PhpSpreadsheet invierte vs OOXML)
                        $validation->setFormula1($range);
                    }
                };

                if (!empty($epsList))     $setDropdown('B', '=Listas!$B$1:$B$' . count($epsList));
                if (!empty($arlList))     $setDropdown('C', '=Listas!$C$1:$C$' . count($arlList));
                if (!empty($pensionList)) $setDropdown('D', '=Listas!$D$1:$D$' . count($pensionList));
                if (!empty($cajaList))    $setDropdown('E', '=Listas!$E$1:$E$' . count($cajaList));
                $setDropdown('H', '=Listas!$F$1:$F$' . count($tipoIbc));
            }
        ];
    }
}
