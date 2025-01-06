<?php

namespace App\Http\Controllers\Api;

use App\Exports\TransactionsExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\PrivilegeController;
use App\Models\Patients;
use App\Models\Transaction;
use App\Models\Users;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

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

        DB::enableQueryLog();
        $model = Transaction::select("created_at", "payment_type", "total_amount", "payment_details", "change")
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->where('source', '!=', 'ONLINE')
            ->get();

        Log::info(DB::getQueryLog());

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

    public function getReport(Request $request)
    {
        try {
            $startDate = $request->has("startDate") ? $request->input("startDate") : null;
            $endDate = $request->has("endDate") ? $request->input("endDate") : null;

            $query = Transaction::query()
                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                    $query->where('created_at', '>=', Carbon::parse($startDate)->toIso8601String())
                        ->where('created_at', '<=', Carbon::parse($endDate)->toIso8601String());
                })
                ->orderBy('created_at', 'asc')
                ->get();

            $data = $this->getDataArray($query);
            $summary = $this->getSummary($startDate, $endDate);

            $formattedStart = Carbon::parse($startDate)->setTimezone(env('APP_TIME_ZONE'))->isoFormat("DD-MM-YYYY_HH:mm:ss");
            $formattedEnd = Carbon::parse($endDate)->setTimezone(env('APP_TIME_ZONE'))->isoFormat("DD-MM-YYYY_HH:mm:ss");

            return Excel::download(
                new TransactionsExport($data, $summary),
                "TrxDetailReport_{$formattedStart}_{$formattedEnd}.xlsx",
                \Maatwebsite\Excel\Excel::XLSX
            );
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => env('APP_ENV') === 'production' ? 'Unexpected error. Please check log.' : $e->getMessage()
            ], 500);
        }
    }

    private function getDataArray($collections)
    {
        $transactions = [];
        $meds = [];

        foreach ($collections as $collection) {
            $creator = Users::find($collection->created_by);
            $patient = Patients::find($collection->patient_id);
            if (array_key_exists("data", $collection->prescription[0])) {
                foreach ($collection->prescription[0]["data"] as $prescription) {
                    array_push($transactions, [
                        "date" => $collection->created_at,
                        "id" => (string) $collection->id,
                        "patient" => $patient ? trim($patient->name) : "Guest",
                        "sku" => $prescription["sku"],
                        "meds" => $prescription["label"],
                        "price" => $this->getSubtotal($prescription)["price"],
                        "qty" => $this->getSubtotal($prescription)["qty"],
                        "discount" => $this->getSubtotal($prescription)["discount"],
                        "subtotal" => $this->getSubtotal($prescription)["subtotal"],
                        "payment_with" => $this->getPaymentType($collection),
                        "total_amount" => (int)preg_replace('/[^0-9]/', '', $collection->total_amount),
                        "payment_amount" => (int)$collection->payment_amount,
                        "change" => (int)$collection->change,
                        "discount_type" => $collection->discount_type === "pctg" ? "Percentage" : "Amount",
                        "discount_amount" => $collection->discount_amount,
                        "source" => $collection->source,
                        "additional_info" => $collection->additional_info,
                        "created_by" => $creator->username,
                        "npwp" => $creator->npwp ? (string) $creator->npwp : ""
                    ]);

                    if (array_key_exists($prescription["label"], $meds)) {
                        $meds[$prescription["label"]]["qty"] = $meds[$prescription["label"]]["qty"] + $this->getSubtotal($prescription)["qty"];
                    } else {
                        $meds[$prescription["label"]]["sku"] = $prescription["sku"];
                        $meds[$prescription["label"]]["label"] = $prescription["label"];
                        $meds[$prescription["label"]]["qty"] = $this->getSubtotal($prescription)["qty"];
                    }
                }
            } else {
                foreach ($collection->prescription as $prescription) {
                    array_push($transactions, [
                        "date" => $collection->created_at,
                        "id" => (string) $collection->id,
                        "patient" => $patient ? trim($patient->name) : "Guest",
                        "sku" => $prescription["sku"],
                        "meds" => $prescription["label"],
                        "price" => $this->getSubtotal($prescription)["price"],
                        "qty" => $this->getSubtotal($prescription)["qty"],
                        "discount" => $this->getSubtotal($prescription)["discount"],
                        "subtotal" => $this->getSubtotal($prescription)["subtotal"],
                        "payment_with" => $this->getPaymentType($collection),
                        "total_amount" => (int)preg_replace('/[^0-9]/', '', $collection->total_amount),
                        "payment_amount" => (int)$collection->payment_amount,
                        "change" => (int)$collection->change,
                        "discount_type" => $collection->discount_type === "pctg" ? "Percentage" : "Amount",
                        "discount_amount" => $collection->discount_amount,
                        "source" => $collection->source,
                        "additional_info" => $collection->additional_info,
                        "created_by" => $creator->username,
                        "npwp" => $creator->npwp ? (string) $creator->npwp : ""
                    ]);

                    if (array_key_exists($prescription["label"], $meds)) {
                        $meds[$prescription["label"]]["qty"] = $meds[$prescription["label"]]["qty"] + $this->getSubtotal($prescription)["qty"];
                    } else {
                        $meds[$prescription["label"]]["sku"] = $prescription["sku"];
                        $meds[$prescription["label"]]["label"] = $prescription["label"];
                        $meds[$prescription["label"]]["qty"] = $this->getSubtotal($prescription)["qty"];
                    }
                }
            }
        }

        $formattedMeds = [];
        foreach ($meds as $med) {
            array_push($formattedMeds, [
                "sku" => $med["sku"],
                "med" => $med["label"],
                "qty" => $med["qty"]
            ]);
        }

        return [
            "transactions" => $transactions,
            "meds" => $formattedMeds
        ];
    }

    private function getSubtotal($prescription)
    {
        $price = array_key_exists("price", $prescription) ? (int) $prescription["price"] : 0;
        $qty = array_key_exists("qty", $prescription) ? (int) $prescription["qty"] : 0;

        if (array_key_exists("discount_type", $prescription) && array_key_exists("discount_value", $prescription)) {
            if ($prescription["discount_value"] > 0) {
                if ($prescription["discount_type"] === "pctg") {
                    $discountAmount = $prescription["price"] * ($prescription["discount_value"] / 100);
                } else if ($prescription["discount_type"] === "amt") {
                    $discountAmount = $prescription["discount_value"];
                }

                $discount = intval($discountAmount);
            } else {
                $discount = 0;
            }
        } else {
            $discount = 0;
        }

        return [
            "price" => $price,
            "qty" => $qty,
            "discount" => $discount,
            "subtotal" => $price * $qty - $discount
        ];
    }

    private function getPaymentType($transaction)
    {
        if ($transaction->payment_details) {
            $detail = [];
            foreach ($transaction->payment_details as $payment) {
                if ($payment["payment-with"] === "CASH") {
                    $paymentWith = "Cash";
                } else if ($payment["payment-with"] === "DEBIT_CARD") {
                    $paymentWith = "Debit Card";
                } else if ($payment["payment-with"] === "CREDIT_CARD") {
                    $paymentWith = "Credit Card";
                } else if ($payment["payment-with"] === "BANK_TRANSFER") {
                    $paymentWith = "Bank Transfer";
                }
                array_push($detail, $paymentWith);
            }

            return implode(", ", $detail);
        } else {
            return $transaction->payment_type;
        }
    }
}
