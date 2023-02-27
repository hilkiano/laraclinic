<?php

namespace Database\Seeders;

use App\Models\Medicine;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class MedicinesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('medicines')->truncate();
        $items = Storage::disk('local')->get('items.json');
        $itemsArr = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $items), true);
        $pricelist = Storage::disk('local')->get('pricelist.json');
        $pricelistArr = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $pricelist), true);

        $filtered = Arr::where($pricelistArr, function ($value, $key) {
            return $value["itemid"] == 2;
        });

        foreach ($itemsArr as $key => $value) {
            if ($value["types"] == "2" && $value["activestatus"] == "Y") {
                $med = new Medicine();
                $med->label         = $value["name"];
                $med->package       = $value["package"] === "NULL" ? null : $value["package"];
                // Get price
                $filtered = Arr::where($pricelistArr, function ($v, $k) use ($value) {
                    return $v["itemid"] == $value["id"];
                });
                $med->sell_price    = $filtered ? (int) Arr::first($filtered)["price"] : null;
                $med->save();
            }
        }
    }
}
