<?php

namespace Database\Seeders;

use App\Models\Users;
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
        Users::factory(19)->create();

        Users::create([
            'name'          => 'Super Admin',
            'email'         => 'testing@mail.com',
            'username'      => 'superadmin',
            'password'      => Hash::make('superadmin'),
            'phone_number'  => '081211112222',
            'group_id'      => 1
        ]);
    }
}
