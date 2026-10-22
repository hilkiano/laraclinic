<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Carbon;

class AppointmentsDetail extends Model
{
    protected $table = "appointments_details";

    protected $fillable = [
        'appointment_uuid',
        'status',
        'pic',
        'additional_note'
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointments::class, 'uuid', 'appointment_uuid');
    }

    public function createdAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Carbon::make($value)
                ->setTimezone(env('APP_TIME_ZONE'))
                ->isoFormat('DD MMMM YYYY HH:mm:ss')
        );
    }

    public function updatedAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Carbon::make($value)
                ->setTimezone(env('APP_TIME_ZONE'))
                ->isoFormat('DD MMMM YYYY HH:mm:ss')
        );
    }

    public function createdBy()
    {
        return $this->belongsTo(Users::class, 'created_by', 'id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(Users::class, 'updated_by', 'id');
    }
}
