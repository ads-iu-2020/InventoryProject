<?php

namespace App\Http\Controllers;

use App\Models\Product;

class ProductController extends Controller
{
    public function list()
    {
        $products = Product::all(['id', 'title', 'description', 'price']);

        $products->map(function (Product $product) {
            $product->price = (float) $product->price;
            return $product;
        });

        return response()->json($products);
    }
}
