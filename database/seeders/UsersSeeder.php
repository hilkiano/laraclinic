<?php

namespace Database\Seeders;

use App\Models\Users;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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
        DB::table('users')->truncate();

        Users::create([
            'name'          => 'Super Admin',
            'email'         => 'testing@mail.com',
            'username'      => 'superadmin',
            'password'      => Hash::make('superadmin'),
            'phone_number'  => '81211112222',
            'group_id'      => 1
        ]);

        // Receptionist
        Users::create([
            'name'          => 'Adam',
            'email'         => '',
            'username'      => 'adam',
            'password'      => Hash::make('12345'),
            'phone_number'  => '',
            'group_id'      => 2
        ]);
        Users::create([
            'name'          => 'Agnes',
            'email'         => '',
            'username'      => 'agnes',
            'password'      => Hash::make('12345'),
            'phone_number'  => '',
            'group_id'      => 2
        ]);

        // Doctor
        Users::create([
            'name'          => 'Dr. Herlina',
            'email'         => '',
            'username'      => 'dr_herlina',
            'password'      => Hash::make('12345'),
            'phone_number'  => '',
            'group_id'      => 3
        ]);
        Users::create([
            'name'          => 'Dr. Sheila',
            'email'         => '',
            'username'      => 'dr_sheila',
            'password'      => Hash::make('12345'),
            'phone_number'  => '',
            'group_id'      => 3
        ]);
        Users::create([
            'name'          => 'Dr. Astri',
            'email'         => '',
            'username'      => 'dr_astri',
            'password'      => Hash::make('12345'),
            'phone_number'  => '',
            'group_id'      => 3
        ]);

        // Pharmacy
        Users::create([
            'name'          => 'Reni',
            'email'         => '',
            'username'      => 'reni',
            'password'      => Hash::make('12345'),
            'phone_number'  => '',
            'group_id'      => 4
        ]);
        Users::create([
            'name'          => 'Rani',
            'email'         => '',
            'username'      => 'rani',
            'password'      => Hash::make('12345'),
            'phone_number'  => '',
            'group_id'      => 4
        ]);

        // Cashier
        Users::create([
            'name'          => 'Yune',
            'email'         => '',
            'username'      => 'yune',
            'password'      => Hash::make('12345'),
            'phone_number'  => '',
            'group_id'      => 5
        ]);
        Users::create([
            'name'          => 'Priska',
            'email'         => '',
            'username'      => 'priska',
            'password'      => Hash::make('12345'),
            'phone_number'  => '',
            'group_id'      => 5
        ]);
        Users::create([
            'name'          => 'Arni',
            'email'         => '',
            'username'      => 'arni',
            'password'      => Hash::make('12345'),
            'phone_number'  => '',
            'group_id'      => 5
        ]);

        // Online Shop
        Users::create([
            'name'          => 'Claudia',
            'email'         => '',
            'username'      => 'claudia',
            'password'      => Hash::make('12345'),
            'phone_number'  => '',
            'group_id'      => 6
        ]);
        Users::create([
            'name'          => 'Rosa',
            'email'         => '',
            'username'      => 'rosa',
            'password'      => Hash::make('12345'),
            'phone_number'  => '',
            'group_id'      => 6
        ]);
        Users::create([
            'name'          => 'Cindy',
            'email'         => '',
            'username'      => 'cindy',
            'password'      => Hash::make('12345'),
            'phone_number'  => '',
            'group_id'      => 6
        ]);
        Users::create([
            'name'          => 'Ryan',
            'email'         => '',
            'username'      => 'ryan',
            'password'      => Hash::make('12345'),
            'phone_number'  => '',
            'group_id'      => 6
        ]);
        Users::create([
            'name'          => 'Suhono',
            'email'         => '',
            'username'      => 'suhono',
            'password'      => Hash::make('12345'),
            'phone_number'  => '',
            'group_id'      => 6
        ]);
    }
}
