<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Seed categories and sub-categories.
     */
    public function run(): void
    {
        // Clean existing data
        DB::table('category_filterable_attributes')->delete();
        DB::table('category_translations')->delete();
        DB::table('categories')->delete();

        $now = Carbon::now();

        // Insert root category
        DB::table('categories')->insert([
            [
                'id'          => 1,
                'position'    => 1,
                'logo_path'   => null,
                'status'      => 1,
                '_lft'        => 1,
                '_rgt'        => 14,
                'parent_id'   => null,
                'banner_path' => null,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
        ]);

        // Insert main categories (level 1)
        DB::table('categories')->insert([
            [
                'id'            => 2,
                'position'      => 1,
                'logo_path'     => null,
                'status'        => 1,
                'display_mode'  => 'products_and_description',
                '_lft'          => 2,
                '_rgt'          => 5,
                'parent_id'     => 1,
                'additional'    => null,
                'banner_path'   => null,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'id'            => 3,
                'position'      => 2,
                'logo_path'     => null,
                'status'        => 1,
                'display_mode'  => 'products_and_description',
                '_lft'          => 6,
                '_rgt'          => 9,
                'parent_id'     => 1,
                'additional'    => null,
                'banner_path'   => null,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'id'            => 4,
                'position'      => 3,
                'logo_path'     => null,
                'status'        => 1,
                'display_mode'  => 'products_and_description',
                '_lft'          => 10,
                '_rgt'          => 13,
                'parent_id'     => 1,
                'additional'    => null,
                'banner_path'   => null,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
        ]);

        // Insert sub-categories (level 2)
        DB::table('categories')->insert([
            [
                'id'            => 5,
                'position'      => 1,
                'logo_path'     => null,
                'status'        => 1,
                'display_mode'  => 'products_and_description',
                '_lft'          => 3,
                '_rgt'          => 4,
                'parent_id'     => 2,
                'additional'    => null,
                'banner_path'   => null,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'id'            => 6,
                'position'      => 2,
                'logo_path'     => null,
                'status'        => 1,
                'display_mode'  => 'products_and_description',
                '_lft'          => 7,
                '_rgt'          => 8,
                'parent_id'     => 3,
                'additional'    => null,
                'banner_path'   => null,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'id'            => 7,
                'position'      => 3,
                'logo_path'     => null,
                'status'        => 1,
                'display_mode'  => 'products_and_description',
                '_lft'          => 11,
                '_rgt'          => 12,
                'parent_id'     => 4,
                'additional'    => null,
                'banner_path'   => null,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
        ]);

        // Insert category translations
        DB::table('category_translations')->insert([
            // Root category
            [
                'category_id'      => 1,
                'name'             => 'Root',
                'slug'             => 'root',
                'url_path'         => 'root',
                'description'      => 'Root Category',
                'meta_title'       => 'Root',
                'meta_description' => 'Root Category',
                'meta_keywords'    => 'root',
                'locale_id'        => null,
                'locale'           => 'en',
            ],
            // Main categories
            [
                'category_id'      => 2,
                'name'             => 'Electronics',
                'slug'             => 'electronics',
                'url_path'         => 'electronics',
                'description'      => 'Electronic devices and accessories',
                'meta_title'       => 'Electronics',
                'meta_description' => 'Shop the latest electronics',
                'meta_keywords'    => 'electronics, gadgets, devices',
                'locale_id'        => null,
                'locale'           => 'en',
            ],
            [
                'category_id'      => 3,
                'name'             => 'Clothing',
                'slug'             => 'clothing',
                'url_path'         => 'clothing',
                'description'      => 'Fashion and apparel for all',
                'meta_title'       => 'Clothing',
                'meta_description' => 'Trendy clothing collection',
                'meta_keywords'    => 'clothing, fashion, apparel',
                'locale_id'        => null,
                'locale'           => 'en',
            ],
            [
                'category_id'      => 4,
                'name'             => 'Home & Garden',
                'slug'             => 'home-garden',
                'url_path'         => 'home-garden',
                'description'      => 'Home improvement and garden supplies',
                'meta_title'       => 'Home & Garden',
                'meta_description' => 'Everything for your home and garden',
                'meta_keywords'    => 'home, garden, furniture',
                'locale_id'        => null,
                'locale'           => 'en',
            ],
            // Sub-categories
            [
                'category_id'      => 5,
                'name'             => 'Mobile Phones',
                'slug'             => 'mobile-phones',
                'url_path'         => 'electronics/mobile-phones',
                'description'      => 'Smartphones and accessories',
                'meta_title'       => 'Mobile Phones',
                'meta_description' => 'Latest smartphones',
                'meta_keywords'    => 'mobile, phones, smartphones',
                'locale_id'        => null,
                'locale'           => 'en',
            ],
            [
                'category_id'      => 6,
                'name'             => 'Men\'s Fashion',
                'slug'             => 'mens-fashion',
                'url_path'         => 'clothing/mens-fashion',
                'description'      => 'Men\'s clothing and accessories',
                'meta_title'       => 'Men\'s Fashion',
                'meta_description' => 'Men\'s fashion collection',
                'meta_keywords'    => 'men, fashion, clothing',
                'locale_id'        => null,
                'locale'           => 'en',
            ],
            [
                'category_id'      => 7,
                'name'             => 'Furniture',
                'slug'             => 'furniture',
                'url_path'         => 'home-garden/furniture',
                'description'      => 'Home furniture and decor',
                'meta_title'       => 'Furniture',
                'meta_description' => 'Quality home furniture',
                'meta_keywords'    => 'furniture, home, decor',
                'locale_id'        => null,
                'locale'           => 'en',
            ],
        ]);

        // Insert category filterable attributes
        DB::table('category_filterable_attributes')->insert([
            ['category_id' => 2, 'attribute_id' => 11], // price
            ['category_id' => 2, 'attribute_id' => 23], // color
            ['category_id' => 2, 'attribute_id' => 24], // size
            ['category_id' => 2, 'attribute_id' => 25], // brand
            ['category_id' => 3, 'attribute_id' => 11],
            ['category_id' => 3, 'attribute_id' => 23],
            ['category_id' => 3, 'attribute_id' => 24],
            ['category_id' => 3, 'attribute_id' => 25],
            ['category_id' => 4, 'attribute_id' => 11],
            ['category_id' => 4, 'attribute_id' => 25],
            ['category_id' => 5, 'attribute_id' => 11],
            ['category_id' => 5, 'attribute_id' => 25],
            ['category_id' => 6, 'attribute_id' => 11],
            ['category_id' => 6, 'attribute_id' => 23],
            ['category_id' => 6, 'attribute_id' => 24],
            ['category_id' => 7, 'attribute_id' => 11],
        ]);
    }
}