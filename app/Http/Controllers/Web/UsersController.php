<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PrivilegeController;
use App\Models\Groups;
use App\Models\Users;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UsersController extends Controller
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

    /**
     * Returns user list view
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        try {
            $userData = $this->getUserData($request);
            $data = [
                "user"  => $this->userData,
                "menus" => $this->menuController->__invoke($request)->original,
                "privs" => $this->privilegeController->__invoke($request)->original["data"],
                "data"  => $userData
            ];

            return view('/admin/users', $data);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => 'Unexpected error happened.'
            ], 500);
        }
    }

    /**
     * Get users data based on request
     *
     * @param \Illuminate\Http\Request $request
     * @return array $data
     */
    private function getUserData($request)
    {
        $data = [];
        $limit = $request->has("limit") ? $request->input("limit") : 10;
        $filter = $request->has("filter") ? $request->input("filter") : null;

        $userModel = Users::with('group')->withTrashed();
        if ($filter) {
            $userModel->where('username', 'ILIKE', "%$filter%")
                ->orWhere('name', 'ILIKE', "%$filter%")
                ->orWhere('email', 'ILIKE', "%$filter%");
        }
        $userModel = $userModel->orderBy("username", "asc");
        $groupModel = Groups::all();

        $data = [
            "rows"  => $userModel->paginate($limit),
            "hasFilter" => $filter ? true : false,
            "groups" => $groupModel
        ];

        return $data;
    }

    /**
     * Deactivate or activate user based on request
     * Required privileges = ['DELETE_USER']
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function changeState(Request $request)
    {
        try {
            $privileges = $this->privilegeController->__invoke($request)->original["data"];
            if (in_array("DELETE_USER", $privileges)) {
                if ($request->has("id")) {
                    if ($request->input("type") === "deactivate") {
                        $model = Users::find($request->input("id"))->delete();
                    } elseif ($request->input("type") === "activate") {
                        $model = Users::withTrashed()->where('id', $request->input("id"))->restore();
                    }

                    return response()->json([
                        'status'    => true,
                        'message'   => "User ID: " . $request->input('id') . " has been deleted.",
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
     * Save user model based on request
     * Required privileges = ['CREATE_USER', 'UPDATE_USER']
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function save(Request $request)
    {
        try {
            $privileges = $this->privilegeController->__invoke($request)->original["data"];
            if ($request->input("id")) {
                if (in_array("UPDATE_USER", $privileges)) {
                    $validator = Validator::make($request->all(), [
                        'username'      => 'required',
                        'name'          => 'required',
                        'email'         => 'nullable|email:rfc,dns',
                        'phone_number'  => 'nullable|digits_between:8,15',
                        'group'         => 'required'
                    ]);
                    if ($validator->fails()) {
                        return response()->json([
                            'status'    => false,
                            'message'   => $validator->errors()
                        ], 422);
                    }

                    $modelUser = Users::find($request->input('id'));
                    if ($modelUser) {
                        $modelUser->username = $request->username;
                        $modelUser->name = $request->name;
                        $modelUser->email = $request->email;
                        $modelUser->phone_number = $request->phone_number;
                        $modelUser->group_id = $request->group;

                        $modelUser->save();

                        return response()->json([
                            'status'    => true,
                            'data'      => $modelUser,
                            'message'   => "User $modelUser->username has been updated."
                        ], 200);
                    }
                } else {
                    return response()->json([
                        'status'    => false,
                        'message'   => 'You did not have permission to do this action.'
                    ], 403);
                }
            } else {
                if (in_array("CREATE_USER", $privileges)) {
                    $validator = Validator::make($request->all(), [
                        'username'      => 'required|unique:users',
                        'name'          => 'required',
                        'email'         => 'nullable|email:rfc,dns',
                        'phone_number'  => 'nullable|digits_between:8,15',
                        'group'         => 'required'
                    ]);
                    if ($validator->fails()) {
                        return response()->json([
                            'status'    => false,
                            'message'   => $validator->errors()
                        ], 422);
                    }
                    $newUser = Users::create([
                        'username'      => $request->username,
                        'password'      => Hash::make('12345'),
                        'name'          => $request->name,
                        'email'         => $request->email,
                        'phone_number'  => $request->phone_number,
                        'group_id'      => $request->group
                    ]);
                    if ($newUser) {
                        return response()->json([
                            'status'    => true,
                            'data'      => $newUser,
                            'message'   => 'New user created.'
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

    public function viewConfigs(Request $request)
    {
        try {
            $data = [
                "user"  => $this->userData,
                "menus" => $this->menuController->__invoke($request)->original,
                "privs" => $this->privilegeController->__invoke($request)->original["data"]
            ];

            return view('/admin/user-configs', $data);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => 'Unexpected error happened.'
            ], 500);
        }
    }

    public function saveConfigs(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name'          => 'required',
                'email'         => 'nullable|email:rfc,dns',
                'phone_number'  => 'nullable|digits_between:8,15',
                'schedule'      => 'nullable|array',
                'new_password'      => 'nullable|min:6|required_with:password_confirmation',
                'confirm_password'  => 'same:new_password',
                'npwp'          => 'nullable|string|min:15'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status'    => false,
                    'message'   => $validator->errors()
                ], 422);
            }

            $user = Users::where('id', auth()->user()->id)->first();

            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone_number = $request->phone_number;
            $user->configs = [
                "schedule" => $request->schedule
            ];
            $user->password = Hash::make($request->new_password);
            $user->npwp = $request->npwp;

            $user->save();

            return response()->json([
                'status'    => true,
                'data'      => $user,
                'message'   => 'Configuration saved.'
            ], 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => 'Unexpected error happened.'
            ], 500);
        }
    }

    public function resetPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id'      => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status'    => false,
                    'message'   => $validator->errors()
                ], 422);
            }

            $user = Users::find($request->input("id"));
            $user->password = Hash::make("12345");

            $user->save();

            return response()->json([
                'status'    => true,
                'data'      => $user,
                'message'   => 'Reset was successfull.'
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
