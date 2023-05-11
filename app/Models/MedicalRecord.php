<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Carbon;

class MedicalRecord extends Model
{
    protected $table = 'medical_records';

    protected $fillable = [
        'appointment_uuid',
        'record_no',
        'patient_id',
        'prescription_id',
        'additional_note'
    ];

    public function patient()
    {
        return $this->belongsTo(Patients::class, 'patient_id', 'id');
    }

    public function prescription()
    {
        return $this->belongsTo(Prescription::class, 'prescription_id', 'id');
    }

    public function createdAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Carbon::make($value)->setTimezone(env('APP_TIME_ZONE'))->isoFormat('DD MMMM YYYY HH:mm:ss')
        );
    }

    public function updatedAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Carbon::make($value)->setTimezone(env('APP_TIME_ZONE'))->isoFormat('DD MMMM YYYY HH:mm:ss')
        );
    }

    public function createdBy(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Users::select('name')->where('id', $value)->first()->name
        );
    }

    public function updatedBy()
    {
        return $this->belongsTo(Users::class, 'updated_by', 'id');
    }

    public function deletedBy()
    {
        return $this->belongsTo(Users::class, 'deleted_by', 'id');
    }
}
