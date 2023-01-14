<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        \App\Models\Groups::insert([
            [
                'name'      => 'Super Admin Group',
                'role_ids'  => json_encode([1])
            ]
        ]);
    }
}
