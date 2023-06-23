<?php

namespace Database\Seeders;

use App\Models\Patients;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;
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
                $patient->code = $value["code"];
                $phone = null;
                if ($value["phone1"] !== "NULL") {
                    $phone = (int) $value["phone1"];
                } elseif ($value["phone2"] !== "NULL") {
                    $phone = (int) $value["phone2"];
                }
                $patient->address      = $this->getAddress($value["id"]);
                $patient->phone_number = $phone;
                $patient->email        = $value["email"] !== "NULL" ? $value["email"] : null;
                $patient->created_at   = Carbon::createFromFormat("Y-m-d H:i:s", $value["createdate"], env("APP_TIME_ZONE"))->utc();
                $patient->save();
            }
        }
    }

    private function getAddress(int $nameId)
    {
        $result = "";

        $address = Storage::disk('local')->get('address.json');
        $namesAddress = Storage::disk('local')->get('patient_address.json');
        $addressArr = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $address), true);
        $namesAddressArr = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $namesAddress), true);

        $addressIdArr = Arr::where($namesAddressArr, function ($n) use ($nameId) {
            return $n["nameid"] === $nameId;
        });

        if (count($addressIdArr) > 0) {
            $addressId = Arr::first($addressIdArr)["addressid"];
            $addressArr = Arr::where($addressArr, function ($n) use ($addressId) {
                return $n["id"] === $addressId;
            });

            if (count($addressArr) > 0) {
                $resultArr = Arr::first($addressArr);
                $result = $resultArr["line1"];
                $result .= $resultArr["line2"] ? ", " . $resultArr["line2"] : "";
                $result .= $resultArr["city"] ? ", " . $resultArr["city"] : "";
                $result .= $resultArr["province"] ? ", " . $resultArr["province"] : "";
                $result .= $resultArr["country"] ? ", " . $resultArr["country"] : "";
                $result .= $resultArr["zipcode"] ? ", " . $resultArr["zipcode"] : "";
            }
        }

        return $result;
    }
}
