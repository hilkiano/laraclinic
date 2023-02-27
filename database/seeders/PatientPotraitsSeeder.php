<?php

namespace Database\Seeders;

use App\Models\PatientPotraits;
use App\Models\Patients;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PatientPotraitsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table("patient_potraits")->truncate();
        // json
        $uploadIds = Storage::disk('local')->get('upload_ids.json');
        $uploadIdsArr = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $uploadIds), true);
        $uploadFile = Storage::disk('local')->get('upload_file.json');
        $uploadFileArr = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $uploadFile), true);
        $names = Storage::disk('local')->get('names.json');
        $namesArr = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $names), true);

        $patients = Patients::all();

        foreach ($patients as $patient) {
            if ($patient->id !== 1) {
                $name = Arr::where($namesArr, function ($n) use ($patient) {
                    return $n["name"] === $patient->name;
                });
                if (count($name) > 0) {
                    $first = Arr::first($name);
                    $ids = Arr::where($uploadIdsArr, function ($uid) use ($first) {
                        return $uid["nameid"] === $first["id"];
                    });

                    if (count($ids) > 0) {
                        $collect = collect($ids);
                        $arrLink = [];
                        foreach ($collect->sortBy('uploadfileid') as $uploadId) {
                            $fileName = Arr::where($uploadFileArr, function ($file) use ($uploadId) {
                                return $file["id"] === $uploadId["uploadfileid"];
                            });
                            $url = "https://klinikmichellebucket.s3.ap-southeast-3.amazonaws.com/patients/" . Arr::first($fileName)["filename"];
                            array_push($arrLink, $url);
                        }
                        // Save new PatientPotraits
                        $newModel = new PatientPotraits();
                        $newModel->patient_id = $patient->id;
                        $newModel->url = $arrLink;
                        $newModel->save();
                    }
                }
            }
        }
    }
}
