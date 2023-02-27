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
            'phone_number'  => '81211112222',
            'group_id'      => 1
        ]);

        Users::create([
            'name'          => 'Demo Receptionist',
            'email'         => 'testing2@mail.com',
            'username'      => 'receptionist',
            'password'      => Hash::make('12345'),
            'phone_number'  => '81211112222',
            'group_id'      => 2
        ]);

        Users::create([
            'name'          => 'Demo Doctor',
            'email'         => 'testing3g@mail.com',
            'username'      => 'doctor',
            'password'      => Hash::make('12345'),
            'phone_number'  => '81211112222',
            'group_id'      => 3
        ]);

        Users::create([
            'name'          => 'Demo Pharmacist',
            'email'         => 'testing4@mail.com',
            'username'      => 'pharmacist',
            'password'      => Hash::make('12345'),
            'phone_number'  => '81211112222',
            'group_id'      => 4
        ]);

        Users::create([
            'name'          => 'Demo Cashier',
            'email'         => 'testing5@mail.com',
            'username'      => 'cashier',
            'password'      => Hash::make('12345'),
            'phone_number'  => '81211112222',
            'group_id'      => 5
        ]);

        Users::create([
            'name'          => 'Demo Olshop Admin',
            'email'         => 'testing6@mail.com',
            'username'      => 'olshop',
            'password'      => Hash::make('12345'),
            'phone_number'  => '81211112222',
            'group_id'      => 6
        ]);
    }
}
