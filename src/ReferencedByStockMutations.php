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
        return $this->morphMany(StockMutation::class, 'reference');
    }
}
