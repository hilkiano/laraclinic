<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class RegisterStock implements SkipsEmptyRows, WithMultipleSheets
{
    use Importable;

    public function sheets(): array
    {
        return [
            "Stock Registration" => new StockImport(),
        ];
    }

    public function onUnknownSheet($sheetName)
    {
        // E.g. you can log that a sheet was not found.
        info("Sheet {$sheetName} was skipped");
    }
}
