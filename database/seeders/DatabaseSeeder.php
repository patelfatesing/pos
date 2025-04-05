<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\Product::factory()->create([
            'product_type' => 'Electronics',
            'name' => 'Smartphone',
            'code' => 'SP001',
            'barcode_symbology' => 'EAN-13',
            'category' => 'Mobile Phones',
            'cost' => 200.00,
            'price' => 300.00,
            'tax_method' => 'Exclusive',
            'quantity' => 50,
            'image' => 'smartphone.jpg',
            'description' => 'A high-quality smartphone with advanced features.',
        ]);

        \App\Models\Product::factory()->create([
            'product_type' => 'Appliances',
            'name' => 'Microwave Oven',
            'code' => 'MW001',
            'barcode_symbology' => 'UPC',
            'category' => 'Kitchen Appliances',
            'cost' => 100.00,
            'price' => 150.00,
            'tax_method' => 'Inclusive',
            'quantity' => 30,
            'image' => 'microwave.jpg',
            'description' => 'A compact microwave oven suitable for small kitchens.',
        ]);

        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            RolePermissionSeeder::class,
            UserSeeder::class,
        ]);
    }
}
