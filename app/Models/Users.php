<?php

namespace App\Models;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class Users extends Authenticatable implements JWTSubject
{
    use HasFactory, SoftDeletes;

    protected $table = 'users';

    protected $fillable = [
        'username',
        'password',
        'name',
        'email',
        'phone_number',
        'email_verified_at',
        'otp',
        'otp_timeout',
        'remember_token',
        'group_id',
        'extended_login'
    ];

    protected $hidden = [
        'password',
        'remember_token'
    ];

    protected $casts = [
        'configs' => 'array'
    ];

    /**
     * getJWTIdentifier
     *
     * @return void
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * getJWTCustomClaims
     *
     * @return void
     */
    public function getJWTCustomClaims()
    {
        if ($this->can('use-extended-time')) {
            $expiration = Carbon::now('UTC')->addYears(2)->getTimestamp();
            return ['exp' => $expiration];
        }
        return [];
    }

    public function group()
    {
        return $this->belongsTo('\App\Models\Groups', 'group_id');
    }
}
