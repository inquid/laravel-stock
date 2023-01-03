<?php

namespace Inquid\Stock\Tests;

use Inquid\Stock\ReferencedByStockMutations;
use Illuminate\Database\Eloquent\Model;

class ReferenceModel extends Model
{
    use ReferencedByStockMutations;

    protected $table = 'reference_models';

    protected $guarded = [];

    public $timestamps = false;
}
