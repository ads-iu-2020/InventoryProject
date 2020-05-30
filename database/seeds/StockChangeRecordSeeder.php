<?php

use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class StockChangeRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $products = Product::all();

        $now =  Carbon::now();

        foreach ($products as $product) {
            $changeRecords = factory(\App\Models\StockChangeRecord::class, 10)->make();

            foreach ($changeRecords as $record) {
                $record->created_at = $now;
                $record->updated_at = $now;
                $record->timestamps = false;
                $product->stockChanges()->save($record);
            }
        }
    }
}
