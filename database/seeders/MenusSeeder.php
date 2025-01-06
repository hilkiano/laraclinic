<?php

namespace Database\Seeders;

use App\Models\Menus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('menus')->truncate();
        $data = [
            [
                'name'      => 'dashboard',
                'label'     => 'Dashboard',
                'icon'      => 'bi-house',
                'route'     => '/',
                'is_parent' => false,
                'parent'    => null,
                'order'     => 1
            ],
            [
                'name'      => 'master-data',
                'label'     => 'Master Data',
                'icon'      => 'bi-database',
                'route'     => null,
                'is_parent' => true,
                'parent'    => null,
                'order'     => 99
            ],
            [
                'name'      => 'users',
                'label'     => 'Users',
                'icon'      => 'bi-person',
                'route'     => '/master/users',
                'is_parent' => false,
                'parent'    => 'master-data',
                'order'     => 1
            ],
            [
                'name'      => 'groups',
                'label'     => 'Groups',
                'icon'      => 'bi-people',
                'route'     => '/master/groups',
                'is_parent' => false,
                'parent'    => 'master-data',
                'order'     => 2
            ],
            [
                'name'      => 'roles',
                'label'     => 'Roles',
                'icon'      => 'bi-gear',
                'route'     => '/master/roles',
                'is_parent' => false,
                'parent'    => 'master-data',
                'order'     => 3
            ],
            [
                'name'      => 'privileges',
                'label'     => 'Privileges',
                'icon'      => 'bi-key-fill',
                'route'     => '/master/privileges',
                'is_parent' => false,
                'parent'    => 'master-data',
                'order'     => 4
            ],
            [
                'name'      => 'menus',
                'label'     => 'Menus',
                'icon'      => 'bi-menu-app',
                'route'     => '/master/menus',
                'is_parent' => false,
                'parent'    => 'master-data',
                'order'     => 5
            ],
            // Patients Menu
            [
                'name'      => 'patients',
                'label'     => 'Patients',
                'icon'      => 'bi-person',
                'route'     => '/patient/list',
                'is_parent' => false,
                'parent'    => null,
                'order'     => 2
            ],
            // Appointment Menu
            [
                'name'      => 'appointments',
                'label'     => 'Appointments',
                'icon'      => 'bi-clipboard-fill',
                'route'     => null,
                'is_parent' => true,
                'parent'    => null,
                'order'     => 3
            ],
            [
                'name'      => 'appointments-list',
                'label'     => 'List',
                'icon'      => 'bi-clipboard-plus-fill',
                'route'     => '/appointments/list',
                'is_parent' => false,
                'parent'    => 'appointments',
                'order'     => 1
            ],
            [
                'name'      => 'appointments-assignment',
                'label'     => 'My Assignment',
                'icon'      => 'bi-clipboard-check-fill',
                'route'     => '/appointments/assignment',
                'is_parent' => false,
                'parent'    => 'appointments',
                'order'     => 2
            ],
            // Medicines Menu
            [
                'name'      => 'medicines',
                'label'     => 'Medicines',
                'icon'      => 'bi-capsule',
                'route'     => '/medicines',
                'is_parent' => false,
                'parent'    => null,
                'order'     => 4
            ],
            // Services Menu
            [
                'name'      => 'services',
                'label'     => 'Services',
                'icon'      => 'bi-hand-thumbs-up',
                'route'     => '/services',
                'is_parent' => false,
                'parent'    => null,
                'order'     => 5
            ],
            // Transaction Menu
            [
                'name'      => 'transactions',
                'label'     => 'Transactions',
                'icon'      => 'bi-cash-coin',
                'route'     => '/transactions',
                'is_parent' => false,
                'parent'    => null,
                'order'     => 6
            ],
            [
                'name'      => 'cashier',
                'label'     => 'Cashier',
                'icon'      => 'bi-wallet2',
                'route'     => '/cashier',
                'is_parent' => false,
                'parent'    => null,
                'order'     => 7
            ],
            [
                'name'      => 'online-trx',
                'label'     => 'Online Transaction',
                'icon'      => 'bi-globe2',
                'route'     => '/online-trx',
                'is_parent' => false,
                'parent'    => null,
                'order'     => 8
            ],
            // Stock
            [
                'name'      => 'stocks',
                'label'     => 'Stocks',
                'icon'      => 'bi-box2',
                'route'     => '/stocks',
                'is_parent' => false,
                'parent'    => null,
                'order'     => 9
            ],
        ];

        foreach ($data as $data) {
            Menus::create([
                'name'      => $data["name"],
                'label'     => $data["label"],
                'icon'      => $data["icon"],
                'route'     => $data["route"],
                'is_parent' => $data["is_parent"],
                'parent'    => $data["parent"],
                'order'     => $data["order"],
            ]);
        }
    }
}
