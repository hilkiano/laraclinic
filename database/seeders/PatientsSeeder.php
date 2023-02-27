<?php

namespace Database\Seeders;

use App\Models\Patients;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PatientsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('patients')->truncate();
        $names = Storage::disk('local')->get('names.json');
        $namesArr = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $names), true);

        foreach ($namesArr as $key => $value) {
            if ($value["activestatus"] == "Y") {
                $patient = new Patients();
                $patient->name = $value["name"];
                $phone = null;
                if ($value["phone1"] !== "NULL") {
                    $phone = (int) $value["phone1"];
                } elseif ($value["phone2"] !== "NULL") {
                    $phone = (int) $value["phone2"];
                }
                $patient->phone_number = $phone;
                $patient->email        = $value["email"] !== "NULL" ? $value["email"] : null;
                $patient->created_at   = Carbon::createFromFormat("Y-m-d H:i:s", $value["createdate"], env("APP_TIME_ZONE"))->utc();
                $patient->save();
            }
        }
    }
}
