<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PrivilegeController;
use Illuminate\Support\Facades\Log;
use App\Models\Appointments;
use Carbon\Carbon;
use Illuminate\Support\Arr;
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

            return view('/appointment', $data);
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
            "today" => Carbon::now()->isoFormat('dddd, DD MMMM YYYY')
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
                    'visit_time'    => Carbon::now(),
                    'visit_reason'  => $request->reason,
                    'status'        => 'waiting'
                ]);

                if ($newAppointment) {
                    return response()->json([
                        'status'    => true,
                        'data'      => $newAppointment,
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
}
