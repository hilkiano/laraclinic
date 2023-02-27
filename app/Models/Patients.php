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
        'additional_note'
    ];

    public function patientPotrait()
    {
        return $this->hasOne(PatientPotraits::class, 'patient_id', 'id')->orderBy('created_at', 'desc');
    }
}
