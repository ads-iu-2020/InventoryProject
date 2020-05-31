<?php
declare(strict_types=1);


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'title',
        'description',
        'price'
    ];

    public function stockChanges()
    {
        return $this->hasMany(StockChangeRecord::class);
    }

    public function getStocksLevel()
    {
        if ($this->relationLoaded('stockChanges')) {
            return $this->stockChanges->sum('value');
        }

        return $this->stockChanges()->sum('value');
    }

    public function reserveStocks($amount)
    {
        if ($this->getStocksLevel() < $amount) {
            throw new \UnexpectedValueException('Not enough stocks to reserve.');
        }

        $this->stockChanges()->create([
            'value' => -$amount
        ]);
    }

    public function cancelStocks($amount)
    {
        $this->stockChanges()->create([
            'value' => $amount
        ]);
    }
}
