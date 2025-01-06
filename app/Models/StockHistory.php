<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockHistory extends Model
{
    protected $table = 'stock_histories';
    protected $primaryKey = 'id';

    protected $fillable = [
        'stock_id',
        'type',
        'quantity',
        'description',
        'transaction_id',
    ];

    public function stock()
    {
        return $this->belongsTo(Stock::class, 'stock_id', 'id');
    }
}
