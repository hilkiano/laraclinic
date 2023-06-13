<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Carbon;

class Prescription extends Model
{
    protected $table = 'prescriptions';

    protected $fillable = [
        'appointment_uuid',
        'patient_id',
        'list',
        'additional_info',
        'source',
        'transaction_id'
    ];

    protected $casts = [
        'list' => 'array'
    ];

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

    public function medicalRecord()
    {
        return $this->belongsTo(MedicalRecord::class, 'appointment_uuid', 'appointment_uuid');
    }

    public function patient()
    {
        return $this->belongsTo(Patients::class, 'patient_id', 'id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id', 'id');
    }
}
