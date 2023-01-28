<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visits extends Model
{
    protected $table = "visits";

    protected $fillable = [
        'patient_id',
        'visit_time',
        'visit_reason',
        'additional_note'
    ];
}
