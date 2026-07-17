<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Attribute type field mapping.
     */
    public array $attributeTypeFields = [
        'text'        => 'text_value',
        'textarea'    => 'text_value',
        'price'       => 'float_value',
        'boolean'     => 'boolean_value',
        'select'      => 'integer_value',
        'multiselect' => 'text_value',
        'datetime'    => 'datetime_value',
        'date'        => 'date_value',
        'file'        => 'text_value',
        'image'       => 'text_value',
        'checkbox'    => 'text_value',
    ];

    /**
     * Products data.
     */
    public array $products = [];

    /**
     * Seed the products and related data.
     */
    public function run(): void
    {
        DB::table('products')->delete();
        DB::table('product_flat')->delete();
        DB::table('product_attribute_values')->delete();
        DB::table('product_categories')->delete();
        DB::table('product_channels')->delete();
        DB::table('product_images')->delete();
        DB::table('product_inventories')->delete();
        DB::table('product_inventory_indices')->delete();
        DB::table('product_price_indices')->delete();
        DB::table('product_customer_group_prices')->delete();
        DB::table('product_relations')->delete();
        DB::table('product_up_sells')->delete();
        DB::table('product_cross_sells')->delete();

        $now = Carbon::now();

        $this->prepareProductsData();

        // 1. Insert products
        $productRecords = [];
        foreach ($this->products as $product) {
            $productRecords[] = [
                'id'                  => $product['product_id'],
                'sku'                 => $product['sku'],
                'type'                => $product['type'],
                'parent_id'           => $product['parent_id'],
                'attribute_family_id' => $product['attribute_family_id'],
                'created_at'          => $now,
                'updated_at'          => $now,
            ];
        }
        DB::table('products')->insert($productRecords);

        // 2. Insert product_flat
        $flatRecords = [];
        foreach ($this->products as $product) {
            $flatRecords[] = [
                'sku'                  => $product['sku'],
                'type'                 => $product['type'],
                'product_number'       => $product['product_number'] ?? null,
                'name'                 => $product['name'],
                'short_description'    => $product['short_description'],
                'description'          => $product['description'],
                'url_key'              => $product['url_key'],
                'new'                  => $product['new'] ?? 0,
                'featured'             => $product['featured'] ?? 0,
                'status'               => $product['status'] ?? 1,
                'meta_title'           => $product['meta_title'],
                'meta_keywords'        => $product['meta_keywords'],
                'meta_description'     => $product['meta_description'],
                'price'                => $product['price'],
                'special_price'        => $product['special_price'],
                'special_price_from'   => $product['special_price_from'],
                'special_price_to'     => $product['special_price_to'],
                'weight'               => $product['weight'],
                'created_at'           => $now,
                'locale'               => 'en',
                'channel'              => 'default',
                'attribute_family_id'  => $product['attribute_family_id'],
                'product_id'           => $product['product_id'],
                'updated_at'           => $now,
                'parent_id'            => $product['parent_id'],
                'visible_individually' => $product['visible_individually'] ?? 1,
            ];
        }
        DB::table('product_flat')->insert($flatRecords);

        // 3. Insert product attribute values
        $attributes = DB::table('attributes')->get()->keyBy('code');
        $attributeValues = [];
        $skipAttributes = ['product_id', 'parent_id', 'type', 'attribute_family_id', 'locale', 'channel', 'created_at', 'updated_at', 'sku'];
        $localeSpecificAttributes = ['name', 'url_key', 'short_description', 'description', 'meta_title', 'meta_keywords', 'meta_description'];

        foreach ($this->products as $product) {
            foreach ($product as $attributeCode => $value) {
                if (in_array($attributeCode, $skipAttributes)) {
                    continue;
                }

                if (! isset($attributes[$attributeCode])) {
                    continue;
                }

                $attribute = $attributes[$attributeCode];

                $uniqueId = implode('|', array_filter([
                    $attribute->value_per_channel ? 'default' : null,
                    $attribute->value_per_locale ? 'en' : null,
                    $product['product_id'],
                    $attribute->id,
                ]));

                $attributeTypeValues = array_fill_keys(array_values($this->attributeTypeFields), null);

                $attributeValues[] = array_merge($attributeTypeValues, [
                    'attribute_id'                               => $attribute->id,
                    'product_id'                                 => $product['product_id'],
                    $this->attributeTypeFields[$attribute->type] => $value,
                    'channel'                                    => $attribute->value_per_channel ? 'default' : null,
                    'locale'                                     => $attribute->value_per_locale ? 'en' : null,
                    'unique_id'                                  => $uniqueId,
                    'json_value'                                 => null,
                ]);
            }
        }
        DB::table('product_attribute_values')->insert($attributeValues);

        // 4. Insert product channels
        $channelRecords = [];
        foreach ($this->products as $product) {
            $channelRecords[] = [
                'product_id' => $product['product_id'],
                'channel_id' => 1,
            ];
        }
        DB::table('product_channels')->insert($channelRecords);

        // 5. Insert product categories
        $categoryRecords = [];
        foreach ($this->products as $product) {
            if (! empty($product['category_id'])) {
                $categoryRecords[] = [
                    'product_id'  => $product['product_id'],
                    'category_id' => $product['category_id'],
                ];
            }
        }
        DB::table('product_categories')->insert($categoryRecords);

        // 6. Insert product price indices (for all 3 customer groups)
        $customerGroupIds = [1, 2, 3];
        $priceIndexRecords = [];
        $indexId = 1;

        foreach ($this->products as $product) {
            if ($product['type'] === 'grouped' || $product['type'] === 'bundle') {
                continue;
            }

            foreach ($customerGroupIds as $groupId) {
                $priceIndexRecords[] = [
                    'id'                => $indexId++,
                    'product_id'        => $product['product_id'],
                    'customer_group_id' => $groupId,
                    'channel_id'        => 1,
                    'min_price'         => $product['price'] ?? 0,
                    'regular_min_price' => $product['price'] ?? 0,
                    'max_price'         => $product['price'] ?? 0,
                    'regular_max_price' => $product['price'] ?? 0,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ];
            }
        }
        DB::table('product_price_indices')->insert($priceIndexRecords);

        // 7. Insert product relations (related products)
        DB::table('product_relations')->insert([
            ['parent_id' => 1, 'child_id' => 2],
            ['parent_id' => 1, 'child_id' => 3],
            ['parent_id' => 2, 'child_id' => 1],
            ['parent_id' => 2, 'child_id' => 3],
            ['parent_id' => 3, 'child_id' => 1],
            ['parent_id' => 3, 'child_id' => 2],
            ['parent_id' => 8, 'child_id' => 9],
            ['parent_id' => 9, 'child_id' => 8],
            ['parent_id' => 10, 'child_id' => 11],
            ['parent_id' => 11, 'child_id' => 10],
        ]);

        // 8. Insert up-sells
        DB::table('product_up_sells')->insert([
            ['parent_id' => 1, 'child_id' => 4],
            ['parent_id' => 2, 'child_id' => 5],
            ['parent_id' => 3, 'child_id' => 1],
            ['parent_id' => 8, 'child_id' => 10],
        ]);

        // 9. Insert cross-sells
        DB::table('product_cross_sells')->insert([
            ['parent_id' => 4, 'child_id' => 1],
            ['parent_id' => 5, 'child_id' => 2],
            ['parent_id' => 1, 'child_id' => 3],
            ['parent_id' => 8, 'child_id' => 9],
        ]);

        // 10. Add super attributes for configurable product
        DB::table('product_super_attributes')->insert([
            ['product_id' => 8, 'attribute_id' => 23], // color
            ['product_id' => 8, 'attribute_id' => 24], // size
        ]);
    }

    /**
     * Prepare products data.
     */
    public function prepareProductsData(): void
    {
        $this->products = [
            // Simple Products - Electronics
            [
                'product_id'           => 1,
                'sku'                  => 'EL-SMART-WATCH-001',
                'type'                 => 'simple',
                'parent_id'            => null,
                'attribute_family_id'  => 1,
                'name'                 => 'Smart Watch Pro X1',
                'short_description'    => 'Advanced smartwatch with health monitoring features',
                'description'          => 'The Smart Watch Pro X1 features a 1.4" AMOLED display, heart rate monitoring, blood oxygen tracking, GPS, and 7 days battery life. Water resistant to 50 meters.',
                'url_key'              => 'smart-watch-pro-x1',
                'new'                  => 1,
                'featured'             => 1,
                'status'               => 1,
                'meta_title'           => 'Smart Watch Pro X1 - Advanced Health Monitoring',
                'meta_keywords'        => 'smartwatch, fitness tracker, health monitor, GPS watch',
                'meta_description'     => 'Advanced smartwatch with health monitoring features',
                'price'                => 249.99,
                'special_price'        => 199.99,
                'special_price_from'   => Carbon::now()->format('Y-m-d'),
                'special_price_to'     => Carbon::now()->addDays(30)->format('Y-m-d'),
                'weight'               => 0.35,
                'visible_individually' => 1,
                'category_id'          => 5, // Mobile Phones sub-category
                'color'                => null,
                'size'                 => null,
                'manage_stock'         => 1,
                'guest_checkout'       => 1,
                'product_number'       => 'SWP-1001',
            ],
            [
                'product_id'           => 2,
                'sku'                  => 'EL-WIRELESS-EARBUDS-002',
                'type'                 => 'simple',
                'parent_id'            => null,
                'attribute_family_id'  => 1,
                'name'                 => 'Wireless Noise-Cancelling Earbuds',
                'short_description'    => 'Premium wireless earbuds with active noise cancellation',
                'description'          => 'Experience crystal clear audio with our Wireless Noise-Cancelling Earbuds. Features Bluetooth 5.2, 8 hours playback, IPX5 water resistance, and comfortable ergonomic design.',
                'url_key'              => 'wireless-noise-cancelling-earbuds',
                'new'                  => 1,
                'featured'             => 0,
                'status'               => 1,
                'meta_title'           => 'Wireless Noise-Cancelling Earbuds - Premium Sound',
                'meta_keywords'        => 'earbuds, wireless, noise cancelling, bluetooth earphones',
                'meta_description'     => 'Premium wireless earbuds with active noise cancellation',
                'price'                => 179.99,
                'special_price'        => 149.99,
                'special_price_from'   => Carbon::now()->format('Y-m-d'),
                'special_price_to'     => Carbon::now()->addDays(15)->format('Y-m-d'),
                'weight'               => 0.12,
                'visible_individually' => 1,
                'category_id'          => 5, // Mobile Phones sub-category
                'color'                => null,
                'size'                 => null,
                'manage_stock'         => 1,
                'guest_checkout'       => 1,
                'product_number'       => 'WNE-2002',
            ],
            [
                'product_id'           => 3,
                'sku'                  => 'EL-LAPTOP-003',
                'type'                 => 'simple',
                'parent_id'            => null,
                'attribute_family_id'  => 1,
                'name'                 => 'UltraBook Pro 15" Laptop',
                'short_description'    => 'Powerful laptop for professionals with 16GB RAM and 512GB SSD',
                'description'          => 'The UltraBook Pro features an Intel i7 processor, 16GB DDR5 RAM, 512GB NVMe SSD, 15.6" FHD display, backlit keyboard, and up to 12 hours battery life. Perfect for professionals and creators.',
                'url_key'              => 'ultrabook-pro-15-laptop',
                'new'                  => 0,
                'featured'             => 1,
                'status'               => 1,
                'meta_title'           => 'UltraBook Pro 15" Laptop - i7 16GB RAM 512GB SSD',
                'meta_keywords'        => 'laptop, ultrabook, i7, 16GB RAM, SSD, professional',
                'meta_description'     => 'Powerful laptop for professionals',
                'price'                => 1299.99,
                'special_price'        => 1099.99,
                'special_price_from'   => Carbon::now()->format('Y-m-d'),
                'special_price_to'     => Carbon::now()->addDays(45)->format('Y-m-d'),
                'weight'               => 1.8,
                'visible_individually' => 1,
                'category_id'          => 2, // Electronics
                'color'                => null,
                'size'                 => null,
                'manage_stock'         => 1,
                'guest_checkout'       => 1,
                'product_number'       => 'UBP-3003',
            ],

            // Simple Products - Clothing
            [
                'product_id'           => 4,
                'sku'                  => 'CL-COTTON-TSHIRT-004',
                'type'                 => 'simple',
                'parent_id'            => null,
                'attribute_family_id'  => 1,
                'name'                 => 'Premium Cotton T-Shirt',
                'short_description'    => 'Comfortable 100% organic cotton t-shirt',
                'description'          => 'Made from 100% organic cotton, this premium t-shirt offers exceptional comfort and breathability. Features a classic fit, reinforced stitching, and pre-shrunk fabric.',
                'url_key'              => 'premium-cotton-t-shirt',
                'new'                  => 1,
                'featured'             => 1,
                'status'               => 1,
                'meta_title'           => 'Premium Cotton T-Shirt - Organic Comfort',
                'meta_keywords'        => 't-shirt, cotton, organic, casual wear',
                'meta_description'     => 'Comfortable 100% organic cotton t-shirt',
                'price'                => 39.99,
                'special_price'        => null,
                'special_price_from'   => null,
                'special_price_to'     => null,
                'weight'               => 0.25,
                'visible_individually' => 1,
                'category_id'          => 6, // Men's Fashion
                'color'                => null,
                'size'                 => null,
                'manage_stock'         => 1,
                'guest_checkout'       => 1,
                'product_number'       => 'PCT-4004',
            ],
            [
                'product_id'           => 5,
                'sku'                  => 'CL-DENIM-JACKET-005',
                'type'                 => 'simple',
                'parent_id'            => null,
                'attribute_family_id'  => 1,
                'name'                 => 'Classic Denim Jacket',
                'short_description'    => 'Timeless denim jacket with modern fit',
                'description'          => 'A classic denim jacket crafted from premium quality denim. Features a modern slim fit, brass buttons, adjustable waist tabs, and two chest pockets with button closure.',
                'url_key'              => 'classic-denim-jacket',
                'new'                  => 0,
                'featured'             => 1,
                'status'               => 1,
                'meta_title'           => 'Classic Denim Jacket - Timeless Style',
                'meta_keywords'        => 'denim jacket, classic, fashion, outerwear',
                'meta_description'     => 'Timeless denim jacket with modern fit',
                'price'                => 89.99,
                'special_price'        => 74.99,
                'special_price_from'   => Carbon::now()->format('Y-m-d'),
                'special_price_to'     => Carbon::now()->addDays(20)->format('Y-m-d'),
                'weight'               => 0.8,
                'visible_individually' => 1,
                'category_id'          => 6, // Men's Fashion
                'color'                => null,
                'size'                 => null,
                'manage_stock'         => 1,
                'guest_checkout'       => 1,
                'product_number'       => 'CDJ-5005',
            ],

            // Simple Products - Home & Garden
            [
                'product_id'           => 6,
                'sku'                  => 'HG-ERGO-CHAIR-006',
                'type'                 => 'simple',
                'parent_id'            => null,
                'attribute_family_id'  => 1,
                'name'                 => 'Ergonomic Office Chair',
                'short_description'    => 'Premium ergonomic chair with lumbar support',
                'description'          => 'Professional ergonomic office chair with adjustable lumbar support, 3D armrests, breathable mesh back, seat height adjustment, and 135° tilt lock mechanism. Supports up to 300 lbs.',
                'url_key'              => 'ergonomic-office-chair',
                'new'                  => 1,
                'featured'             => 0,
                'status'               => 1,
                'meta_title'           => 'Ergonomic Office Chair - Premium Comfort',
                'meta_keywords'        => 'office chair, ergonomic, lumbar support, mesh chair',
                'meta_description'     => 'Premium ergonomic chair with lumbar support',
                'price'                => 449.99,
                'special_price'        => 399.99,
                'special_price_from'   => Carbon::now()->format('Y-m-d'),
                'special_price_to'     => Carbon::now()->addDays(25)->format('Y-m-d'),
                'weight'               => 18.5,
                'visible_individually' => 1,
                'category_id'          => 7, // Furniture
                'color'                => null,
                'size'                 => null,
                'manage_stock'         => 1,
                'guest_checkout'       => 1,
                'product_number'       => 'EOC-6006',
            ],
            [
                'product_id'           => 7,
                'sku'                  => 'HG-INDOOR-PLANT-007',
                'type'                 => 'simple',
                'parent_id'            => null,
                'attribute_family_id'  => 1,
                'name'                 => 'Indoor Plant Collection - 3 Pack',
                'short_description'    => 'Set of 3 easy-care indoor plants with pots',
                'description'          => 'Transform your space with our Indoor Plant Collection. Includes 3 easy-care plants (Snake Plant, Pothos, ZZ Plant) in modern ceramic pots. Perfect for beginners and experienced plant parents alike.',
                'url_key'              => 'indoor-plant-collection-3-pack',
                'new'                  => 1,
                'featured'             => 0,
                'status'               => 1,
                'meta_title'           => 'Indoor Plant Collection - 3 Easy-Care Plants',
                'meta_keywords'        => 'indoor plants, house plants, snake plant, pothos, ZZ plant',
                'meta_description'     => 'Set of 3 easy-care indoor plants with pots',
                'price'                => 79.99,
                'special_price'        => null,
                'special_price_from'   => null,
                'special_price_to'     => null,
                'weight'               => 3.5,
                'visible_individually' => 1,
                'category_id'          => 4, // Home & Garden
                'color'                => null,
                'size'                 => null,
                'manage_stock'         => 1,
                'guest_checkout'       => 1,
                'product_number'       => 'IPC-7007',
            ],

            // Configurable Product (with variants)
            [
                'product_id'           => 8,
                'sku'                  => 'CF-SNEAKERS-008',
                'type'                 => 'configurable',
                'parent_id'            => null,
                'attribute_family_id'  => 1,
                'name'                 => 'Athletic Running Sneakers',
                'short_description'    => 'High-performance running shoes with responsive cushioning',
                'description'          => 'Engineered for peak performance, these Athletic Running Sneakers feature responsive cushioning, breathable mesh upper, durable rubber outsole, and ergonomic arch support. Available in multiple colors and sizes.',
                'url_key'              => 'athletic-running-sneakers',
                'new'                  => 1,
                'featured'             => 1,
                'status'               => 1,
                'meta_title'           => 'Athletic Running Sneakers - Performance Footwear',
                'meta_keywords'        => 'running shoes, sneakers, athletic footwear, sports shoes',
                'meta_description'     => 'High-performance running shoes with responsive cushioning',
                'price'                => null,
                'special_price'        => null,
                'special_price_from'   => null,
                'special_price_to'     => null,
                'weight'               => null,
                'visible_individually' => 1,
                'category_id'          => 3, // Clothing
                'color'                => null,
                'size'                 => null,
                'manage_stock'         => 1,
                'guest_checkout'       => 1,
                'product_number'       => 'ARS-8008',
            ],

            // Configurable Variant 1 - Red / Size 9
            [
                'product_id'           => 9,
                'sku'                  => 'CF-SNEAKERS-008-RED-9',
                'type'                 => 'simple',
                'parent_id'            => 8,
                'attribute_family_id'  => 1,
                'name'                 => 'Athletic Running Sneakers - Red / Size 9',
                'short_description'    => 'Red athletic sneakers size 9',
                'description'          => 'Red variant of our Athletic Running Sneakers in size 9.',
                'url_key'              => 'athletic-running-sneakers-red-9',
                'new'                  => 1,
                'featured'             => 0,
                'status'               => 1,
                'meta_title'           => 'Athletic Running Sneakers - Red / Size 9',
                'meta_keywords'        => 'red sneakers, running shoes size 9',
                'meta_description'     => 'Red athletic sneakers size 9',
                'price'                => 129.99,
                'special_price'        => 109.99,
                'special_price_from'   => Carbon::now()->format('Y-m-d'),
                'special_price_to'     => Carbon::now()->addDays(14)->format('Y-m-d'),
                'weight'               => 0.65,
                'visible_individually' => 1,
                'category_id'          => 3,
                'color'                => 1, // attribute option id for Red
                'size'                 => 7, // attribute option id for M (size 9)
                'manage_stock'         => 1,
                'guest_checkout'       => 1,
                'product_number'       => 'ARS-8009',
            ],

            // Configurable Variant 2 - Blue / Size 9
            [
                'product_id'           => 10,
                'sku'                  => 'CF-SNEAKERS-008-BLUE-9',
                'type'                 => 'simple',
                'parent_id'            => 8,
                'attribute_family_id'  => 1,
                'name'                 => 'Athletic Running Sneakers - Blue / Size 9',
                'short_description'    => 'Blue athletic sneakers size 9',
                'description'          => 'Blue variant of our Athletic Running Sneakers in size 9.',
                'url_key'              => 'athletic-running-sneakers-blue-9',
                'new'                  => 0,
                'featured'             => 0,
                'status'               => 1,
                'meta_title'           => 'Athletic Running Sneakers - Blue / Size 9',
                'meta_keywords'        => 'blue sneakers, running shoes size 9',
                'meta_description'     => 'Blue athletic sneakers size 9',
                'price'                => 129.99,
                'special_price'        => null,
                'special_price_from'   => null,
                'special_price_to'     => null,
                'weight'               => 0.65,
                'visible_individually' => 1,
                'category_id'          => 3,
                'color'                => 2, // attribute option id for Green (using as Blue)
                'size'                 => 7, // attribute option id for M (size 9)
                'manage_stock'         => 1,
                'guest_checkout'       => 1,
                'product_number'       => 'ARS-8010',
            ],

            // Configurable Variant 3 - Black / Size 10
            [
                'product_id'           => 11,
                'sku'                  => 'CF-SNEAKERS-008-BLACK-10',
                'type'                 => 'simple',
                'parent_id'            => 8,
                'attribute_family_id'  => 1,
                'name'                 => 'Athletic Running Sneakers - Black / Size 10',
                'short_description'    => 'Black athletic sneakers size 10',
                'description'          => 'Black variant of our Athletic Running Sneakers in size 10.',
                'url_key'              => 'athletic-running-sneakers-black-10',
                'new'                  => 0,
                'featured'             => 1,
                'status'               => 1,
                'meta_title'           => 'Athletic Running Sneakers - Black / Size 10',
                'meta_keywords'        => 'black sneakers, running shoes size 10',
                'meta_description'     => 'Black athletic sneakers size 10',
                'price'                => 139.99,
                'special_price'        => 119.99,
                'special_price_from'   => Carbon::now()->format('Y-m-d'),
                'special_price_to'     => Carbon::now()->addDays(21)->format('Y-m-d'),
                'weight'               => 0.7,
                'visible_individually' => 1,
                'category_id'          => 3,
                'color'                => 4, // attribute option id for Black
                'size'                 => 8, // attribute option id for L (size 10)
                'manage_stock'         => 1,
                'guest_checkout'       => 1,
                'product_number'       => 'ARS-8011',
            ],
        ];
    }
}