<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PrivilegeController;
use Illuminate\Support\Facades\Log;
use App\Models\Groups;
use App\Models\Roles;

class GroupsController extends Controller
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
            $groupData = $this->getGroupData($request);
            $data = [
                "user"  => $this->userData,
                "menus" => $this->menuController->__invoke($request)->original,
                "privs" => $this->privilegeController->__invoke($request)->original["data"],
                "data"  => $groupData
            ];

            return view('/admin/groups', $data);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => $e->getMessage()
            ], 500);
        }
    }

    private function getGroupData($request)
    {
        $data = [];
        $limit = $request->has("limit") ? $request->input("limit") : 10;
        $filter = $request->has("filter") ? $request->input("filter") : null;

        $groupsModel = Groups::withTrashed();
        if ($filter) {
            $groupsModel->where('name', 'ILIKE', "%$filter%")
                ->orWhere('description', 'ILIKE', "%$filter%");
        }
        $paginated = $groupsModel->paginate($limit);
        foreach ($paginated->items() as $row) {
            $roles = [];
            foreach ($row->role_ids as $roleId) {
                $roleModel = Roles::select('name')->find($roleId)->pluck('name');
                array_push($roles, $roleModel[0]);
            }
            $row->roles = $roles;
        }

        $data = [
            "rows"  => $paginated,
            "hasFilter" => $filter ? true : false
        ];

        return $data;
    }
}
