<?php

namespace Inquid\Stock\Tests;

use Inquid\Stock\HasStock;
use Illuminate\Database\Eloquent\Model;

class OrderRow extends Model
{
    use HasStock;

    protected $table = 'order_rows';

    protected $guarded = [];

    public $timestamps = false;

    public function stockModel()
    {
        return $this->belongsTo(StockModel::class);
    }
}
