<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
