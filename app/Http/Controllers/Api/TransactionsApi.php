<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\PrivilegeController;
use App\Models\Patients;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TransactionsApi extends Controller
{
    private $privilegeController;

    public function __construct()
    {
        $this->privilegeController = new PrivilegeController();
    }

    public function list(Request $request)
    {
        try {
            $dataPerPage = $request->input("limit");
            $page = $request->input("page") + 1;
            $patientName = $request->has("patient_name") ? $request->input("patient_name") : null;
            $offset = ($page === 1) ? 0 : ($page * $dataPerPage) - $dataPerPage;
            $startDate = $request->has("startDate") ? $request->input("startDate") : null;
            $endDate = $request->has("endDate") ? $request->input("endDate") : null;

            $model = Transaction::query()
                ->when($patientName, function ($query) use ($patientName) {
                    return $query->whereHas('patient', function ($query) use ($patientName) {
                        $query->where('name', 'ILIKE', '%' . $patientName . '%');
                    });
                })
                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                    $query->where('created_at', '>=', Carbon::parse($startDate)->toIso8601String())
                        ->where('created_at', '<=', Carbon::parse($endDate)->toIso8601String());
                });
            $count = $model->count();
            $model = $model->limit($dataPerPage)
                ->offset($offset);
            $model = $model->orderBy('created_at', 'desc');
            $data = $model->get();

            foreach ($data as $k => $v) {
                $patientName = null;
                if ($v->patient_id) {
                    $patientName = Patients::select('name')->find($v->patient_id)->name;
                }
                $v->patient_name = $patientName;
            }

            return response()->json([
                'status'        => true,
                'data'          => $data,
                'summary'       => $this->getSummary($startDate, $endDate),
                'count'         => $count,
                'pagination'    => [
                    'offset'    => $offset,
                    'rowCount'  => $dataPerPage,
                    'page'      => $page,
                    'pageCount' => ceil($count / $dataPerPage)
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => env('APP_ENV') === 'production' ? 'Unexpected error. Please check log.' : $e->getMessage()
            ], 500);
        }
    }

    private function getSummary($startDate, $endDate): array
    {
        $summary = [
            "cash"      => (int) 0,
            "transfer"  => (int) 0,
            "debit"     => (int) 0,
            "cc"        => (int) 0,
            "change"    => (int) 0
        ];

        $model = Transaction::select("created_at", "payment_type", "total_amount", "payment_details", "change")
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->where('source', '!=', 'ONLINE')
            ->get();

        foreach ($model as $trx) {
            $summary["change"] = $summary["change"] + $trx->change;
            if ($trx->payment_details) {
                foreach ($trx->payment_details as $key => $value) {
                    if ($value["payment-with"] === "CASH") {
                        $summary["cash"] = $summary["cash"] + $this->convertRawInt($value["payment-amount"]);
                    } else if ($value["payment-with"] === "DEBIT_CARD") {
                        $summary["debit"] = $summary["debit"] + $this->convertRawInt($value["payment-amount"]);
                    } else if ($value["payment-with"] === "CREDIT_CARD") {
                        $summary["cc"] = $summary["cc"] + $this->convertRawInt($value["payment-amount"]);
                    } else if ($value["payment-with"] === "BANK_TRANSFER") {
                        $summary["transfer"] = $summary["transfer"] + $this->convertRawInt($value["payment-amount"]);
                    }
                }
            } else {
                if ($trx->payment_type === "Cash") {
                    $summary["cash"] = $summary["cash"] + $this->convertRawInt($trx->total_amount);
                } else if ($trx->payment_type === "Debit Card") {
                    $summary["debit"] = $summary["debit"] + $this->convertRawInt($trx->total_amount);
                } else if ($trx->payment_type === "Credit Card") {
                    $summary["cc"] = $summary["cc"] + $this->convertRawInt($trx->total_amount);
                } else if ($trx->payment_type === "Transfer Bank") {
                    $summary["transfer"] = $summary["transfer"] + $this->convertRawInt($trx->total_amount);
                }
            }
        }

        return $summary;
    }

    private function convertRawInt($value): int
    {
        $numericString = preg_replace('/[^0-9]/', '', $value);
        return intval($numericString);
    }
}
