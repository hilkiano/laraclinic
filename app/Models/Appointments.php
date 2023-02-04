<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointments extends Model
{
    protected $table = "appointments";

    protected $fillable = [
        'uuid',
        'patient_id',
        'visit_time',
        'visit_reason',
        'status',
        'additional_note'
    ];

    public function patient()
    {
        return $this->belongsTo(Patients::class, 'patient_id', 'id');
    }
}
