<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'transactions';

    protected $fillable = [
        'appointment_uuid',
        'prescription_id',
        'payment_type',
        'additional_info'
    ];

    protected $casts = [
        'additional_info' => 'array'
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointments::class, 'appoinment_uuid', 'uuid');
    }

    public function prescription()
    {
        return $this->belongsTo(Prescription::class, 'prescription_id', 'id');
    }
}
