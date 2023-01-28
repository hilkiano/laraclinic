<?php

namespace Database\Seeders;

use App\Models\Menus;
use Illuminate\Database\Seeder;

class MenusSeeder extends Seeder
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
                'route'     => null,
                'is_parent' => true,
                'parent'    => null,
                'order'     => 2
            ],
            [
                'name'      => 'patients-list',
                'label'     => 'List',
                'icon'      => 'bi-person-lines-fill',
                'route'     => '/patient/list',
                'is_parent' => false,
                'parent'    => 'patients',
                'order'     => 1
            ],
            [
                'name'      => 'patients-form',
                'label'     => 'Form',
                'icon'      => 'bi-person-add',
                'route'     => '/patient/register',
                'is_parent' => false,
                'parent'    => 'patients',
                'order'     => 2
            ],
            // Doctors Menu
            [
                'name'      => 'appointments',
                'label'     => 'Appointments',
                'icon'      => 'bi-clock-fill',
                'route'     => '/appointments',
                'is_parent' => false,
                'parent'    => null,
                'order'     => 3
            ]
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
