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

        $this->get('/api/stocks/list')->seeJson([
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

        $this->get('/api/stocks/list')->seeJson([
            [
                'product_id' => $product->id,
                'stocks' => 0
            ]
        ]);
    }
}
