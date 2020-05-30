<?php

use Laravel\Lumen\Testing\DatabaseMigrations;

class ProductTest extends TestCase
{
    use DatabaseMigrations;

    public function testExample()
    {
        $product = factory(\App\Models\Product::class)->create();
        $this->get('/api/products/list')->seeJson([
            [
                'id' => $product->id,
                'title' => $product->title,
                'description' => $product->description,
                'price' => $product->price
            ]
        ]);
    }
}
