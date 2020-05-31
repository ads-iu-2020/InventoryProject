<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockChangeRecord;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
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

    public function history(Request $request)
    {
        $this->validate($request, [
            'product_id' => 'required|exists:products,id',
        ]);

        /** @var Product $product */
        $product = Product::find($request->input('product_id'));

        $stocksHistory = $product->stockChanges()->get()
            ->keyBy(function (StockChangeRecord $record) {
                return $record->id;
            })
            ->map(function (StockChangeRecord $record) {
                $record->value = (int)$record->value;
                return $record->only(['value', 'created_at']);
            });

        return response()->json($stocksHistory);
    }

    public function dayHistory(Request $request)
    {
        $this->validate($request, [
            'product_id' => 'required|exists:products,id',
        ]);

        /** @var Product $product */
        $product = Product::find($request->input('product_id'));

        $response = [];

        $startAt = $product->stockChanges()->first()->created_at;
        $period = CarbonPeriod::create($startAt, CarbonImmutable::today());

        foreach ($period as $day) {
            $dayStocks = $product
                ->stockChanges()
                ->where('created_at', '<=', $day->endOfDay()->toDateTimeString())
                ->get();

            $response[$day->format('Y-m-d')] = $dayStocks->sum('value');
        }

        return response()->json($response);
    }

}
