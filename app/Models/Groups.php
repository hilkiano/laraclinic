<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Groups extends Model
{
    use SoftDeletes;

    protected $table = "groups";

    protected $fillable = [
        'name',
        'description',
        'role_ids'
    ];

    protected $casts = [
        'role_ids' => 'array'
    ];
}
