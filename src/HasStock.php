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
     * @param array $arguments
     * @return float
     */
    public function stock($date = null, array $arguments = []): float
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
        $reference = Arr::get($arguments, 'reference');
        $warehouse = Arr::get($arguments, 'warehouse');
        
        if ($reference) {
            $mutations->where([
                'reference_type' => $reference->getMorphClass(),
                'reference_id' => $reference->getKey(),
            ]);
        }
        
        if ($warehouse) {
            $mutations->where([
                'warehouse_type' => $warehouse->getMorphClass(),
                'warehouse_id' => $warehouse->getKey(),
            ]);
        }
        
        return (float) $mutations
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
        $reference = Arr::get($arguments, 'reference');
        $warehouse = Arr::get($arguments, 'warehouse');
        
        $mutations = $this->stockMutations();
        
        if ($reference) {
            $mutations->where([
                'reference_type' => $reference->getMorphClass(),
                'reference_id' => $reference->getKey(),
            ]);
        }
        
        if ($warehouse) {
            $mutations->where([
                'warehouse_type' => $warehouse->getMorphClass(),
                'warehouse_id' => $warehouse->getKey(),
            ]);
        }
        
        $mutations->delete();
        
        if (!is_null($newAmount)) {
            $this->createStockMutation($newAmount, $arguments);
        }

        return true;
    }
    
    public function moveBetweenStocks(float $amount, Warehouse $source, Warehouse $destination)
    {
        $this->decreaseStock($amount, [ 'warehouse' => $source]);
        $this->increaseStock($amount, [ 'warehouse' => $destination]);

        return true;
    }

    public function setStock($newAmount, $arguments = []): Model|bool
    {
        $currentStock = $this->stock(null, $arguments);

        if ($deltaStock = $newAmount - $currentStock) {
            return $this->createStockMutation($deltaStock, $arguments);
        }

        return false;
    }

    public function inStock($amount = 1, $arguments = []): bool
    {
        $currentStock = $this->stock(null, $arguments);

        return $currentStock > 0.0 && $currentStock >= $amount;
    }

    public function outOfStock($arguments = []): bool
    {
        $currentStock = $this->stock(null, $arguments);
        
        return $currentStock <= 0.0;
    }

    /**
     * Function to handle mutations (increase, decrease).
     *
     * @param float|int $amount
     * @param  array  $arguments
     * @return Model
     */
    protected function createStockMutation(float|int $amount, array $arguments = []): Model
    {
        $reference = Arr::get($arguments, 'reference');
        $warehouse = Arr::get($arguments, 'warehouse');
        
        $createArguments = collect([
            'amount' => $amount,
            'description' => Arr::get($arguments, 'description'),
        ])->when($reference, function ($collection) use ($reference) {
            return $collection
                ->put('reference_type', $reference->getMorphClass())
                ->put('reference_id', $reference->getKey());
        })->when($warehouse, function ($collection) use ($warehouse) {
            return $collection
                ->put('warehouse_type', $warehouse->getMorphClass())
                ->put('warehouse_id', $warehouse->getKey());
        })->toArray();

        return $this->stockMutations()->create($createArguments);
    }

    /*
     |--------------------------------------------------------------------------
     | Scopes
     |--------------------------------------------------------------------------
     */
    
    public function scopeWhereInStock($query, Warehouse $warehouse = null)
    {
        return $query->where(function ($query) use ($warehouse) {
            return $query->whereHas('stockMutations', function ($query) use ($warehouse) {
                return $query
                    ->select('stockable_id')
                    ->when($warehouse, function ($query) use ($warehouse) {
                        return $query
                            ->where('warehouse_type', $warehouse->getMorphClass())
                            ->where('warehouse_id', $warehouse->getKey());
                    })
                    ->groupBy('stockable_id')
                    ->havingRaw('SUM(amount) > 0.0');
            });
        });
    }
    
    public function scopeWhereOutOfStock($query, Warehouse $warehouse = null)
    {
        return $query->where(function ($query) use ($warehouse) {
            return $query->whereHas('stockMutations', function ($query) use ($warehouse) {
                return $query->select('stockable_id')
                    ->when($warehouse, function ($query) use ($warehouse) {
                        return $query
                            ->where('warehouse_type', $warehouse->getMorphClass())
                            ->where('warehouse_id', $warehouse->getKey());
                    })
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
