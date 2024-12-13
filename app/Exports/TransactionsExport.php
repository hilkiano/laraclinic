<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class TransactionsExport implements WithMultipleSheets
{
    protected $dataTransactions;
    protected $dataMeds;
    protected $summary;

    public function __construct($data, $summary)
    {
        $this->dataTransactions = $data ? $data["transactions"] : [];
        $this->dataMeds = $data ? $data["meds"] : [];
        $this->summary = $summary;
    }

    protected $tables = ["Transactions"];

    public function sheets(): array
    {
        return [
            new SheetTransactionExport($this->dataTransactions, $this->summary),
            new SheetMedicineExport($this->dataMeds)
        ];
    }
}
