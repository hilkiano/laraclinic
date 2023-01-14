<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        \App\Models\Menus::insert([
            [
                'name'      => 'dashboard',
                'label'     => 'Dashboard',
                'icon'      =>  'bi-house',
                'route'     => '/',
                'is_parent' => false,
                'parent'    => null,
                'order'     => 1
            ],
            [
                'name'      => 'master-data',
                'label'     => 'Master Data',
                'icon'      =>  'bi-database',
                'route'     => null,
                'is_parent' => true,
                'parent'    => null,
                'order'     => 99
            ],
            [
                'name'      => 'users',
                'label'     => 'Users',
                'icon'      =>  'bi-person',
                'route'     => '/master/users',
                'is_parent' => false,
                'parent'    => 'master-data',
                'order'     => 1
            ],
            [
                'name'      => 'groups',
                'label'     => 'Groups',
                'icon'      =>  'bi-people',
                'route'     => '/master/groups',
                'is_parent' => false,
                'parent'    => 'master-data',
                'order'     => 2
            ],
            [
                'name'      => 'roles',
                'label'     => 'Roles',
                'icon'      =>  'bi-gear',
                'route'     => '/master/roles',
                'is_parent' => false,
                'parent'    => 'master-data',
                'order'     => 3
            ],
            [
                'name'      => 'privileges',
                'label'     => 'Privileges',
                'icon'      =>  'bi-key-fill',
                'route'     => '/master/privileges',
                'is_parent' => false,
                'parent'    => 'master-data',
                'order'     => 4
            ],
            [
                'name'      => 'menus',
                'label'     => 'Menus',
                'icon'      =>  'bi-menu-app',
                'route'     => '/master/menus',
                'is_parent' => false,
                'parent'    => 'master-data',
                'order'     => 5
            ]
        ]);
    }
}
