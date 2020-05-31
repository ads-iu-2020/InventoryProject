<?php

use App\Models\Product;
use App\Models\StockChangeRecord;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Laravel\Lumen\Testing\DatabaseMigrations;

class StockTest extends TestCase
{
    use DatabaseMigrations;

    public function testList()
    {
        /** @var Product $product */
        $product = factory(Product::class)->create();

        $values = [10, -5, 15];

        foreach ($values as $value) {
            $product->stockChanges()->create([
                'value' => $value
            ]);
        }

        $this
            ->get('/api/stocks/list')
            ->seeJson([
                [
                    'product_id' => $product->id,
                    'stocks' => array_sum($values)
                ]
            ]);
    }

    public function testListWithNoChanges()
    {
        /** @var Product $product */
        $product = factory(Product::class)->create();

        $this
            ->get('/api/stocks/list')
            ->seeJson([
                [
                    'product_id' => $product->id,
                    'stocks' => 0
                ]
            ]);
    }

    public function testReserve()
    {
        /** @var Product $product */
        $product = factory(Product::class)->create();

        $product->stockChanges()->create([
            'value' => 100
        ]);

        $params = [
            'product_id' => $product->id,
            'amount' => 10
        ];

        $this
            ->get('/api/stocks/reserve?' . http_build_query($params))
            ->seeJson([
                'success' => true
            ]);

        $this
            ->get('/api/stocks/list')
            ->seeJson([
                [
                    'product_id' => $product->id,
                    'stocks' => 90
                ]
            ]);
    }

    public function testReserveEmpty()
    {
        /** @var Product $product */
        $product = factory(Product::class)->create();

        $params = [
            'product_id' => $product->id,
            'amount' => 10
        ];

        $this
            ->get('/api/stocks/reserve?' . http_build_query($params))
            ->seeJson([
                'success' => false,
                'message' => 'Not enough stocks to reserve.'
            ]);
    }

    public function testReserveNegative()
    {
        /** @var Product $product */
        $product = factory(Product::class)->create();

        $params = [
            'product_id' => $product->id,
            'amount' => -10
        ];

        $this
            ->get('/api/stocks/reserve?' . http_build_query($params))
            ->seeJson([
                'amount' => [
                    'The amount must be at least 0.'
                ],
            ]);
    }

    public function testCancel()
    {
        /** @var Product $product */
        $product = factory(Product::class)->create();

        $product->stockChanges()->create([
            'value' => 100
        ]);

        $params = [
            'product_id' => $product->id,
            'amount' => 10
        ];

        $this
            ->get('/api/stocks/cancel?' . http_build_query($params))
            ->seeJson([
                'success' => true
            ]);

        $this
            ->get('/api/stocks/list')
            ->seeJson([
                [
                    'product_id' => $product->id,
                    'stocks' => 110
                ]
            ]);
    }

    public function testCancelNegative()
    {
        /** @var Product $product */
        $product = factory(Product::class)->create();

        $params = [
            'product_id' => $product->id,
            'amount' => -10
        ];

        $this
            ->get('/api/stocks/cancel?' . http_build_query($params))
            ->seeJson([
                'amount' => [
                    'The amount must be at least 0.'
                ],
            ]);
    }

    public function testHistory()
    {
        /** @var Product $product */
        $product = factory(Product::class)->create();

        $stockChanges = factory(StockChangeRecord::class, 20)->make()->toArray();
        $now = Carbon::now()->startOfMinute();
        $expectedJson = [];

        foreach ($stockChanges as $stockChange) {
            $stockChangeModel = $product->stockChanges()->make($stockChange);
            $stockChangeModel['created_at'] = $now;
            $stockChangeModel->save(['timestamps' => false]);

            $expectedJson[$stockChangeModel->id] = [
                'value' => $stockChangeModel->value,
                'created_at' => $now
            ];
        }

        $params = [
            'product_id' => $product->id
        ];

        $this
            ->get('/api/stocks/history?' . http_build_query($params))
            ->seeJson($expectedJson);
    }

    public function testDayHistory()
    {
        /** @var Product $product */
        $product = factory(Product::class)->create();

        $period = CarbonPeriod::create(Carbon::today()->subMonth(), Carbon::today());

        foreach ($period as $day) {
            $dayStockChanges = factory(StockChangeRecord::class, 100)->make()->toArray();

            $i = 0;

            foreach ($dayStockChanges as $stockChange) {
                $stockChangeModel = $product->stockChanges()->make($stockChange);
                $stockChangeModel['created_at'] = $day->subHours($i);
                $stockChangeModel->save(['timestamps' => false]);

                $i++;
            }
        }

        $params = [
            'product_id' => $product->id
        ];

        $this
            ->get('/api/stocks/history/day?' . http_build_query($params))
            ->assertResponseOk();
    }
}
