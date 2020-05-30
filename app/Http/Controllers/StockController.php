<?php

namespace App\Http\Controllers;

use App\Models\Product;

class StockController extends Controller
{

    public function list()
    {
        $products = Product::with('stockChanges')->get();

        $stocks = $products->map(function (Product $product) {
            return [
                'product_id' => $product->id,
                'stocks' => $product->stockChanges->sum('value')
            ];
        });

        return response()->json($stocks);
    }
}
