<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PrivilegeController;
use App\Models\Privileges;
use Illuminate\Support\Facades\Log;

class PrivilegesController extends Controller
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
            $menuData = $this->getMenuData($request);
            $data = [
                "user"  => $this->userData,
                "menus" => $this->menuController->__invoke($request)->original,
                "privs" => $this->privilegeController->__invoke($request)->original["data"],
                "data"  => $menuData
            ];

            return view('/admin/privileges', $data);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => $e->getMessage()
            ], 500);
        }
    }

    private function getMenuData($request)
    {
        $data = [];
        $limit = $request->has("limit") ? $request->input("limit") : 10;
        $filter = $request->has("filter") ? $request->input("filter") : null;

        $privilegeModel = Privileges::query();
        if ($filter) {
            $privilegeModel->where('name', 'ILIKE', "%$filter%")
                ->orWhere('description', 'ILIKE', "%$filter%");
        }

        $data = [
            "rows"      => $privilegeModel->paginate($limit),
            "hasFilter" => $filter ? true : false
        ];

        return $data;
    }
}
