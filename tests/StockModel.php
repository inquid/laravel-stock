<?php

namespace Inquid\Stock\Tests;

use Inquid\Stock\HasStock;
use Illuminate\Database\Eloquent\Model;

class StockModel extends Model
{
    use HasStock;

    protected $table = 'stock_models';

    protected $guarded = [];

    public $timestamps = false;
}
