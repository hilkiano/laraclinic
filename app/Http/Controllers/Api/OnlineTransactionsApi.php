<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PrivilegeController;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\Api\OnlineTransaction\FindPatientRequest;
use App\Http\Requests\Api\OnlineTransaction\MakeTransactionRequest;
use App\Models\Medicine;
use App\Models\Patients;
use App\Models\Prescription;
use App\Models\StockHistory;
use App\Models\Transaction;

class OnlineTransactionsApi extends Controller
{
    private $privilegeController;

    public function __construct()
    {
        $this->privilegeController = new PrivilegeController();
    }

    /**
     * Find a patient based on the given search criteria.
     *
     * @param FindPatientRequest $request The request object containing the search criteria.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the search results.
     */
    public function findPatient(FindPatientRequest $request)
    {
        try {
            if (empty($request->all())) {
                return response()->json([
                    'status'    => false,
                    'message'   => 'Please add at least one query before searching.'
                ], 400);
            }

            $patient = Patients::with(['medicalRecords', 'patientPotrait', 'prescriptions', 'prescriptions.medicalRecord'])
                ->when($request->has("name"), function ($query) use ($request) {
                    $query->where("name", "ILIKE", "%" . $request->input("name") . "%");
                })
                ->when($request->has("phone_number"), function ($query) use ($request) {
                    $query->where("phone_number", "ILIKE", "%" . $request->input("phone_number") . "%");
                })
                ->when($request->has("address"), function ($query) use ($request) {
                    $query->where("address", "ILIKE", "%" . $request->input("address") . "%");
                })
                ->when($request->has("code"), function ($query) use ($request) {
                    $query->where("code", "ILIKE", "%" . $request->input("code") . "%");
                })
                ->limit(50) // limit results
                ->get();

            return response()->json([
                'status'    => true,
                'data'      => $patient
            ], 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => env('APP_ENV') === 'production' ? 'Unexpected error. Please check log.' : $e->getMessage()
            ], 500);
        }
    }

    public function makeTransaction(MakeTransactionRequest $request)
    {
        try {
            // New Transaction
            $trx = new Transaction();
            $trx->patient_id = $request->patient_id;
            $trx->prescription = json_decode($request->prescription);
            $trx->additional_info = $request->notes;
            $trx->total_amount = 0;
            $trx->payment_type = "BANK_TRANSFER";
            $trx->payment_amount = 0;
            $trx->change = 0;
            $trx->discount_type = "pctg";
            $trx->discount_amount = 0;
            $trx->source = 'ONLINE';
            $trx->save();

            // New Prescription
            $rx = new Prescription();
            $rx->patient_id = $request->patient_id;
            $rx->list = json_decode($request->prescription);
            $rx->additional_info = $request->notes;
            $rx->source = 'ONLINE';
            $rx->transaction_id = $trx->id;
            $rx->save();

            // Update stock history
            $prescription = json_decode($request->prescription);
            foreach ($prescription as $item) {
                $med = Medicine::select("id")->with("stocks")->where("sku", $item->sku)->first();
                if ($med) {
                    if (count($med->stocks) > 0) {
                        $latestStock = $med->stocks->first();

                        $history = new StockHistory();
                        $history->stock_id = $latestStock->id;
                        $history->type = "OUT";
                        $history->quantity = $item->qty;
                        $history->transaction_id = $trx->id;

                        $history->save();
                    }
                }
            }

            return response()->json([
                'status'    => true,
                'message'   => 'Transaction made successfully.'
            ], 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => env('APP_ENV') === 'production' ? 'Unexpected error. Please check log.' : $e->getMessage()
            ], 500);
        }
    }
}
