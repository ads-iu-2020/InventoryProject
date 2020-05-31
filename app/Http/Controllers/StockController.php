<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function list()
    {
        $products = Product::with('stockChanges')->get();

        $stocks = $products->map(function (Product $product) {
            return [
                'product_id' => $product->id,
                'stocks' => $product->getStocksLevel()
            ];
        });

        return response()->json($stocks);
    }

    public function reserve(Request $request)
    {
        $this->validate($request, [
            'product_id' => 'required|exists:products,id',
            'amount' => 'required|integer|min:0'
        ]);

        /** @var Product $product */
        $product = Product::find($request->input('product_id'));

        try {
            $product->reserveStocks($request->input('amount'));
        } catch (\UnexpectedValueException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }

        return response()->json(['success' => true]);
    }

    public function cancel(Request $request)
    {
        $this->validate($request, [
            'product_id' => 'required|exists:products,id',
            'amount' => 'required|integer|min:0'
        ]);

        /** @var Product $product */
        $product = Product::find($request->input('product_id'));

        $product->cancelStocks($request->input('amount'));

        return response()->json(['success' => true]);
    }


}
