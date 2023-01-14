<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Users::factory(100)->create();

        \App\Models\Users::insert([
            [
                'name'          => 'Super Admin',
                'email'         => 'testing@mail.com',
                'username'      => 'superadmin',
                'password'      => Hash::make('superadmin'),
                'phone_number'  => '081211112222',
                'group_id'      => 1
            ]
        ]);
    }
}
