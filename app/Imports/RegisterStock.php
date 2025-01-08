<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithStartRow;

class RegisterStock implements WithStartRow, SkipsEmptyRows
{
    use Importable;

    public function startRow(): int
    {
        return 2;
    }
}
