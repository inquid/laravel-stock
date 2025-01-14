<?php

namespace Inquid\Stock\Tests;

use Illuminate\Database\Eloquent\Model;
use Inquid\Stock\HasStock;
use Inquid\Stock\Tests\Models\StockModel;

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
