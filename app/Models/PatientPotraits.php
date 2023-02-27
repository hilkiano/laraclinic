<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientPotraits extends Model
{
    protected $table = "patient_potraits";

    protected $fillable = [
        'patient_id',
        'url'
    ];

    protected $casts = [
        'url'   => 'array'
    ];
}
