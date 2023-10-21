<?php

namespace Inquid\Stock;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\morphMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

trait HasStock
{
    /*
     |--------------------------------------------------------------------------
     | Accessors
     |--------------------------------------------------------------------------
     */

    /**
     * Stock accessor.
     *
     * @return float
     */
    public function getStockAttribute()
    {
        return $this->stock();
    }

    /*
     |--------------------------------------------------------------------------
     | Methods
     |--------------------------------------------------------------------------
     */

    /**
     * Returns the stock at a given date and warehouse (optionals)
     *
     * @param null $date
     * @param null $warehouse
     * @return float
     */
    public function stock($date = null, $warehouse = null): float
    {
        $date = $date ?: Carbon::now();
        
        if (! $date instanceof DateTimeInterface) {
            $date = Carbon::create($date);
        }
        
        $specialDateClass = config('stock.special_date_class');
        if($specialDateClass){
            $date = new $specialDateClass($date);
        } else {
            $date = $date->format('Y-m-d H:i:s');
        }
        
        $mutations = $this->stockMutations()->where('created_at', '<=', $date);
        
        if ($warehouse != null) {
            $mutations->where([
                'reference_type' => $warehouse::class,
                'reference_id' => $warehouse->id,
            ]);
        }
        
        return (float) $mutations
            ->where('created_at', '<=', $date)
            ->sum('amount');
    }

    public function increaseStock($amount = 1, $arguments = []): Model
    {
        return $this->createStockMutation($amount, $arguments);
    }

    public function decreaseStock($amount = 1, $arguments = []): Model
    {
        return $this->createStockMutation(-1 * abs($amount), $arguments);
    }

    public function mutateStock($amount = 1, $arguments = []): Model
    {
        return $this->createStockMutation($amount, $arguments);
    }

    public function clearStock($newAmount = null, $arguments = []): bool
    {
        $this->stockMutations()->delete();

        if (! is_null($newAmount)) {
            $this->createStockMutation($newAmount, $arguments);
        }

        return true;
    }

    public function setStock($newAmount, $arguments = []): Model
    {
        $currentStock = $this->stock(null, $arguments['reference'] ?? null);

        if ($deltaStock = $newAmount - $currentStock) {
            return $this->createStockMutation($deltaStock, $arguments);
        }

        return false;
    }

    public function inStock($amount = 1): bool
    {
        return $this->stock > 0.0 && $this->stock >= $amount;
    }

    public function outOfStock(): bool
    {
        return $this->stock <= 0.0;
    }

    /**
     * Function to handle mutations (increase, decrease).
     *
     * @param  float  $amount
     * @param  array  $arguments
     * @return Model
     */
    protected function createStockMutation($amount, $arguments = []): Model
    {
        $reference = Arr::get($arguments, 'reference');

        $createArguments = collect([
            'amount' => $amount,
            'description' => Arr::get($arguments, 'description'),
        ])->when($reference, function ($collection) use ($reference) {
            return $collection
                ->put('reference_type', $reference->getMorphClass())
                ->put('reference_id', $reference->getKey());
        })->toArray();

        return $this->stockMutations()->create($createArguments);
    }

    /*
     |--------------------------------------------------------------------------
     | Scopes
     |--------------------------------------------------------------------------
     */

    public function scopeWhereInStock($query)
    {
        return $query->where(function ($query) {
            return $query->whereHas('stockMutations', function ($query) {
                return $query->select('stockable_id')
                    ->groupBy('stockable_id')
                    ->havingRaw('SUM(amount) > 0.0');
            });
        });
    }

    public function scopeWhereOutOfStock($query)
    {
        return $query->where(function ($query) {
            return $query->whereHas('stockMutations', function ($query) {
                return $query->select('stockable_id')
                    ->groupBy('stockable_id')
                    ->havingRaw('SUM(amount) <= 0.0');
            })->orWhereDoesntHave('stockMutations');
        });
    }

    /*
     |--------------------------------------------------------------------------
     | Relations
     |--------------------------------------------------------------------------
     */

    /**
     * Relation with StockMutation.
     *
     * @return morphMany
     */
    public function stockMutations(): morphMany
    {
        return $this->morphMany(config('stock.stock_mutation_model'), 'stockable');
    }
}
