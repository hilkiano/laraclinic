<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PrivilegeController;
use App\Models\Roles;

class RolesController extends Controller
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
            $rolesData = $this->getRolesData($request);
            $data = [
                "user"  => $this->userData,
                "menus" => $this->menuController->__invoke($request)->original,
                "privs" => $this->privilegeController->__invoke($request)->original["data"],
                "data"  => $rolesData
            ];

            return view('/admin/roles', $data);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => $e->getMessage()
            ], 500);
        }
    }

    private function getRolesData($request)
    {
        $data = [];
        $limit = $request->has("limit") ? $request->input("limit") : 10;
        $filter = $request->has("filter") ? $request->input("filter") : null;

        $rolesModel = Roles::withTrashed();
        if ($filter) {
            $rolesModel->where('name', 'ILIKE', "%$filter%")
                ->orWhere('description', 'ILIKE', "%$filter%");
        }

        $paginated = $rolesModel->paginate($limit);
        foreach ($paginated->items() as $row) {
            $row->menu_count = count($row->menu_ids);
            $row->privilege_count = count($row->privilege_ids);
        }

        $data = [
            "rows"      => $paginated,
            "hasFilter" => $filter ? true : false
        ];

        return $data;
    }
}
