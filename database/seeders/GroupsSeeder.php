<?php

namespace Database\Seeders;

use App\Models\Groups;
use Illuminate\Database\Seeder;

class GroupsSeeder extends Seeder
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
                'name'      => 'Super Admin Group',
                'role_ids'  => [1]
            ],
            [
                'name'      => 'Receptionist Group',
                'role_ids'  => [2]
            ],
            [
                'name'      => 'Doctor Group',
                'role_ids'  => [3]
            ],
            [
                'name'      => 'Pharmacist Group',
                'role_ids'  => [4]
            ],
            [
                'name'      => 'Cashier Group',
                'role_ids'  => [5]
            ],
            [
                'name'      => 'Online Shop Admin Group',
                'role_ids'  => [6]
            ]
        ];

        foreach ($data as $data) {
            Groups::create([
                'name'      => $data["name"],
                'role_ids'  => $data["role_ids"]
            ]);
        }
    }
}
