<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $table = 'stocks';
    protected $primaryKey = 'id';

    protected $fillable = [
        'medicine_id',
        'base_quantity',
    ];

    public function medicine()
    {
        return $this->belongsTo(Medicine::class, 'medicine_id', 'id');
    }

    public function histories()
    {
        return $this->hasMany(StockHistory::class, 'stock_id', 'id');
    }
}
