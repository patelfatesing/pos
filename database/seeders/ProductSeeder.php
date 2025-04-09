<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Support\Arr;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $categoryIds = Category::pluck('id')->toArray();
        $subCategoryIds = SubCategory::pluck('id')->toArray();

        Product::insert([
            [
                'name' => 'Red Wine',
                'brand' => 'Vintage Wines',
                'size' => '750ml',
                'sku' => 'REDWINE750',
                'abv' => 13.5,
                'barcode' => '1234567890123',
                'image' => 'red-wine.jpg',
                'description' => 'A classic red wine with smooth finish.',
                'category_id' => Arr::random($categoryIds),
                'subcategory_id' => Arr::random($subCategoryIds),
                'is_active' => 'yes',
                'is_deleted' => 'no',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Craft Beer',
                'brand' => 'Brew Bros',
                'size' => '500ml',
                'sku' => 'CRAFTBEER500',
                'abv' => 5.0,
                'barcode' => '9876543210987',
                'image' => 'craft-beer.jpg',
                'description' => 'A bold and hoppy craft beer.',
                'category_id' => Arr::random($categoryIds),
                'subcategory_id' => Arr::random($subCategoryIds),
                'is_active' => 'yes',
                'is_deleted' => 'no',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
