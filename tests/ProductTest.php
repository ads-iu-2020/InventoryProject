<?php

use App\Models\Product;
use Laravel\Lumen\Testing\DatabaseMigrations;

class ProductTest extends TestCase
{
    use DatabaseMigrations;

    public function testList()
    {
        $product = factory(Product::class)->create();
        $this->get('/api/products/list')->seeJson([
            [
                'id' => $product->id,
                'title' => $product->title,
                'description' => $product->description,
                'price' => $product->price
            ]
        ]);
    }

    public function testSearch()
    {
        $products = factory(Product::class, 100)->create();

        $productToSearch = $products->first();

        $route = '/api/products/search';
        $params = [
            'title' => $productToSearch->title
        ];

        $url = $route . '?' . http_build_query($params);

        $this->get($url)->seeJson([
            [
                'id' => $productToSearch->id,
                'title' => $productToSearch->title,
                'description' => $productToSearch->description,
                'price' => $productToSearch->price
            ]
        ]);
    }
}
