<?php

namespace Appstract\Stock\Tests;

use Illuminate\Database\Eloquent\Model;

/**
 * Class to represent a warehouse.
 */
class WarehouseModel extends Model
{
    protected $table = 'warehouses';

    protected $guarded = [];

    public $timestamps = false;
}
