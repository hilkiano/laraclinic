<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Menus extends Model
{
    use SoftDeletes;

    protected $table = "menus";

    protected $fillable = [
        'name',
        'label',
        'route',
        'icon',
        'is_parent',
        'parent',
        'order'
    ];
}
