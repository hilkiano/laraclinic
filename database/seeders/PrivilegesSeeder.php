<?php

namespace Database\Seeders;

use App\Models\Privileges;
use Illuminate\Database\Seeder;

class PrivilegesSeeder extends Seeder
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
                'name'  => 'CREATE_USER',
                'description'   => 'Ability to create new user'
            ],
            [
                'name'  => 'UPDATE_USER',
                'description'   => 'Ability to update new user'
            ],
            [
                'name'  => 'DELETE_USER',
                'description'   => 'Ability to delete new user'
            ],
            [
                'name'  => 'CREATE_MENU',
                'description'   => 'Ability to create new menu'
            ],
            [
                'name'  => 'UPDATE_MENU',
                'description'   => 'Ability to update new menu'
            ],
            [
                'name'  => 'DELETE_MENU',
                'description'   => 'Ability to delete new menu'
            ],
            [
                'name'  => 'CREATE_ROLE',
                'description'   => 'Ability to create new role'
            ],
            [
                'name'  => 'UPDATE_ROLE',
                'description'   => 'Ability to update new role'
            ],
            [
                'name'  => 'DELETE_ROLE',
                'description'   => 'Ability to delete new role'
            ],
            [
                'name'  => 'CREATE_GROUP',
                'description'   => 'Ability to create new group'
            ],
            [
                'name'  => 'UPDATE_GROUP',
                'description'   => 'Ability to update new group'
            ],
            [
                'name'  => 'DELETE_GROUP',
                'description'   => 'Ability to delete new group'
            ],
        ];

        foreach ($data as $data) {
            Privileges::create([
                'name'          => $data["name"],
                'description'   => $data["description"]
            ]);
        }
    }
}
