<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointments;
use App\Models\AppointmentsDetail;
use App\Events\AssignmentTaken;
use App\Events\PrintReceipt;
use App\Models\Transaction;
use App\Models\Prescription;
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
                $handleCheckout = $this->handleCheckout($request->all());

                if ($handleCheckout) {
                    return response()->json([
                        'status'    => true,
                        'message'   => 'Checkout succeed.'
                    ], 200);
                } else {
                    return response()->json([
                        'status'    => true,
                        'message'   => 'Checkout failed.'
                    ], 200);
                }
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

    public function checkout(Request $request)
    {
        try {
            // Parse JSON string
            $data = $request->input("data");
            $payment = $request->input("payment");
            if (gettype($data) === "string") {
                $data = json_decode($data);
            }
            if (gettype($payment) === "string") {
                $payment = json_decode($payment);
            }

            // Create new transaction
            $trx = new Transaction();
            $trx->patient_id = $request->has("patient") ? (int) $request->input("patient") : null;
            $trx->prescription = $data;
            $trx->payment_type = $payment->method;
            $trx->total_amount = $payment->total;
            $trx->payment_amount = $payment->amount;
            $trx->change = $payment->change;
            $trx->discount_type = $payment->discount_type;
            $trx->discount_amount = $payment->discount_value;
            $trx->source = "SELF";

            $trx->save();

            // If there is patient, make prescription with SELF source
            if ($request->has("patient")) {
                $rx = new Prescription();
                $rx->patient_id = (int) $request->input("patient");
                $rx->list = $data;
                $rx->source = "SELF";
                $rx->transaction_id = $trx->id;

                $rx->save();
            }

            // Dispatch print event
            PrintReceipt::dispatch($trx->toArray());

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

    private function handleCheckout(array $data)
    {
        try {
            // Find appointment by its uuid
            $appointment = Appointments::where('uuid', $data['uuid'])->first();

            // Create new transaction
            $trx = new Transaction();
            $trx->appointment_uuid = $data['uuid'];
            $trx->patient_id = $appointment->patient_id;
            $trx->prescription = json_decode($data['prescription']);
            $trx->payment_type = $data['payment_with'];
            $trx->total_amount = $data['total_amount'];
            $trx->payment_amount = $data['payment_amount'];
            $trx->change = $data['change'];
            $trx->discount_type = $data['total_discount_type'];
            $trx->discount_amount = $data['total_discount'];
            $trx->source = "APPOINTMENT";

            $trx->save();

            // Dispatch print event
            PrintReceipt::dispatch($trx->toArray());

            if ($trx) {
                $appointment->status = config('constants.status.completed');
                $appointment->save();

                // Add new detail
                $newDetail = new AppointmentsDetail();
                $newDetail->appointment_uuid = $data['uuid'];
                $newDetail->status = config('constants.status.completed');
                $newDetail->pic = auth()->id();
                $newDetail->save();

                // Update or create prescription
                $rx = Prescription::where('appointment_uuid', $data["uuid"])->first();

                if ($rx) {
                    $rx->transaction_id = $trx->id;
                    $rx->save();
                }
            }

            return true;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return false;
        }
    }
}
