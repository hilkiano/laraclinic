<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointments;
use App\Models\AppointmentsDetail;
use App\Models\MedicalRecord;
use App\Models\Medicine;
use App\Models\Prescription;
use App\Models\Services;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AppointmentApi extends Controller
{
    private $userData;

    public function __construct()
    {
        $this->userData = auth()->user();
    }

    /**
     * Get appointment details by uuid
     *
     * @param  string $uuid
     * @return \Illuminate\Http\Response
     */
    public function getDetail(string $uuid)
    {
        try {
            return response()->json([
                'status'    => true,
                'data'      => AppointmentsDetail::with(['createdBy' => function ($query) {
                    $query->select('id', 'name', 'username');
                }])
                    ->where('appointment_uuid', $uuid)
                    ->orderBy('created_at', 'asc')
                    ->get()
            ], 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => env('APP_ENV') === 'production' ? 'Unexpected error. Please check log.' : $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new detail of appointment
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function makeDetail(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'uuid'          => 'required',
                'status'        => 'required'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status'    => false,
                    'message'   => $validator->errors()
                ], 422);
            }

            $assignment = Appointments::where('uuid', $request->input('uuid'))->first();
            $assignment->status = $request->input('status');
            $assignment->save();

            $newDetail = AppointmentsDetail::create([
                'appointment_uuid'  => $assignment->uuid,
                'patient_id'        => $assignment->patient_id,
                'status'            => $request->input('status'),
                'additional_note'   => $request->has('additional_note') ? $request->input('additional_note') : null,
                'pic'               => $request->has('pic') ? $request->input('pic') : null
            ]);

            return response()->json([
                'status'    => true,
                'data'      => [
                    'assignment'    => $assignment,
                    'detail'        => $newDetail
                ],
                'message'   => 'Assignment updated.'
            ], 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => env('APP_ENV') === 'production' ? 'Unexpected error. Please check log.' : $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get list of appointment by user group
     *
     * @return \Illuminate\Http\Response
     */
    public function getMyAppointments()
    {
        try {
            $group = $this->userData->group_id;
            $model = Appointments::with(['patient', 'patient.patientPotrait'])
                ->whereDate('visit_time', Carbon::now()->toDateString())
                ->when($group === config('constants.group.doctor'), function ($query) {
                    $query->where('status', config('constants.status.doctor_waiting'));
                })
                ->when($group === config('constants.group.pharmacy'), function ($query) {
                    $query->where('status', config('constants.status.pharmacy_waiting'));
                })
                ->when($group === config('constants.group.cashier'), function ($query) {
                    $query->where('status', config('constants.status.payment_waiting'));
                })
                ->orderBy('daily_code', 'asc')
                ->limit(10);

            return response()->json([
                'status'    => true,
                'data'      => $model->get()
            ], 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => env('APP_ENV') === 'production' ? 'Unexpected error. Please check log.' : $e->getMessage()
            ], 500);
        }
    }

    public function take(Request $request)
    {
        try {
            $currentStatus = Appointments::select('status')->where('uuid', $request->uuid)->first()->status;
            if (!strpos($currentStatus, 'WAITING')) {
                return response()->json([
                    'status'    => false,
                    'message'   => 'This assignment already assigned. Please refresh the page.'
                ], 400);
            }

            $status = $request->status;
            if ($status === config("constants.status.doctor_waiting")) {
                $status = config("constants.status.doctor_assigned");
            } elseif ($status === config("constants.status.pharmacy_waiting")) {
                $status = config("constants.status.pharmacy_assigned");
            }
            $request->merge(["status" => $status, "pic" => auth()->id()]);
            $makeDetail = $this->makeDetail($request);

            if ($makeDetail->getStatusCode() === 200) {
                $model = Appointments::with(['patient', 'patient.patientPotrait', 'patient.medicalRecords'])
                    ->where('uuid', $request->uuid)
                    ->first();

                if ($model->patient->birth_date) {
                    $model->patient->birth_date = Carbon::make($model->patient->birth_date)->isoFormat("D MMMM YYYY");
                    $model->patient->age = Carbon::make($model->patient->birth_date)->age;
                }

                return response()->json([
                    'status'    => true,
                    'data'      => $model
                ], 200);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => env('APP_ENV') === 'production' ? 'Unexpected error. Please check log.' : $e->getMessage()
            ], 500);
        }
    }

    /**
     * Make/rollback progression in assignment
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
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
                    if ($appointment->status === config('constants.status.doctor_assigned')) {
                        $appointment->status = config('constants.status.doctor_waiting');
                    } elseif ($appointment->status === config('constants.status.pharmacy_assigned')) {
                        $appointment->status = config('constants.status.pharmacy_waiting');
                    }
                    $appointment->save();
                }

                return response()->json([
                    'status'    => true,
                    'message'   => 'Assignment rolled back.'
                ], 200);
            } elseif ($request->input('method') === 'submit') {
                $prescription = $request->has('prescription') ? json_decode($request->input('prescription')) : null;

                // Change daily code
                $appointment = Appointments::where('uuid', $request->input('uuid'))->first();
                $appointment->status = config('constants.status.pharmacy_waiting');
                $appointment->daily_code = $this->getDailyCode(config('constants.reason.pharmacy'), Carbon::now());
                $appointment->save();

                // Add new detail
                $newDetail = new AppointmentsDetail();
                $newDetail->appointment_uuid = $request->input('uuid');
                $newDetail->status = config('constants.status.pharmacy_waiting');
                $newDetail->pic = auth()->id();
                $newDetail->save();

                // Add prescription
                $newRx = new Prescription();
                $newRx->appointment_uuid = $request->input('uuid');
                $newRx->patient_id = $appointment->patient_id;
                $newRx->list = $prescription ? $prescription[0]->data : null;
                $newRx->save();

                // Add medical rec
                $newMedRecord = new MedicalRecord();
                $newMedRecord->appointment_uuid = $request->input('uuid');
                $newMedRecord->record_no = Str::uuid();
                $newMedRecord->patient_id = $appointment->patient_id;
                $newMedRecord->prescription_id = $newRx->id;
                $newMedRecord->additional_note = $request->has('medical_note') ? $request->input('medical_note') : null;
                $newMedRecord->save();

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

    public function getAssignation(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'pic'       => 'required',
                'status'    => 'required'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status'    => false,
                    'message'   => $validator->errors()
                ], 422);
            }
            $model = Appointments::with(['patient', 'detail', 'patient.patientPotrait', 'patient.medicalRecords' => function ($query) {
                $query->take(5);
            }])
                ->whereHas('detail', function ($query) use ($request) {
                    return $query->where('pic', $request->input('pic'));
                })
                ->where('status', $request->input("status"))
                ->first();

            if ($model) {
                if ($model->patient->birth_date) {
                    $model->patient->birth_date = Carbon::make($model->patient->birth_date)->isoFormat("D MMMM YYYY");
                    $model->patient->age = Carbon::make($model->patient->birth_date)->age;
                }
            }

            return response()->json([
                'status'    => true,
                'data'      => $model
            ], 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => env('APP_ENV') === 'production' ? 'Unexpected error. Please check log.' : $e->getMessage()
            ], 500);
        }
    }

    public function getItems($query)
    {
        try {
            $modelA = Medicine::select('sku', 'label')
                ->where('label', 'ILIKE', "%$query%");
            $modelB = Services::select('sku', 'label')
                ->union($modelA)
                ->where('label', 'ILIKE', "%$query%");

            $data = $modelB->orderBy('label', 'asc')->get();

            return response()->json([
                'status'    => true,
                'data'      => $data
            ], 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => env('APP_ENV') === 'production' ? 'Unexpected error. Please check log.' : $e->getMessage()
            ], 500);
        }
    }

    private function getDailyCode(string $reason, $visitTime): string
    {
        $count = Appointments::whereDate('visit_time', $visitTime->toDateString())
            ->where('visit_reason', $reason)
            ->count();

        $initial = $reason === config('constants.reason.doctor') ? "A" : "B";
        return $initial . str_pad($count + 1, 3, "0", STR_PAD_LEFT);
    }
}
