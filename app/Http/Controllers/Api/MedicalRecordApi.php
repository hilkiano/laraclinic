<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MedicalRecordApi extends Controller
{
    private $userData;

    public function __construct()
    {
        $this->userData = auth()->user();
    }

    /**
     * Retrieves a Prescription object from the database based on a given ID
     * @param int $id The ID of the Prescription object to retrieve
     * @return \Illuminate\Http\JsonResponse Returns a JSON response containing the Prescription object's data if the retrieval is successful
     * or a JSON response indicating that an error occurred if the retrieval failed.
     * @throws \Exception If an unexpected error occurs during the retrieval process
     */
    public function getPrescription(int $id)
    {
        try {
            return response()->json([
                'status'    => true,
                'message'   => 'Prescription copied.',
                'data'      => Prescription::select('list')->find($id)->list
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
