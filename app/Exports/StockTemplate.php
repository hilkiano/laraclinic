<?php

namespace App\Exports;

use App\Models\Medicine;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Border;

class StockTemplate implements FromArray, WithEvents, WithHeadings, WithTitle, WithColumnWidths
{
    protected $limit = 2001;
    protected $selects;

    public function __construct()
    {
        $medicines = Medicine::select("label")->orderBy("label", "asc")->get()->toArray();
        $this->selects = [
            [
                "columns_name" => "A",
                "options" => array_column($medicines, "label")
            ]
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $workSheet = $event->sheet->getDelegate();
                $workSheet->freezePane('A2');
                $workSheet->getStyle('A1:B1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => [
                            'argb' => Color::COLOR_YELLOW
                        ]
                    ]
                ]);

                $workSheet->setShowGridlines(false);
                $workSheet->getStyle("A1:B{$this->limit}")->applyFromArray([
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_THICK,
                            'color' => [
                                'argb' => Color::COLOR_RED
                            ],
                        ],
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_HAIR,
                            'color' => [
                                'argb' => Color::COLOR_BLACK
                            ],
                        ]
                    ]
                ]);

                $hiddenSheet = $event->sheet->getDelegate()->getParent()->createSheet();
                $hiddenSheet->setTitle('HiddenBatch');
                $hiddenSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);

                foreach ($this->selects as $select) {
                    $dropColumn = $select['columns_name'];
                    $options = $select['options'];

                    // Set data validation formula to refer to hidden sheet cells
                    $validation = $event->sheet->getCell("{$dropColumn}2")->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                    $validation->setAllowBlank(false);
                    $validation->setShowInputMessage(true);
                    $validation->setShowErrorMessage(true);
                    $validation->setShowDropDown(true);
                    $validation->setErrorTitle('Input error');
                    $validation->setError('Value is not in list.');
                    $validation->setPromptTitle('Pick from list');
                    $validation->setPrompt('Please pick a value from the drop-down list.');

                    // Populate hidden sheet with dropdown values
                    foreach ($options as $index => $option) {
                        $cellCoordinate = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(1) . ($index + 1);
                        $hiddenSheet->setCellValue($cellCoordinate, $option);
                    }
                    $validation->setFormula1('HiddenBatch!$A$1:$A$' . count($options));

                    // Clone validation to remaining rows
                    for ($i = 3; $i <= $this->limit; $i++) {
                        $event->sheet->getCell("{$dropColumn}{$i}")->setDataValidation(clone $validation);
                    }
                }
            }
        ];
    }

    public function headings(): array
    {
        return [
            'Medicine',
            'Base Quantity'
        ];
    }

    public function array(): array
    {
        return [];
    }

    public function title(): string
    {
        return 'Stock Registration';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 100,
            'B' => 20
        ];
    }
}
