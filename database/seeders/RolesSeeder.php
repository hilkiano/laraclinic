<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Roles::insert([
            [
                'name'      => 'Super Admin',
                'menu_ids'  => $this->getAllMenuIds(),
                'privilege_ids' => $this->getAllPrivilegeIds()
            ]
        ]);
    }

    protected function getAllMenuIds()
    {
        $menuIds = [];

        $model = \App\Models\Menus::select('id')->get();
        foreach ($model as $menu) {
            array_push($menuIds, $menu->id);
        }

        return json_encode($menuIds);
    }

    protected function getAllPrivilegeIds()
    {
        $privIds = [];

        $model = \App\Models\Privileges::select('id')->get();
        foreach ($model as $privilege) {
            array_push($privIds, $privilege->id);
        }

        return json_encode($privIds);
    }
}
