<?php

namespace App\Models;

use BinaryCats\Sku\HasSku;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    use HasSku, SoftDeletes;

    protected $table = 'medicines';

    protected $fillable = [
        'sku',
        'label',
        'category',
        'buy_price',
        'sell_price',
        'prescription',
        'description'
    ];

    public function stocks()
    {
        return $this->hasMany(Stock::class, 'medicine_id', 'id')->orderBy('created_at', 'asc');
    }
}
