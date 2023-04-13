<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'transactions';
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = [
        'appointment_uuid',
        'patient_id',
        'prescription',
        'payment_type',
        'total_amount',
        'payment_amount',
        'change',
        'discount_type',
        'discount_amount',
        'additional_info'
    ];

    protected $casts = [
        'prescription' => 'array',
        'additional_info' => 'array'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            do {
                $id = str_pad(mt_rand(1, 999999999999999), 15, '0', STR_PAD_LEFT);
            } while (static::where('id', $id)->exists());

            $model->id = $id;
        });
    }

    public function appointment()
    {
        return $this->belongsTo(Appointments::class, 'appoinment_uuid', 'uuid');
    }

    public function prescription()
    {
        return $this->belongsTo(Prescription::class, 'prescription_id', 'id');
    }
}
