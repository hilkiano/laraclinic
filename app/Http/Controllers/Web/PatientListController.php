<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PrivilegeController;
use App\Models\Patients;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PatientListController extends Controller
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
            $viewData = $this->getViewData($request);
            $data = [
                "user"  => $this->userData,
                "menus" => $this->menuController->__invoke($request)->original,
                "privs" => $this->privilegeController->__invoke($request)->original["data"],
                "data"  => $viewData,
                "image_placeholder" => asset('images/potrait-placeholder.png'),
            ];

            return view('/patient/list', $data);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => 'Unexpected error.'
            ], 500);
        }
    }

    private function getViewData($request)
    {
        $patientModel = Patients::query()->with(['patientPotrait', 'appointments'])->orderBy('name', 'asc');
        $limit = $request->has("limit") ? $request->input("limit") : 20;
        $filterBy = $request->has("filter_by") ? $request->input("filter_by") : null;
        $filterField = $request->has("filter_field") ? $request->input("filter_field") : null;

        if ($filterBy && $filterField) {
            $patientModel->where($filterBy, 'ILIKE', "%$filterField%");
        }

        $paginated = $patientModel->paginate($limit);
        foreach ($paginated->items() as $row) {
            if ($row->patientPotrait) {
                $row->last_potrait = Arr::last($row->patientPotrait->url);
            }
            if ($row->birth_date) {
                $row->birth_date_formatted = Carbon::make($row->birth_date)->isoFormat("D MMMM YYYY");
                $row->age = Carbon::make($row->birth_date)->age;
            }
            $row->joined_at = Carbon::make($row->created_at)->setTimezone(env('APP_TIME_ZONE'))->isoFormat("D MMMM YYYY, HH:mm:ss");
            $lastVisited = null;
            if (count($row->appointments) > 0) {
                $lastVisited = $row->appointments->first()
                    ->created_at
                    ->setTimezone(env('APP_TIME_ZONE'))
                    ->isoFormat('DD MMMM YYYY HH:mm:ss');
            }
            $row->last_visited = $lastVisited;
        }

        $data = [
            "rows"      => $paginated,
            "hasFilter" => $filterBy && $filterField ? true : false
        ];

        return $data;
    }

    public function selectList($query)
    {
        try {
            $data = Patients::select('id', 'name', 'code')
                ->where(function ($q) use ($query) {
                    $q->where('name', 'ILIKE', "%$query%")
                        ->orWhere('code', 'ILIKE', "%$query%");
                })
                ->orderBy('name', 'asc')
                ->get();

            if ($data) {
                foreach ($data as $d) {
                    $d->name = $d->name . " (" . $d->code . ")";
                }
            }
            return response()->json([
                'status'    => true,
                'data'      => $data
            ], 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => 'Unexpected error.'
            ], 500);
        }
    }
}
