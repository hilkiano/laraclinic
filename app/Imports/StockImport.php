<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithStartRow;

class StockImport implements WithStartRow, SkipsEmptyRows
{
    public function startRow(): int
    {
        return 2;
    }
}
