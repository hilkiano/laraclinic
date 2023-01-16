<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PrivilegeController;
use Illuminate\Support\Facades\Log;
use App\Models\Groups;
use App\Models\Roles;
use App\Models\Users;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

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
                $roleModel = Roles::select('name')->where('id', $roleId)->first();
                array_push($roles, $roleModel->name);
            }
            $row->roles = $roles;
        }

        $data = [
            "rows"  => $paginated,
            "hasFilter" => $filter ? true : false,
            "roles" => Roles::all()
        ];

        return $data;
    }

    /**
     * Deactivate or activate menu based on request
     * Required privileges = ['DELETE_GROUP']
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function changeState(Request $request)
    {
        try {
            $privileges = $this->privilegeController->__invoke($request)->original["data"];
            if (in_array("DELETE_GROUP", $privileges)) {
                if ($request->has("id")) {
                    if ($request->input("type") === "deactivate") {
                        // CHECK IF THERE IS A GROUP USING THIS ROLE
                        if (!$this->checkGroupUsage($request->input("id"))) {
                            $model = Groups::find($request->input("id"))->delete();
                        } else {
                            return response()->json([
                                'status'    => false,
                                'message'   => 'This group is still in use. Aborting request.'
                            ], 400);
                        }
                    } elseif ($request->input("type") === "activate") {
                        $model = Groups::withTrashed()->where('id', $request->input("id"))->restore();
                    }

                    return response()->json([
                        'status'    => true,
                        'message'   => "Group ID: " . $request->input('id') . " has been deleted.",
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

    private function checkGroupUsage($id)
    {
        $users = Users::all()->toArray();
        $matched = [];
        foreach ($users as $key => $value) {
            if ($id == $value["group_id"]) {
                array_push($matched, $key);
            }
        }

        return count($matched) > 0;
    }

    /**
     * Save menu model based on request
     * Required privileges = ['CREATE_GROUP', 'UPDATE_GROUP']
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function save(Request $request)
    {
        try {
            $privileges = $this->privilegeController->__invoke($request)->original["data"];
            if ($request->input("id")) {
                if (in_array("UPDATE_GROUP", $privileges)) {
                    $validator = Validator::make($request->all(), [
                        'name'          => 'required',
                        'role_ids'      => 'required',
                    ]);
                    if ($validator->fails()) {
                        return response()->json($validator->errors(), 422);
                    }

                    $modelGroup = Groups::find($request->input('id'));
                    if ($modelGroup) {
                        $modelGroup->name = $request->name;
                        $modelGroup->description = $request->description !== "" ? $request->description : null;
                        $modelGroup->role_ids = Arr::map($request->role_ids, function ($value) {
                            return (int) $value;
                        });

                        $modelGroup->save();

                        return response()->json([
                            'status'    => true,
                            'data'      => $modelGroup,
                            'message'   => "Group $modelGroup->name has been updated."
                        ], 200);
                    }
                } else {
                    return response()->json([
                        'status'    => false,
                        'message'   => 'You did not have permission to do this action.'
                    ], 403);
                }
            } else {
                if (in_array("CREATE_GROUP", $privileges)) {
                    $validator = Validator::make($request->all(), [
                        'name'          => 'required|unique:groups',
                        'role_ids'      => 'required',
                    ]);
                    if ($validator->fails()) {
                        return response()->json([
                            'status'    => false,
                            'message'   => $validator->errors()
                        ], 422);
                    }
                    $newGroup = Groups::create([
                        'name'          => $request->name,
                        'description'   => $request->description !== "" ? $request->description : null,
                        'role_ids'      => Arr::map($request->role_ids, function ($value) {
                            return (int) $value;
                        }),
                    ]);
                    if ($newGroup) {
                        return response()->json([
                            'status'    => true,
                            'data'      => $newGroup,
                            'message'   => 'New group created.'
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
