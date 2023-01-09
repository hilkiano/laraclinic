<?php

namespace App\Models;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'role',
        'remember_token'
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
        return [];
    }
}
