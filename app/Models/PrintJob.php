<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrintJob extends Model
{
    protected $table = 'print_jobs';

    protected $fillable = [
        'payload',
        'is_completed',
        'error'
    ];

    protected $casts = [
        'payload' => 'array'
    ];
}
