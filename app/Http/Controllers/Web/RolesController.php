<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PrivilegeController;
use App\Models\Groups;
use App\Models\Menus;
use App\Models\Privileges;
use App\Models\Roles;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

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
            "hasFilter" => $filter ? true : false,
            "menus"     => Menus::all(),
            "privileges" => Privileges::all()
        ];

        return $data;
    }

    /**
     * Deactivate or activate menu based on request
     * Required privileges = ['DELETE_ROLE']
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function changeState(Request $request)
    {
        try {
            $privileges = $this->privilegeController->__invoke($request)->original["data"];
            if (in_array("DELETE_ROLE", $privileges)) {
                if ($request->has("id")) {
                    if ($request->input("type") === "deactivate") {
                        // CHECK IF THERE IS A GROUP USING THIS ROLE
                        if (!$this->checkRoleUsage($request->input("id"))) {
                            $model = Roles::find($request->input("id"))->delete();
                        } else {
                            return response()->json([
                                'status'    => false,
                                'message'   => 'This role is still in use. Aborting request.'
                            ], 400);
                        }
                    } elseif ($request->input("type") === "activate") {
                        $model = Roles::withTrashed()->where('id', $request->input("id"))->restore();
                    }

                    return response()->json([
                        'status'    => true,
                        'message'   => "Role ID: " . $request->input('id') . " has been deleted.",
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

    private function checkRoleUsage($id)
    {
        $groups = Groups::all()->toArray();
        $matched = [];
        foreach ($groups as $key => $value) {
            if (in_array($id, $value['role_ids'])) {
                array_push($matched, $key);
            }
        }
        return count($matched) > 0;
    }

    /**
     * Save menu model based on request
     * Required privileges = ['CREATE_ROLE', 'UPDATE_ROLE']
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function save(Request $request)
    {
        try {
            $privileges = $this->privilegeController->__invoke($request)->original["data"];
            if ($request->input("id")) {
                if (in_array("UPDATE_ROLE", $privileges)) {
                    $validator = Validator::make($request->all(), [
                        'name'          => 'required',
                        'menu_ids'      => 'required',
                        'privilege_ids' => 'required',
                    ]);
                    if ($validator->fails()) {
                        return response()->json($validator->errors(), 422);
                    }

                    $modelRole = Roles::find($request->input('id'));
                    if ($modelRole) {
                        $modelRole->name = $request->name;
                        $modelRole->description = $request->description !== "" ? $request->description : null;
                        $modelRole->menu_ids = Arr::map($request->menu_ids, function ($value) {
                            return (int) $value;
                        });
                        $modelRole->privilege_ids = Arr::map($request->privilege_ids, function ($value) {
                            return (int) $value;
                        });

                        $modelRole->save();

                        return response()->json([
                            'status'    => true,
                            'data'      => $modelRole,
                            'message'   => "Role $modelRole->name has been updated."
                        ], 200);
                    }
                } else {
                    return response()->json([
                        'status'    => false,
                        'message'   => 'You did not have permission to do this action.'
                    ], 403);
                }
            } else {
                if (in_array("CREATE_ROLE", $privileges)) {
                    $validator = Validator::make($request->all(), [
                        'name'          => 'required|unique:roles',
                        'menu_ids'      => 'required',
                        'privilege_ids' => 'required',
                    ]);
                    if ($validator->fails()) {
                        return response()->json([
                            'status'    => false,
                            'message'   => $validator->errors()
                        ], 422);
                    }
                    $newRole = Roles::create([
                        'name'          => $request->name,
                        'description'   => $request->description !== "" ? $request->description : null,
                        'menu_ids'      => Arr::map($request->menu_ids, function ($value) {
                            return (int) $value;
                        }),
                        'privilege_ids' => Arr::map($request->privilege_ids, function ($value) {
                            return (int) $value;
                        }),
                    ]);
                    if ($newRole) {
                        return response()->json([
                            'status'    => true,
                            'data'      => $newRole,
                            'message'   => 'New role created.'
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
