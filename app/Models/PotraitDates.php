<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PotraitDates extends Model
{
    protected $table = "potrait_dates";

    public $timestamps = false;

    protected $fillable = [
        'url',
        'upload_date',
    ];
}
