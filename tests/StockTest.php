<?php

use App\Models\Product;
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
}
