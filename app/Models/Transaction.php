<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Transaction extends Model
{
    use SoftDeletes;

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
        'additional_info',
        'source',
        'payment_details'
    ];

    protected $casts = [
        'prescription' => 'array',
        'additional_info' => 'array',
        'payment_details' => 'array'
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

    public function paymentType(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->getPaymentType($value)
        );
    }

    public function totalAmount(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->moneyFormat($value)
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

    private function getPaymentType($val): string
    {
        switch ($val) {
            case 'CASH':
                return 'Cash';
                break;
            case 'CREDIT_CARD':
                return 'Credit Card';
                break;
            case 'DEBIT_CARD':
                return 'Debit Card';
                break;
            case 'BANK_TRANSFER':
                return 'Transfer Bank';
                break;

            default:
                return 'Unknown';
                break;
        }
    }

    private function moneyFormat($val): string
    {
        $number = (float)$val;
        $formatted = number_format($number, 0, ',', '.');
        return $result = "Rp. " . $formatted;
    }
}
