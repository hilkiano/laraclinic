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
        'additional_note',
        'daily_code'
    ];

    public function patient()
    {
        return $this->belongsTo(Patients::class, 'patient_id', 'id');
    }

    public function detail()
    {
        return $this->hasMany(AppointmentsDetail::class, 'appointment_uuid', 'uuid');
    }

    public function medicalRecord()
    {
        return $this->hasOne(MedicalRecord::class, 'appointment_uuid', 'uuid');
    }

    public function prescription()
    {
        return $this->hasOne(Prescription::class, 'appointment_uuid', 'uuid');
    }
}
