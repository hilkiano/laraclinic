<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PrivilegeController;
use Illuminate\Support\Facades\Log;
use App\Models\Appointments;
use App\Models\AppointmentsDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AppointmentController extends Controller
{
    private $userData;
    private $menuController;
    private $privilegeController;

    public function __construct()
    {
        $this->userData = auth()->user();
        $this->menuController = new MenuController();
        $this->privilegeController = new PrivilegeController();
    }

    public function index(Request $request)
    {
        try {
            $appointmentData = $this->getAppointmentData($request);
            $data = [
                "user"  => $this->userData,
                "menus" => $this->menuController->__invoke($request)->original,
                "privs" => $this->privilegeController->__invoke($request)->original["data"],
                "data"  => $appointmentData
            ];

            return view('/appointments/list', $data);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => $e->getMessage()
            ], 500);
        }
    }

    private function getAppointmentData($request)
    {
        $data = [];

        $limit = $request->has("limit") ? $request->input("limit") : 10;
        $filter = $request->has("filter") ? $request->input("filter") : null;
        $status = $request->has("status") ? $request->input("status") : null;
        $reason = $request->has("reason") ? $request->input("reason") : null;

        $start = Carbon::now()->setTimezone(env('APP_TIME_ZONE'))->startOfDay()->utc();
        $end = Carbon::now()->setTimezone(env('APP_TIME_ZONE'))->endOfDay()->utc();

        $appointmentModel = Appointments::with('patient')
            ->where('created_at', '>=', $start)
            ->where('created_at', '<=', $end)
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($reason, function ($query) use ($reason) {
                $query->where('visit_reason', $reason);
            })
            ->when($filter, function ($query) use ($filter) {
                $query->whereHas('patient', function ($query) use ($filter) {
                    return $query->where('name', 'ILIKE', "%$filter%");
                });
            });
        $paginated = $appointmentModel->paginate($limit);
        foreach ($paginated->items() as $row) {
            $row->visit_time = Carbon::make($row->visit_time)->setTimezone(env('APP_TIME_ZONE'))->isoFormat('DD MMMM YYYY HH:mm:ss');
        }

        $data = [
            "rows"  => $paginated,
            "hasFilter" => $filter ? true : false,
            "today" => Carbon::now()->setTimezone(env('APP_TIME_ZONE'))->isoFormat('dddd, DD MMMM YYYY')
        ];

        return $data;
    }

    public function make(Request $request)
    {
        try {
            $privileges = $this->privilegeController->__invoke($request)->original["data"];
            if (in_array("PATIENT_ASSIGNMENT", $privileges)) {
                $validator = Validator::make($request->all(), [
                    'patient_id'    => 'required',
                    'reason'        => 'required',
                    'visit_time'    => 'required'
                ]);
                if ($validator->fails()) {
                    return response()->json([
                        'status'    => false,
                        'message'   => $validator->errors()
                    ], 422);
                }

                $newAppointment = Appointments::create([
                    'uuid'          => Str::uuid(),
                    'patient_id'    => $request->patient_id,
                    'visit_time'    => Carbon::createFromIsoFormat('MM/DD/YYYY, HH:mm:ss A', $request->visit_time, env('APP_TIME_ZONE'))->setTimezone('UTC'),
                    'visit_reason'  => $request->reason,
                    'status'        => 'waiting',
                    'additional_note' => $request->additional_note
                ]);

                $newAppointmentDetail = AppointmentsDetail::create([
                    'appointment_uuid'  => $newAppointment->uuid,
                    'status'            => $newAppointment->status,
                    'additional_note'   => $newAppointment->additional_note
                ]);

                if ($newAppointment) {
                    return response()->json([
                        'status'    => true,
                        'data'      => [
                            'appointment' => $newAppointment,
                            'detail'      => $newAppointmentDetail
                        ],
                        'message'   => 'New appointment created. <br /> <a href="/appointment/detail/' . $newAppointment->uuid . '" _target="blank">See details</a>'
                    ], 200);
                }
            } else {
                return response()->json([
                    'status'    => false,
                    'message'   => 'You did not have permission to do this action.'
                ], 403);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => $e->getMessage()
            ], 500);
        }
    }

    public function viewCompleteList(Request $request)
    {
        try {
            $data = [
                "user"  => $this->userData,
                "menus" => $this->menuController->__invoke($request)->original,
                "privs" => $this->privilegeController->__invoke($request)->original["data"],
            ];

            return view('/appointments/complete-list', $data);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => $e->getMessage()
            ], 500);
        }
    }

    public function getCompleteList(Request $request)
    {
        try {
            $dataPerPage = $request->input("limit");
            $page = $request->input("page") + 1;
            $name = $request->has("name") ? $request->input("name") : null;
            $reason = $request->has("reason") ? $request->input("reason") : null;
            $status = $request->has("status") ? $request->input("status") : null;
            $startDate = $request->has("startDate") ? $request->input("startDate") : null;
            $endDate = $request->has("endDate") ? $request->input("endDate") : null;
            $offset = ($page === 1) ? 0 : ($page * $dataPerPage) - $dataPerPage;
            $model = Appointments::with('patient')
                ->when($name, function ($query) use ($name) {
                    $query->whereHas('patient', function ($q) use ($name) {
                        $q->where('name', 'ILIKE', "%$name%");
                    });
                })
                ->when($reason, function ($query) use ($reason) {
                    $query->where('visit_reason', $reason);
                })
                ->when($status, function ($query) use ($status) {
                    $query->where('status', $status);
                })
                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                    $query->where('visit_time', '>=', $startDate)
                        ->where('visit_time', '<=', $endDate);
                });
            $count = $model->count();
            $model = $model->limit($dataPerPage)
                ->offset($offset);
            $data = $model->get();

            return response()->json([
                'status'        => true,
                'data'          => $data,
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
                'message'   => $e->getMessage()
            ], 500);
        }
    }

    public function viewAssignment(Request $request)
    {
        try {
            $data = [
                "user"  => $this->userData,
                "menus" => $this->menuController->__invoke($request)->original,
                "privs" => $this->privilegeController->__invoke($request)->original["data"]
            ];

            return view('/appointments/assignment', $data);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => $e->getMessage()
            ], 500);
        }
    }

    public function viewDetail(Request $request)
    {
        try {
            $uuid = $request->uuid;
            $data = [
                "user"  => $this->userData,
                "menus" => $this->menuController->__invoke($request)->original,
                "privs" => $this->privilegeController->__invoke($request)->original["data"],
                "data"  => Appointments::with(['detail', 'patient'])->where('uuid', $uuid)->first()
            ];

            return view('/appointments/detail', $data);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => $e->getMessage()
            ], 500);
        }
    }
}
