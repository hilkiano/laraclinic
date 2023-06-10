<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PrivilegeController;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\Api\OnlineTransaction\FindPatientRequest;
use App\Http\Requests\Api\OnlineTransaction\MakeTransactionRequest;
use App\Models\Patients;

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
                ->when($request->has("record_no"), function ($query) use ($request) {
                    $query->whereHas('medicalRecords', function ($subquery) use ($request) {
                        $subquery->where("record_no", $request->input("record_no"));
                    });
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
