<?php

namespace Database\Seeders;

use App\Models\Privileges;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PrivilegesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('privileges')->truncate();
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
            [
                'name'  => 'PATIENT_REGISTER',
                'description'   => 'Ability to register a patient'
            ],
            [
                'name'  => 'PATIENT_ASSIGNMENT',
                'description'   => 'Ability to make an appointment for a patient'
            ],
            [
                'name'  => 'PATIENT_SEARCH',
                'description'   => 'Ability to search a patient'
            ],
            [
                'name'  => 'PATIENT_LIST',
                'description'   => 'Ability to view patients list'
            ],
            [
                'name'  => 'PATIENT_DETAIL_INFO',
                'description'   => 'Ability to view patient details'
            ],
            [
                'name'  => 'PATIENT_MEDICAL_HISTORY_CREATE',
                'description'   => 'Ability to create patient medical history'
            ],
            [
                'name'  => 'PATIENT_MEDICAL_HISTORY_VIEW',
                'description'   => 'Ability to view patient medical history'
            ],
            [
                'name'  => 'PATIENT_PRESCRIPTION_VIEW',
                'description'   => 'Ability to view patient prescription'
            ],
            [
                'name'  => 'PATIENT_PRESCRIPTION_CREATE',
                'description'   => 'Ability to create patient prescription'
            ],
            [
                'name'  => 'PATIENT_PRESCRIPTION_UPDATE',
                'description'   => 'Ability to update patient prescription'
            ],
            [
                'name'  => 'PATIENT_PRESCRIPTION_CHECKOUT',
                'description'   => 'Ability to checkout patient prescription'
            ],
            [
                'name'  => 'MEDICINE_SERVICE_LIST',
                'description'   => 'Ability to view medicines and services'
            ],
            [
                'name'  => 'MEDICINE_SERVICE_CREATE',
                'description'   => 'Ability to create medicine'
            ],
            [
                'name'  => 'MEDICINE_SERVICE_UPDATE',
                'description'   => 'Ability to update medicine'
            ],
            [
                'name'  => 'MEDICINE_SERVICE_DELETE',
                'description'   => 'Ability to delete medicine'
            ],
            [
                'name'  => 'ONLINE_TRX_CREATE',
                'description'   => 'Ability to record online transaction'
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
