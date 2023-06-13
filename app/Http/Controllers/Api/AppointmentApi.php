<?php

namespace App\Http\Controllers\Api;

use App\Events\AssignmentCreated;
use App\Events\AssignmentTaken;
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
use Illuminate\Support\Arr;
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
     * Get appointments for the current user.
     *
     * @param Request $request The HTTP request object.
     * @return \Illuminate\Http\JsonResponse The HTTP response containing the appointments data.
     */
    public function getMyAppointments(Request $request)
    {
        try {
            // Get the name filter from the request or set it to null.
            $nameFilter = $request->has("name") ? $request->input("name") : null;

            // Get the user's group ID.
            $group = $this->userData->group_id;

            // Query the appointments table and eager-load the patient and patientPotrait relationships.
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
                // Add a filter based on the nameFilter parameter if it is set.
                ->when($nameFilter, function ($query) use ($nameFilter) {
                    $query->whereHas('patient', function ($subquery) use ($nameFilter) {
                        $subquery->where('name', 'ILIKE', "%$nameFilter%");
                    });
                })
                ->orderBy('visit_time', 'asc')
                ->limit(10);

            // Return a JSON response with the appointments data.
            return response()->json([
                'status'    => true,
                'data'      => $model->get()
            ], 200);
        } catch (\Exception $e) {
            // Log any errors and return an error response.
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
            } elseif ($status === config("constants.status.payment_waiting")) {
                $status = config("constants.status.payment_assigned");
            }
            $request->merge(["status" => $status, "pic" => auth()->id()]);
            $makeDetail = $this->makeDetail($request);

            if ($makeDetail->getStatusCode() === 200) {
                $model = Appointments::with(['patient', 'detail', 'patient.patientPotrait'])
                    ->when($request->status === config("constants.status.doctor_assigned"), function ($query) {
                        $query->with(['patient.medicalRecords' => function ($query) {
                            $query->take(5);
                        }]);
                    })
                    ->when($request->status === config("constants.status.pharmacy_assigned"), function ($query) {
                        $query->with(['prescription', 'medicalRecord', 'patient.prescriptions' => function ($query) {
                            $query->take(5);
                        }]);
                    })
                    ->where('uuid', $request->uuid)
                    ->first();

                if ($model->patient->birth_date) {
                    $model->patient->birth_date = Carbon::make($model->patient->birth_date)->isoFormat("D MMMM YYYY");
                    $model->patient->age = Carbon::make($model->patient->birth_date)->age;
                }

                AssignmentTaken::dispatch();

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

                AssignmentTaken::dispatch();

                return response()->json([
                    'status'    => true,
                    'message'   => 'Assignment rolled back.'
                ], 200);
            } elseif ($request->input('method') === 'submit') {
                $prescription = $request->has('prescription') ? json_decode($request->input('prescription')) : null;

                // Change status
                $newStatus = null;
                $appointment = Appointments::where('uuid', $request->input('uuid'))->first();
                if ($appointment->status === config('constants.status.doctor_assigned')) {
                    $newStatus = config('constants.status.pharmacy_waiting');
                } elseif ($appointment->status === config('constants.status.pharmacy_assigned')) {
                    $newStatus = config('constants.status.payment_waiting');
                }

                $appointment->status = $newStatus;
                $appointment->save();

                // Add new detail
                $newDetail = new AppointmentsDetail();
                $newDetail->appointment_uuid = $request->input('uuid');
                $newDetail->status = $newStatus;
                $newDetail->pic = auth()->id();
                $newDetail->save();

                // Update or create prescription
                $rx = Prescription::where('appointment_uuid', $request->input('uuid'))->first();
                if ($rx) {
                    $rx->list = $prescription ? $prescription[0]->data : null;
                    $rx->save();
                } else {
                    $rx = new Prescription();
                    $rx->appointment_uuid = $request->input('uuid');
                    $rx->patient_id = $appointment->patient_id;
                    $rx->list = $prescription ? $prescription[0]->data : null;
                    $rx->source = "DOCTOR";

                    $rx->save();
                }

                // Create medical record if the assignment through doctor
                if ($newStatus === config('constants.status.pharmacy_waiting')) {
                    // Add medical rec
                    $newMedRecord = new MedicalRecord();
                    $newMedRecord->appointment_uuid = $request->input('uuid');
                    $newMedRecord->record_no = Str::uuid();
                    $newMedRecord->patient_id = $appointment->patient_id;
                    $newMedRecord->prescription_id = $rx->id;
                    $newMedRecord->additional_note = $request->has('medical_note') ? $request->input('medical_note') : null;
                    $newMedRecord->save();
                }

                AssignmentCreated::dispatch();

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
            Log::error($e->getTrace());
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
            $model = Appointments::with(['patient', 'detail', 'patient.patientPotrait'])
                ->when($request->status === config("constants.status.doctor_assigned"), function ($query) {
                    $query->with(['patient.prescriptions.medicalRecord' => function ($query) {
                        $query->take(5);
                    }]);
                })
                ->when($request->status === config("constants.status.pharmacy_assigned"), function ($query) {
                    $query->with(['patient.prescriptions.medicalRecord', 'medicalRecord' => function ($query) {
                        $query->take(5);
                    }]);
                })
                ->when($request->status === config("constants.status.payment_assigned"), function ($query) {
                    $query->with('prescription');
                })
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
                if ($request->status === config("constants.status.payment_assigned")) {
                    if ($model->prescription->list) {
                        // Get its price
                        $model->prescription->list = Arr::map($model->prescription->list, function ($value, $key) {
                            $sellPrice = null;
                            $medSellPrice = Medicine::select('sell_price')
                                ->where('sku', $value['sku'])
                                ->first();
                            if (!$medSellPrice) {
                                $svcSellPrice = Services::select('sell_price')
                                    ->where('sku', $value['sku'])
                                    ->first();

                                $sellPrice = $svcSellPrice;
                            } else {
                                $sellPrice = $medSellPrice;
                            }
                            $value['price'] = $sellPrice ? $sellPrice->sell_price : null;

                            return $value;
                        });
                    }
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

    public function getPrice($query)
    {
        try {
            $model = Medicine::select('sell_price')->where('sku', $query)->first();
            if (!$model) {
                $model = Services::select('sell_price')->where('sku', $query)->first();
            }

            return response()->json([
                'status'    => true,
                'data'      => $model->sell_price
            ], 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => env('APP_ENV') === 'production' ? 'Unexpected error. Please check log.' : $e->getMessage()
            ], 500);
        }
    }

    public function sendToDoc(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'uuid'          => 'required'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status'    => false,
                    'message'   => $validator->errors()
                ], 422);
            }

            // Check if this patient already has consultation
            $details = AppointmentsDetail::where('appointment_uuid', $request->input('uuid'))->get();
            foreach ($details as $detail) {
                if ($detail->status === config('constants.status.doctor_assigned')) {
                    return response()->json([
                        'status'    => false,
                        'message'   => 'This patient already has consultation. Cannot re-assigned to doctor.'
                    ], 400);
                }
            }

            // Change status
            $assignment = Appointments::where('uuid', $request->input('uuid'))->first();
            $assignment->status = config('constants.status.doctor_waiting');
            $assignment->save();

            $request->merge(["status" => $assignment->status, "pic" => auth()->id()]);
            $makeDetail = $this->makeDetail($request);

            if ($makeDetail->getStatusCode() === 200) {
                return response()->json([
                    'status'    => false,
                    'message'   => 'Assigned to doctor.'
                ], 200);
            } else {
                return response()->json([
                    'status'    => false,
                    'message'   => 'Re-assignment to doctor failed.'
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => env('APP_ENV') === 'production' ? 'Unexpected error. Please check log.' : $e->getMessage()
            ], 500);
        }
    }
}
