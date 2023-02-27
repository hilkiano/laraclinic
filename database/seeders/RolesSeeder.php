<?php

namespace Database\Seeders;

use App\Models\Roles;
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
                'menu_ids'      => $this->getAllMenuIds(),
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

        $model = \App\Models\Menus::select('id')->find([1, 8, 9, 10, 11, 12]);
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

        $model = \App\Models\Menus::select('id')->find([1, 8, 9, 10, 11, 13]);
        foreach ($model as $menu) {
            array_push($menuIds, $menu->id);
        }

        return $menuIds;
    }

    protected function getDoctorPrivilegeIds()
    {
        $privIds = [];

        $model = \App\Models\Privileges::select('id')->get();
        foreach ($model as $privilege) {
            array_push($privIds, $privilege->id);
        }

        return $privIds;
    }

    protected function getPharmacistMenuIds()
    {
        $menuIds = [];

        $model = \App\Models\Menus::select('id')->find([1, 8, 9, 10]);
        foreach ($model as $menu) {
            array_push($menuIds, $menu->id);
        }

        return $menuIds;
    }

    protected function getPharmacistPrivilegeIds()
    {
        $privIds = [];

        $model = \App\Models\Privileges::select('id')->get();
        foreach ($model as $privilege) {
            array_push($privIds, $privilege->id);
        }

        return $privIds;
    }

    protected function getCashierMenuIds()
    {
        $menuIds = [];

        $model = \App\Models\Menus::select('id')->find([1, 8, 9, 10]);
        foreach ($model as $menu) {
            array_push($menuIds, $menu->id);
        }

        return $menuIds;
    }

    protected function getCashierPrivilegeIds()
    {
        $privIds = [];

        $model = \App\Models\Privileges::select('id')->get();
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

    protected function getOwnerPrivilegeIds()
    {
        $privIds = [];

        // $model = \App\Models\Privileges::select('id')->find();
        // foreach ($model as $privilege) {
        //     array_push($privIds, $privilege->id);
        // }

        return $privIds;
    }
}
