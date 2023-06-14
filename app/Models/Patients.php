<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patients extends Model
{

    protected $table = "patients";

    protected $fillable = [
        'name',
        'birth_date',
        'address',
        'phone_number',
        'email',
        'weight',
        'height',
        'additional_note',
        'deleted_at',
        'deleted_by'
    ];

    public function patientPotrait()
    {
        return $this->hasOne(PatientPotraits::class, 'patient_id', 'id')->orderBy('created_at', 'desc');
    }

    public function appointments()
    {
        return $this->hasMany(Appointments::class, 'patient_id', 'id')->orderBy('created_at', 'desc');
    }

    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class, 'patient_id', 'id')->orderBy('created_at', 'desc');
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class, 'patient_id', 'id')->orderBy('created_at', 'desc');
    }
}
