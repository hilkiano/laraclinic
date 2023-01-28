<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PrivilegeController;
use App\Models\Patients;
use Carbon\Carbon;
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
                "data"  => $viewData
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
        $patientModel = Patients::query()->with('patientPotrait');
        $limit = $request->has("limit") ? $request->input("limit") : 20;
        $filterBy = $request->has("filter_by") ? $request->input("filter_by") : null;
        $filterField = $request->has("filter_field") ? $request->input("filter_field") : null;

        if ($filterBy && $filterField) {
            $patientModel->where($filterBy, 'ILIKE', "%$filterField%");
        }

        $paginated = $patientModel->paginate($limit);
        foreach ($paginated->items() as $row) {
            $row->birth_date_formatted = Carbon::make($row->birth_date)->isoFormat("D MMMM YYYY");
            $row->joined_at = Carbon::make($row->created_at)->setTimezone(env('APP_TIME_ZONE'))->isoFormat("D MMMM YYYY, HH:mm:ss");
            $row->age = Carbon::make($row->birth_date)->age;
        }

        $data = [
            "rows"      => $paginated,
            "hasFilter" => $filterBy && $filterField ? true : false
        ];

        return $data;
    }
}
