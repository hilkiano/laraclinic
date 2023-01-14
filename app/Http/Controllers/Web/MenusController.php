<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PrivilegeController;
use App\Models\Menus;

class MenusController extends Controller
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

            return view('/admin/menus', $data);
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

        $menuModel = Menus::withTrashed();
        if ($filter) {
            $menuModel->where('name', 'ILIKE', "%$filter%")
                ->orWhere('route', 'ILIKE', "%$filter%")
                ->orWhere('label', 'ILIKE', "%$filter%")
                ->orWhere('parent', 'ILIKE', "%$filter%");
        }

        $data = [
            "rows"      => $menuModel->paginate($limit),
            "hasFilter" => $filter ? true : false
        ];

        return $data;
    }
}
