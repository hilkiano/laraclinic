<?php

namespace App\Models;

use BinaryCats\Sku\HasSku;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Services extends Model
{
    use HasSku, SoftDeletes;

    protected $table = 'services';

    protected $fillable = [
        'sku',
        'label',
        'category',
        'buy_price',
        'sell_price',
        'description'
    ];
}
