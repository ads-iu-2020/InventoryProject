<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class ProductController extends Controller
{

    public function list()
    {
        $products = Product::all();
        $products = $this->handleProductInfo($products);

        return response()->json($products);
    }

    public function search(Request $request)
    {
        $this->validate($request, [
            'title' => 'required|string'
        ]);

        $foundProducts = Product::where('title', $request->input('title'))->get()->values();
        $foundProducts = $this->handleProductInfo($foundProducts);

        return response()->json($foundProducts);
    }

    /**
     * @param Product[]|Collection $products
     * @return Product[]|Collection
     */
    protected function handleProductInfo($products)
    {
        return $products
            ->transform(function (Product $product) {
                $product->price = (float)$product->price;
                return $product->only(['id', 'title', 'description', 'price']);
            });
    }
}
