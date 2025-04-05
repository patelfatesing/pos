<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        Item::insert([
            [
                'name' => 'Laptop',
                'image_url' => 'https://fastly.picsum.photos/id/21/3008/2008.jpg?hmac=T8DSVNvP-QldCew7WD4jj_S3mWwxZPqdF0CNPksSko4',
                'description' => 'High-performance laptop with 16GB RAM.',
                'category' => 'Electronics',
                'brand' => 'Dell',
                'sku' => 'LAP12345',
                'price' => 75000.00,
                'quantity' => 10,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Smartphone',
                'image_url' => 'https://fastly.picsum.photos/id/21/3008/2008.jpg?hmac=T8DSVNvP-QldCew7WD4jj_S3mWwxZPqdF0CNPksSko4',
                'description' => 'Latest model smartphone with OLED display.',
                'category' => 'Electronics',
                'brand' => 'Samsung',
                'sku' => 'SMT56789',
                'price' => 50000.00,
                'quantity' => 20,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Headphones',
                'image_url' => 'https://fastly.picsum.photos/id/21/3008/2008.jpg?hmac=T8DSVNvP-QldCew7WD4jj_S3mWwxZPqdF0CNPksSko4',
                'description' => 'Wireless noise-canceling headphones.',
                'category' => 'Accessories',
                'brand' => 'Sony',
                'sku' => 'HDP67890',
                'price' => 10000.00,
                'quantity' => 15,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
