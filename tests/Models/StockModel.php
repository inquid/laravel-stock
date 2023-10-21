<?php

namespace Inquid\Stock\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Inquid\Stock\HasStock;

class StockModel extends Model
{
    use HasStock;

    protected $table = 'stock_models';

    protected $guarded = [];

    public $timestamps = false;
}
