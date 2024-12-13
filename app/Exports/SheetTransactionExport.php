<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\DefaultValueBinder;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Border;

class SheetTransactionExport extends DefaultValueBinder implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithCustomValueBinder, WithEvents, WithCustomStartCell
{
    protected $data;
    protected $summary;
    protected $count;

    public function __construct($data = [], $summary)
    {
        $this->data = $data;
        $this->count = count($data) + 1;

        $this->summary = $summary;
    }

    public function headings(): array
    {
        return [
            "Date",
            "ID",
            "Patient",
            "SKU",
            "Medicine/Service",
            "Price/Qty",
            "Quantity",
            "Discount/Qty",
            "Subtotal",
            "Payment With",
            "Total Amount",
            "Payment Amount",
            "Change",
            "Discount Type",
            "Discount Amount",
            "Source",
            "Notes",
            "Created By",
            "NPWP"
        ];
    }

    public function startCell(): string
    {
        return 'A8';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $workSheet = $event->sheet->getDelegate();
                $workSheet->freezePane('A9');

                // SUMMARY
                $workSheet->mergeCells("A1:B1");
                $workSheet->setCellValue("A1", 'Summary');
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

                $workSheet->setCellValue("A2", 'Cash');
                $workSheet->setCellValue("A3", 'Bank Transfer');
                $workSheet->setCellValue("A4", 'Debit Card');
                $workSheet->setCellValue("A5", 'Credit Card');
                $workSheet->setCellValue("A6", 'Change');
                $workSheet->setCellValue("B2", $this->summary["cash"]);
                $workSheet->setCellValue("B3", $this->summary["transfer"]);
                $workSheet->setCellValue("B4", $this->summary["debit"]);
                $workSheet->setCellValue("B5", $this->summary["cc"]);
                $workSheet->setCellValue("B6", $this->summary["change"] * -1);
                $workSheet->getStyle("A1:B6")->applyFromArray([
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
                $workSheet->getStyle("B2:B6")->applyFromArray([
                    'font' => [
                        'bold' => true
                    ]
                ]);
                $workSheet->getStyle("A6:B6")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => [
                            'argb' => "f8d7da"
                        ]
                    ]
                ]);

                // DATA
                $workSheet->getStyle('A8:S8')->applyFromArray([
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

                $endRows = $this->count + 7;
                $workSheet->getStyle("A8:S{$endRows}")->applyFromArray([
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

                // Loop through each row and apply conditional formatting
                for ($row = 9; $row <= $this->count + 7; $row++) {
                    $trxType = $workSheet->getCell('P' . $row)->getValue();
                    $patient = $workSheet->getCell('C' . $row)->getValue();

                    if ($patient === "Guest") {
                        $workSheet->getStyle('C' . $row)->applyFromArray([
                            'font' => [
                                'italic' => true
                            ]
                        ]);
                    }

                    if ($trxType === "ONLINE") {
                        $workSheet->getStyle('A' . $row . ':S' . $row)->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'color' => [
                                    'argb' => "fff3cd"
                                ]
                            ]
                        ]);
                    } else if ($trxType === "APPOINTMENT") {
                        $workSheet->getStyle('A' . $row . ':S' . $row)->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'color' => [
                                    'argb' => "cfe2ff"
                                ]
                            ]
                        ]);
                    } else if ($trxType === "SELF") {
                        $workSheet->getStyle('A' . $row . ':S' . $row)->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'color' => [
                                    'argb' => "f8d7da"
                                ]
                            ]
                        ]);
                    }
                }
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

    public function title(): string
    {
        return "Transactions";
    }

    public function array(): array
    {
        return $this->data;
    }
}
