<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Border;

class SheetMedicineExport extends DefaultValueBinder implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithEvents
{
    protected $data;
    protected $count;

    public function __construct($data = [])
    {
        $this->data = $data;
        $this->count = count($data) + 1;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $workSheet = $event->sheet->getDelegate();
                $workSheet->freezePane('A2');

                // DATA
                $workSheet->getStyle('A1:C1')->applyFromArray([
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

                $workSheet->getStyle("A1:C{$this->count}")->applyFromArray([
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

                $workSheet->setShowGridlines(false);
            }
        ];
    }

    public function bindValue(Cell $cell, $value)
    {
        if (is_string($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        // else return default behavior
        return parent::bindValue($cell, $value);
    }

    public function headings(): array
    {
        return [
            "SKU",
            "Label",
            "Quantity"
        ];
    }

    public function title(): string
    {
        return "Items";
    }

    public function array(): array
    {
        return $this->data;
    }
}
