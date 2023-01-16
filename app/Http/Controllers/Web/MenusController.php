<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PrivilegeController;
use App\Models\Menus;
use Illuminate\Support\Facades\Validator;

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
        $menuModel = $menuModel->orderBy("name", "asc");

        $data = [
            "rows"      => $menuModel->paginate($limit),
            "hasFilter" => $filter ? true : false,
            "hostname"  => request()->getHttpHost(),
            "parents"   => Menus::where("is_parent", true)->get()
        ];

        return $data;
    }

    /**
     * Deactivate or activate menu based on request
     * Required privileges = ['DELETE_MENU']
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function changeState(Request $request)
    {
        try {
            $privileges = $this->privilegeController->__invoke($request)->original["data"];
            if (in_array("DELETE_MENU", $privileges)) {
                if ($request->has("id")) {
                    if ($request->input("type") === "deactivate") {
                        $model = Menus::find($request->input("id"))->delete();
                    } elseif ($request->input("type") === "activate") {
                        $model = Menus::withTrashed()->where('id', $request->input("id"))->restore();
                    }

                    return response()->json([
                        'status'    => true,
                        'message'   => "Menu ID: " . $request->input('id') . " has been deleted.",
                        'data'      => $model
                    ], 200);
                }

                return response()->json([
                    'status'    => true,
                    'message'   => "Nothing was changed."
                ], 200);
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
                'message'   => 'Unexpected error happened.'
            ], 500);
        }
    }

    /**
     * Save menu model based on request
     * Required privileges = ['CREATE_MENU', 'UPDATE_MENU']
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function save(Request $request)
    {
        try {
            $privileges = $this->privilegeController->__invoke($request)->original["data"];
            if ($request->input("id")) {
                if (in_array("UPDATE_MENU", $privileges)) {
                    $validator = Validator::make($request->all(), [
                        'name'      => 'required',
                        'label'     => 'required',
                        'order'     => 'required|numeric',
                    ]);
                    if ($validator->fails()) {
                        return response()->json($validator->errors(), 422);
                    }

                    $modelMenu = Menus::find($request->input('id'));
                    if ($modelMenu) {
                        $modelMenu->name = $request->name;
                        $modelMenu->label = $request->label;
                        $modelMenu->is_parent = $request->is_parent ? true : false;
                        $modelMenu->order = $request->order;
                        $modelMenu->route = $request->route;
                        $modelMenu->parent = $request->parent !== "" ? $request->parent : null;
                        $modelMenu->icon = $request->icon;

                        $modelMenu->save();

                        return response()->json([
                            'status'    => true,
                            'data'      => $modelMenu,
                            'message'   => "Menu $modelMenu->name has been updated."
                        ], 200);
                    }
                } else {
                    return response()->json([
                        'status'    => false,
                        'message'   => 'You did not have permission to do this action.'
                    ], 403);
                }
            } else {
                if (in_array("CREATE_MENU", $privileges)) {
                    $validator = Validator::make($request->all(), [
                        'name'      => 'required|unique:menus',
                        'label'     => 'required',
                        'order'     => 'required|numeric',
                    ]);
                    if ($validator->fails()) {
                        return response()->json([
                            'status'    => false,
                            'message'   => $validator->errors()
                        ], 422);
                    }
                    $newMenu = Menus::create([
                        'name'          => $request->name,
                        'label'         => $request->label,
                        'is_parent'     => $request->is_parent ? true : false,
                        'icon'          => $request->icon,
                        'order'         => $request->order,
                        'route'         => $request->route,
                        'parent'        => $request->parent !== "" ? $request->parent : null
                    ]);
                    if ($newMenu) {
                        return response()->json([
                            'status'    => true,
                            'data'      => $newMenu,
                            'message'   => 'New menu created.'
                        ], 200);
                    }
                } else {
                    return response()->json([
                        'status'    => false,
                        'message'   => 'You did not have permission to do this action.'
                    ], 403);
                }
            }

            return response()->json([
                'status'    => true,
                'requests'  => $request->all()
            ], 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => 'Unexpected error happened.'
            ], 500);
        }
    }
}
