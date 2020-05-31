<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockChangeRecord;
use Carbon\Carbon;
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

    /**
     * Return day by day stocks records for previous week.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function dayHistory(Request $request)
    {
        $this->validate($request, [
            'product_id' => 'required|exists:products,id',
        ]);

        /** @var Product $product */
        $product = Product::find($request->input('product_id'))->load('stockChanges');

        $response = [];

        $startAt = Carbon::today()->subWeeks(2);
        $period = CarbonPeriod::create($startAt, Carbon::today());

        $initialStockAmount = $product->stockChanges
            ->where('created_at', '<', $startAt)
            ->sum('value');

        foreach ($period as $day) {
            if ($day->isSameDay($startAt)) {
                $response[$day->format('Y-m-d')] = $initialStockAmount;
            } else {
                $response[$day->format('Y-m-d')] = 0;
            }
        }

        $weekStockChanges = $product->stockChanges->where('created_at', '>=', $startAt);

        foreach ($weekStockChanges as $stockChange) {
            $response[$stockChange->created_at->format('Y-m-d')] += $stockChange->value;
        }

        $isFirstDay = true;

        foreach ($period as $day) {
            if (!$isFirstDay) {
                $response[$day->format('Y-m-d')] += $response[$day->subDay()->format('Y-m-d')];
            }

            $isFirstDay = false;
        }

        return response()->json($response);
    }

}
