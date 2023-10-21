<?php

namespace Inquid\Stock;

use Illuminate\Database\Eloquent\Relations\morphMany;

trait ReferencedByStockMutations
{
    /**
     * Relation with StockMutation.
     *
     * @return morphMany
     */
    public function stockMutations(): morphMany
    {
        return $this->morphMany(config('stock.stock_mutation_model'), 'reference');
    }
}
