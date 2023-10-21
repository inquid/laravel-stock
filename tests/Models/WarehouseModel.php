<?php

namespace Inquid\Stock\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Inquid\Stock\Warehouse;

/**
 * Class to represent a warehouse.
 */
class WarehouseModel extends Model implements Warehouse
{
    protected $table = 'warehouses';

    protected $guarded = [];

    public $timestamps = false;
}
