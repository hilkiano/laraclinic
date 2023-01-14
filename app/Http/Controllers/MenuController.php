<?php

namespace App\Http\Controllers;

use App\Models\Menus;
use App\Models\Roles;
use Illuminate\Http\Request;
use App\Models\Users;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MenuController extends Controller
{
    /**
     * Requesting menu array for UI
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {
        try {
            $user = Users::with('group')->find(Auth::id());
            $menuIds = [];
            // Get menu from each role id 
            foreach ($user->group->role_ids as $roleId) {
                $role = Roles::find($roleId);
                foreach ($role->menu_ids as $menuId) {
                    if (!in_array($menuId, $menuIds, true)) {
                        array_push($menuIds, $menuId);
                    }
                }
            }

            $menus = $this->generateMenu($menuIds);

            return response()->json([
                'status'    => true,
                'data'      => $menus
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => $e->getMessage()
            ], 500);
        }
    }

    protected function generateMenu($menuIds)
    {
        $menus = [];
        foreach ($menuIds as $menu) {
            $model = Menus::find($menu);
            array_push($menus, $model);
        }

        return $this->organizeMenu($menus);
    }

    private function organizeMenu($menus)
    {
        $requestPath = (request()->path() !== '/') ? '/' . request()->path() : request()->path();
        $org = [];
        foreach ($menus as $menu) {
            if ($menu->is_parent) {
                array_push($org, (object)[
                    "name"          => $menu->name,
                    "label"         => $menu->label,
                    "order"         => $menu->order,
                    "is_parent"     => $menu->is_parent,
                    "route"         => $menu->route,
                    "icon"          => $menu->icon,
                    "child"         => $this->organizeChildMenu($menus, $menu->name)
                ]);
            } elseif (empty($menu->parent)) {
                array_push($org, (object)[
                    "name"      => $menu->name,
                    "label"     => $menu->label,
                    "order"     => $menu->order,
                    "is_parent" => $menu->is_parent,
                    "route"     => $menu->route,
                    "icon"      => $menu->icon,
                    "is_active" => $menu->route === $requestPath
                ]);
            }
        }

        $sorted = collect($org)->sortBy('order', SORT_NUMERIC)->toArray();
        return array_values($sorted);
    }

    private function organizeChildMenu($menus, $parentName)
    {
        $children = [];
        $requestPath = (request()->path() !== '/') ? '/' . request()->path() : request()->path();
        foreach ($menus as $menu) {
            if ($menu->parent === $parentName) {
                array_push($children, (object)[
                    "name"      => $menu->name,
                    "label"     => $menu->label,
                    "order"     => $menu->order,
                    "is_parent" => $menu->is_parent,
                    "route"     => $menu->route,
                    "icon"      => $menu->icon,
                    "is_active" => $menu->route === $requestPath
                ]);
            }
        }

        $sorted = collect($children)->sortBy('order', SORT_NUMERIC)->toArray();
        return $sorted;
    }
}
