<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventorySeeder extends Seeder
{
    /**
     * Seed the product inventories and inventory indices.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Insert product inventories
        DB::table('product_inventories')->insert([
            // Electronics
            [
                'product_id'          => 1,
                'vendor_id'           => 0,
                'inventory_source_id' => 1,
                'qty'                 => 150,
            ],
            [
                'product_id'          => 2,
                'vendor_id'           => 0,
                'inventory_source_id' => 1,
                'qty'                 => 200,
            ],
            [
                'product_id'          => 3,
                'vendor_id'           => 0,
                'inventory_source_id' => 1,
                'qty'                 => 50,
            ],

            // Clothing
            [
                'product_id'          => 4,
                'vendor_id'           => 0,
                'inventory_source_id' => 1,
                'qty'                 => 300,
            ],
            [
                'product_id'          => 5,
                'vendor_id'           => 0,
                'inventory_source_id' => 1,
                'qty'                 => 80,
            ],

            // Home & Garden
            [
                'product_id'          => 6,
                'vendor_id'           => 0,
                'inventory_source_id' => 1,
                'qty'                 => 25,
            ],
            [
                'product_id'          => 7,
                'vendor_id'           => 0,
                'inventory_source_id' => 1,
                'qty'                 => 100,
            ],

            // Configurable variants
            [
                'product_id'          => 9,
                'vendor_id'           => 0,
                'inventory_source_id' => 1,
                'qty'                 => 60,
            ],
            [
                'product_id'          => 10,
                'vendor_id'           => 0,
                'inventory_source_id' => 1,
                'qty'                 => 45,
            ],
            [
                'product_id'          => 11,
                'vendor_id'           => 0,
                'inventory_source_id' => 1,
                'qty'                 => 35,
            ],
        ]);

        // Insert product inventory indices
        DB::table('product_inventory_indices')->insert([
            [
                'product_id' => 1,
                'channel_id' => 1,
                'qty'        => 150,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'product_id' => 2,
                'channel_id' => 1,
                'qty'        => 200,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'product_id' => 3,
                'channel_id' => 1,
                'qty'        => 50,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'product_id' => 4,
                'channel_id' => 1,
                'qty'        => 300,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'product_id' => 5,
                'channel_id' => 1,
                'qty'        => 80,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'product_id' => 6,
                'channel_id' => 1,
                'qty'        => 25,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'product_id' => 7,
                'channel_id' => 1,
                'qty'        => 100,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'product_id' => 8,
                'channel_id' => 1,
                'qty'        => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'product_id' => 9,
                'channel_id' => 1,
                'qty'        => 60,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'product_id' => 10,
                'channel_id' => 1,
                'qty'        => 45,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'product_id' => 11,
                'channel_id' => 1,
                'qty'        => 35,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}