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

        $imageDir = public_path('img/category');

        // Supported extensions in public/img/category (your files may be .png / .pnj / .webp)
        $imageFilesPnj = glob($imageDir . '/*.pnj') ?: [];
        $imageFilesWebp = glob($imageDir . '/*.webp') ?: [];
        $imageFilesPng  = glob($imageDir . '/*.png') ?: [];

        $imageFiles = array_merge($imageFilesPnj, $imageFilesWebp, $imageFilesPng);

        $images = array_map(static function ($path) {
            return basename($path);
        }, $imageFiles);

        // Shuffle so each seed generates different mapping.
        shuffle($images);

        // Category set: 1 root + 6 main + 12 sub (randomly assign images)
        $main = [
            2 => ['name' => 'Electronics', 'slug' => 'electronics'],
            3 => ['name' => 'Clothing', 'slug' => 'clothing'],
            4 => ['name' => 'Home & Garden', 'slug' => 'home-garden'],
            8 => ['name' => 'Sports & Outdoors', 'slug' => 'sports-outdoors'],
            9 => ['name' => 'Beauty & Health', 'slug' => 'beauty-health'],
            10 => ['name' => 'Automotive', 'slug' => 'automotive'],
        ];

        // 12 sub categories: 2 for each main (keeps carousel populated and realistic)
        $sub = [
            5  => ['parent_id' => 2,  'name' => 'Mobile Phones',        'slug' => 'mobile-phones'],
            6  => ['parent_id' => 2,  'name' => 'Laptops & Tablets',    'slug' => 'laptops-tablets'],

            7  => ['parent_id' => 3,  'name' => "Men's Fashion",        'slug' => 'mens-fashion'],
            11 => ['parent_id' => 3,  'name' => "Women\u2019s Fashion",   'slug' => 'womens-fashion'],

            12 => ['parent_id' => 4,  'name' => 'Furniture',             'slug' => 'furniture'],
            13 => ['parent_id' => 4,  'name' => 'Kitchen Essentials',   'slug' => 'kitchen-essentials'],

            14 => ['parent_id' => 8,  'name' => 'Fitness Equipment',    'slug' => 'fitness-equipment'],
            15 => ['parent_id' => 8,  'name' => 'Camping & Hiking',     'slug' => 'camping-hiking'],

            16 => ['parent_id' => 9,  'name' => 'Skincare',             'slug' => 'skincare'],
            17 => ['parent_id' => 9,  'name' => 'Personal Care',        'slug' => 'personal-care'],

            18 => ['parent_id' => 10, 'name' => 'Car Accessories',      'slug' => 'car-accessories'],
            19 => ['parent_id' => 10, 'name' => 'Motor Oil & Fluids',  'slug' => 'motor-oil-fluids'],
        ];

        // ---- Nested set bookkeeping ----
        // Root gets lft=1, rgt = 2 * nodes_count - 1
        // With 1 root + 6 main + 12 sub = 19 nodes => root _rgt=38
        DB::table('categories')->insert([
            [
                'id'          => 1,
                'position'    => 1,
                'logo_path'   => null,
                'status'      => 1,
                '_lft'        => 1,
                '_rgt'        => 38,
                'parent_id'   => null,
                'banner_path' => null,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
        ]);

        // Precomputed _lft/_rgt per main with 2 sub nodes each:
        // Main i gets interval size 6: main + 2 sub => (main opens) + sub(2 each) => 6 positions
        // Sequence: main1:(2..7), main2:(8..13), main3:(14..19), main4:(20..25), main5:(26..31), main6:(32..37)
        $mainIntervals = [
            2  => ['lft' => 2,  'rgt' => 7],
            3  => ['lft' => 8,  'rgt' => 13],
            4  => ['lft' => 14, 'rgt' => 19],
            8  => ['lft' => 20, 'rgt' => 25],
            9  => ['lft' => 26, 'rgt' => 31],
            10 => ['lft' => 32, 'rgt' => 37],
        ];

        $subIntervals = [
            5  => ['lft' => 3,  'rgt' => 4],
            6  => ['lft' => 5,  'rgt' => 6],

            7  => ['lft' => 9,  'rgt' => 10],
            11 => ['lft' => 11, 'rgt' => 12],

            12 => ['lft' => 15, 'rgt' => 16],
            13 => ['lft' => 17, 'rgt' => 18],

            14 => ['lft' => 21, 'rgt' => 22],
            15 => ['lft' => 23, 'rgt' => 24],

            16 => ['lft' => 27, 'rgt' => 28],
            17 => ['lft' => 29, 'rgt' => 30],

            18 => ['lft' => 33, 'rgt' => 34],
            19 => ['lft' => 35, 'rgt' => 36],
        ];

        // Assign images: repeat if fewer than categories.
        $assignImagePath = static function () use (&$images) {
            if (empty($images)) {
                return null;
            }
            static $i = 0;
            $file = $images[$i % count($images)];
            $i++;

            // Bagisto uses `logo_path` as a stored path. We’ll store a path relative to `public/`.
            return 'img/category/' . $file;
        };

        // Insert main categories
        foreach ($main as $id => $data) {
            $interval = $mainIntervals[$id];

            DB::table('categories')->insert([
                [
                    'id'           => $id,
                    'position'     => (int) array_search($id, array_keys($main), true) + 1,
                    'logo_path'    => $assignImagePath(),
                    'status'       => 1,
                    'display_mode' => 'products_and_description',
                    '_lft'         => $interval['lft'],
                    '_rgt'         => $interval['rgt'],
                    'parent_id'    => 1,
                    'additional'   => null,
                    'banner_path'  => null,
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ],
            ]);
        }

        // Insert sub categories
        foreach ($sub as $id => $data) {
            $interval = $subIntervals[$id];

            DB::table('categories')->insert([
                [
                    'id'           => $id,
                    'position'     => (int) array_search($id, array_keys($sub), true) + 1,
                    'logo_path'    => $assignImagePath(),
                    'status'       => 1,
                    'display_mode' => 'products_and_description',
                    '_lft'         => $interval['lft'],
                    '_rgt'         => $interval['rgt'],
                    'parent_id'    => $data['parent_id'],
                    'additional'   => null,
                    'banner_path'  => null,
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ],
            ]);
        }

        // Insert translations (en)
        $translations = [];
        $translations[] = [
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
        ];

        foreach ($main as $id => $data) {
            $translations[] = [
                'category_id'      => $id,
                'name'             => $data['name'],
                'slug'             => $data['slug'],
                'url_path'         => $data['slug'],
                'description'      => $data['name'] . ' Category',
                'meta_title'       => $data['name'],
                'meta_description' => 'Shop ' . $data['name'],
                'meta_keywords'    => $data['slug'],
                'locale_id'        => null,
                'locale'           => 'en',
            ];
        }

        foreach ($sub as $id => $data) {
            $parentSlug = $main[$data['parent_id']]['slug'];
            $urlPath = $parentSlug . '/' . $data['slug'];

            $translations[] = [
                'category_id'      => $id,
                'name'             => $data['name'],
                'slug'             => $data['slug'],
                'url_path'         => $urlPath,
                'description'      => $data['name'] . ' Category',
                'meta_title'       => $data['name'],
                'meta_description' => 'Shop ' . $data['name'],
                'meta_keywords'    => $data['slug'],
                'locale_id'        => null,
                'locale'           => 'en',
            ];
        }

        DB::table('category_translations')->insert($translations);

        // Insert category filterable attributes
        // Use a simple pattern: apply basic filterable attributes to main categories.
        $filterable = [11, 23, 24, 25]; // price,color,size,brand
        $rows = [];
        foreach (array_keys($main) as $catId) {
            foreach ($filterable as $attributeId) {
                $rows[] = ['category_id' => $catId, 'attribute_id' => $attributeId];
            }
        }
        DB::table('category_filterable_attributes')->insert($rows);
    }
}

