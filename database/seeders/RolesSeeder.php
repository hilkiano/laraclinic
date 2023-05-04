<?php

namespace Database\Seeders;

use App\Models\Roles;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->truncate();
        $data = [
            [
                'name'          => 'Super Admin',
                'menu_ids'      => $this->getAllMenuIds(),
                'privilege_ids' => $this->getAllPrivilegeIds()
            ],
            [
                'name'          => 'Receptionist',
                'menu_ids'      => $this->getReceptionistMenuIds(),
                'privilege_ids' => $this->getReceptionistPrivilegeIds()
            ],
            [
                'name'          => 'Doctor',
                'menu_ids'      => $this->getDoctorMenuIds(),
                'privilege_ids' => $this->getDoctorPrivilegeIds()
            ],
            [
                'name'          => 'Pharmacist',
                'menu_ids'      => $this->getPharmacistMenuIds(),
                'privilege_ids' => $this->getPharmacistPrivilegeIds()
            ],
            [
                'name'          => 'Cashier',
                'menu_ids'      => $this->getCashierMenuIds(),
                'privilege_ids' => $this->getCashierPrivilegeIds()
            ],
            [
                'name'          => 'Online Shop Admin',
                'menu_ids'      => $this->getOlShopAdminMenuIds(),
                'privilege_ids' => $this->getOlShopAdminPrivilegeIds()
            ],
            [
                'name'          => 'Owner',
                'menu_ids'      => $this->getOwnerMenuIds(),
                'privilege_ids' => $this->getOwnerPrivilegeIds()
            ],
        ];

        foreach ($data as $data) {
            Roles::create([
                'name'          => $data["name"],
                'menu_ids'      => $data["menu_ids"],
                'privilege_ids' => $data["privilege_ids"]
            ]);
        }
    }

    protected function getAllMenuIds()
    {
        $menuIds = [];

        $model = \App\Models\Menus::select('id')->get();
        foreach ($model as $menu) {
            array_push($menuIds, $menu->id);
        }

        return $menuIds;
    }

    protected function getAllPrivilegeIds()
    {
        $privIds = [];

        $model = \App\Models\Privileges::select('id')->get();
        foreach ($model as $privilege) {
            array_push($privIds, $privilege->id);
        }

        return $privIds;
    }

    protected function getReceptionistMenuIds()
    {
        $menuIds = [];

        $model = \App\Models\Menus::select('id')->find([1, 8, 9, 10]);
        foreach ($model as $menu) {
            array_push($menuIds, $menu->id);
        }

        return $menuIds;
    }

    protected function getReceptionistPrivilegeIds()
    {
        $privIds = [];

        $model = \App\Models\Privileges::select('id')->find([13, 14, 15, 16, 17]);
        foreach ($model as $privilege) {
            array_push($privIds, $privilege->id);
        }

        return $privIds;
    }

    protected function getDoctorMenuIds()
    {
        $menuIds = [];

        $model = \App\Models\Menus::select('id')->find([1, 8, 9, 11]);
        foreach ($model as $menu) {
            array_push($menuIds, $menu->id);
        }

        return $menuIds;
    }

    protected function getDoctorPrivilegeIds()
    {
        $privIds = [];

        $model = \App\Models\Privileges::select('id')->find([15, 16, 17, 18, 19, 20, 21, 22, 24]);
        foreach ($model as $privilege) {
            array_push($privIds, $privilege->id);
        }

        return $privIds;
    }

    protected function getPharmacistMenuIds()
    {
        $menuIds = [];

        $model = \App\Models\Menus::select('id')->find([1, 9, 11, 12, 13]);
        foreach ($model as $menu) {
            array_push($menuIds, $menu->id);
        }

        return $menuIds;
    }

    protected function getPharmacistPrivilegeIds()
    {
        $privIds = [];

        $model = \App\Models\Privileges::select('id')->find([15, 16, 17, 18, 19, 20, 21, 22, 24, 25, 26, 27]);
        foreach ($model as $privilege) {
            array_push($privIds, $privilege->id);
        }

        return $privIds;
    }

    protected function getCashierMenuIds()
    {
        $menuIds = [];

        $model = \App\Models\Menus::select('id')->find([1, 14, 15]);
        foreach ($model as $menu) {
            array_push($menuIds, $menu->id);
        }

        return $menuIds;
    }

    protected function getCashierPrivilegeIds()
    {
        $privIds = [];

        $model = \App\Models\Privileges::select('id')->find([17, 19, 20, 23, 24]);
        foreach ($model as $privilege) {
            array_push($privIds, $privilege->id);
        }

        return $privIds;
    }

    protected function getOlShopAdminMenuIds()
    {
        $menuIds = [];

        $model = \App\Models\Menus::select('id')->find([1, 8, 9, 10]);
        foreach ($model as $menu) {
            array_push($menuIds, $menu->id);
        }

        return $menuIds;
    }

    protected function getOlShopAdminPrivilegeIds()
    {
        $privIds = [];

        $model = \App\Models\Privileges::select('id')->get();
        foreach ($model as $privilege) {
            array_push($privIds, $privilege->id);
        }

        return $privIds;
    }

    protected function getOwnerMenuIds()
    {
        $menuIds = [];

        $model = \App\Models\Menus::select('id')->find([1, 2, 3, 8, 9, 10, 12, 13, 14]);
        foreach ($model as $privilege) {
            array_push($menuIds, $privilege->id);
        }

        return $menuIds;
    }

    protected function getOwnerPrivilegeIds()
    {
        $privIds = [];

        $model = \App\Models\Privileges::select('id')->find([1, 2, 3, 16, 19, 20, 24]);
        foreach ($model as $privilege) {
            array_push($privIds, $privilege->id);
        }

        return $privIds;
    }
}
