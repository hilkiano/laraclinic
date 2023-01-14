<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Roles extends Model
{
    use SoftDeletes;

    protected $table = "roles";

    protected $fillable = [
        'name',
        'description',
        'menu_ids',
        'privilege_ids'
    ];

    protected $casts = [
        'menu_ids' => 'array',
        'privilege_ids' => 'array'
    ];
}
