<?php
declare(strict_types=1);


namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'title',
        'description',
        'price'
    ];

    public function stockChanges() {
        return $this->hasMany(StockChangeRecord::class);
    }
}
