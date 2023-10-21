<?php


use Inquid\Stock\Models\StockModel;
use Inquid\Stock\Models\WarehouseModel;

return [

    /*
    |--------------------------------------------------------------------------
    | Default table name
    |--------------------------------------------------------------------------
    |
    | Table name to use to store mutations.
    |
    */

    'table' => 'stock_mutations',
    
    'models' => [
        'stock' => StockModel::class,
        'warehouse' => WarehouseModel::class,
    ],

];
