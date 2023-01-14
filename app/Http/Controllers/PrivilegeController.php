<?php

namespace App\Http\Controllers;

use App\Models\Privileges;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Users;
use App\Models\Roles;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PrivilegeController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        try {
            $user = Users::with('group')->find(Auth::id());
            $privIds = [];
            // Get menu from each role id 
            foreach ($user->group->role_ids as $roleId) {
                $role = Roles::find($roleId);
                foreach ($role->privilege_ids as $privId) {
                    if (!in_array($privId, $privIds, true)) {
                        array_push($privIds, $privId);
                    }
                }
            }

            $privNames = [];
            $privModel = Privileges::select('name')->find($privIds);
            foreach ($privModel as $priv) {
                array_push($privNames, $priv->name);
            }

            return response()->json([
                'status'    => true,
                'data'      => $privNames
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => $e->getMessage()
            ], 500);
        }
    }
}
