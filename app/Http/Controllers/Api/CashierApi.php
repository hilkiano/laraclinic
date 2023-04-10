<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointments;
use App\Models\AppointmentsDetail;
use App\Events\AssignmentTaken;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CashierApi extends Controller
{
    private $userData;

    public function __construct()
    {
        $this->userData = auth()->user();
    }

    public function progress(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'uuid'      => 'required',
                'method'    => 'required'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status'    => false,
                    'message'   => $validator->errors()
                ], 422);
            }

            $appointment = Appointments::where('uuid', $request->input('uuid'))->firstOrFail();
            if ($request->input('method') === 'cancel') {
                $del = AppointmentsDetail::where('appointment_uuid', $request->input('uuid'))
                    ->where('status', $appointment->status)
                    ->delete();
                if ($del) {
                    $appointment->status = config('constants.status.payment_waiting');
                    $appointment->save();
                }

                AssignmentTaken::dispatch();

                return response()->json([
                    'status'    => true,
                    'message'   => 'Assignment rolled back.'
                ], 200);
            } elseif ($request->input('method') === 'submit') {

                // Create new transaction row, print recipe and mark assignment as completed

                return response()->json([
                    'status'    => true,
                    'message'   => 'Assignment completed.'
                ], 200);
            }

            return response()->json([
                'status'    => false,
                'message'   => 'No transaction.'
            ], 400);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => env('APP_ENV') === 'production' ? 'Unexpected error. Please check log.' : $e->getMessage()
            ], 500);
        }
    }
}
