<?php
declare(strict_types=1);


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockChangeRecord extends Model
{
    protected $fillable = [
        'value'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
