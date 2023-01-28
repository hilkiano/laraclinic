<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patients extends Model
{
    use HasFactory;

    protected $table = "patients";

    protected $fillable = [
        'name',
        'birth_date',
        'address',
        'phone_number',
        'weight',
        'height',
        'additional_note'
    ];

    public function patientPotrait()
    {
        return $this->hasMany(PatientPotraits::class, 'patient_id', 'id')->orderBy('created_at', 'desc');
    }
}
