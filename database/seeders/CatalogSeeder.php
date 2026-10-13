<?php

namespace Database\Seeders;

use App\Models\Collection;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $collectionsData = [
            [
                'name' => 'Alexa Core',
                'slug' => 'alexa-core',
                'description' => 'Everyday essentials defined by simplicity, refined proportions, and understated character.',
                'products' => [
                    [
                        'name' => 'Alexa Core',
                        'slug' => 'alexa-core',
                        'description' => 'Alexa Core is designed around simplicity. Its clean silhouette, considered proportions, and insulated stainless steel body make it a refined companion for everyday routines. From the first coffee of the morning to a long afternoon at your desk, Core keeps hydration within reach without compromising your sense of style.',
                        'capacity' => '500 ml',
                        'material' => '18/8 stainless steel',
                        'lid_type' => 'Screw cap',
                        'weight' => '±280g',
                        'status' => 'active',
                        'variants' => [
                            [
                                'name' => 'Core Midnight',
                                'color_name' => 'Midnight',
                                'color_hex' => '#1C1C1C',
                                'sku' => 'ALX-CORE-MID',
                                'price' => 899000,
                                'stock' => 20,
                                'status' => 'active',
                                'image' => 'images/products/core-midnight.png',
                            ],
                            [
                                'name' => 'Core Sage',
                                'color_name' => 'Sage',
                                'color_hex' => '#A8B5A2',
                                'sku' => 'ALX-CORE-SAG',
                                'price' => 899000,
                                'stock' => 20,
                                'status' => 'active',
                                'image' => 'images/products/core-sage.png',
                            ],
                            [
                                'name' => 'Core Ivory',
                                'color_name' => 'Ivory',
                                'color_hex' => '#F2EEE6',
                                'sku' => 'ALX-CORE-IVY',
                                'price' => 899000,
                                'stock' => 20,
                                'status' => 'active',
                                'image' => 'images/products/core-ivory.png',
                            ],
                            [
                                'name' => 'Core Lavender',
                                'color_name' => 'Lavender',
                                'color_hex' => '#C8BDD9',
                                'sku' => 'ALX-CORE-LAV',
                                'price' => 899000,
                                'stock' => 20,
                                'status' => 'active',
                                'image' => 'images/products/core-lavender.png',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Alexa Active',
                'slug' => 'alexa-active',
                'description' => 'Functional hydration designed for movement, active routines, and life on the go.',
                'products' => [
                    [
                        'name' => 'Alexa Active',
                        'slug' => 'alexa-active',
                        'description' => "Alexa Active brings function into a refined form. Designed for movement, its sport spout and integrated carry handle make hydration convenient whether you're heading to the gym, commuting across the city, or spending the day outdoors.",
                        'capacity' => '600 ml',
                        'material' => '18/8 stainless steel',
                        'lid_type' => 'Sport spout with integrated carry handle',
                        'weight' => '±320g',
                        'status' => 'active',
                        'variants' => [
                            [
                                'name' => 'Active Snow',
                                'color_name' => 'Snow',
                                'color_hex' => '#F5F5F3',
                                'sku' => 'ALX-ACT-SNW',
                                'price' => 1099000,
                                'stock' => 20,
                                'status' => 'active',
                                'image' => 'images/products/active-snow.png',
                            ],
                            [
                                'name' => 'Active Eclipse',
                                'color_name' => 'Eclipse',
                                'color_hex' => '#292929',
                                'sku' => 'ALX-ACT-ECL',
                                'price' => 1099000,
                                'stock' => 20,
                                'status' => 'active',
                                'image' => 'images/products/active-eclipse.png',
                            ],
                            [
                                'name' => 'Active Sky',
                                'color_name' => 'Sky',
                                'color_hex' => '#AFC7D8',
                                'sku' => 'ALX-ACT-SKY',
                                'price' => 1099000,
                                'stock' => 20,
                                'status' => 'active',
                                'image' => 'images/products/active-sky.png',
                            ],
                            [
                                'name' => 'Active Blush',
                                'color_name' => 'Blush',
                                'color_hex' => '#DDB8B5',
                                'sku' => 'ALX-ACT-BLS',
                                'price' => 1099000,
                                'stock' => 20,
                                'status' => 'active',
                                'image' => 'images/products/active-blush.png',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Alexa Trek',
                'slug' => 'alexa-trek',
                'description' => 'Durable everyday hydration designed for longer days, travel, and outdoor journeys.',
                'products' => [
                    [
                        'name' => 'Alexa Trek',
                        'slug' => 'alexa-trek',
                        'description' => 'Alexa Trek is designed for days that take you further. Its larger capacity, wide-mouth design, and integrated carry handle make it suited to travel and outdoor use while maintaining the refined character of the Alexa collection.',
                        'capacity' => '750 ml',
                        'material' => '18/8 stainless steel',
                        'lid_type' => 'Wide-mouth with integrated carry handle',
                        'weight' => '±390g',
                        'status' => 'active',
                        'variants' => [
                            [
                                'name' => 'Trek Midnight',
                                'color_name' => 'Midnight',
                                'color_hex' => '#1C1C1C',
                                'sku' => 'ALX-TRK-MID',
                                'price' => 1299000,
                                'stock' => 20,
                                'status' => 'active',
                                'image' => 'images/products/trek-midnight.png',
                            ],
                            [
                                'name' => 'Trek Steel',
                                'color_name' => 'Steel',
                                'color_hex' => '#8C9296',
                                'sku' => 'ALX-TRK-STL',
                                'price' => 1299000,
                                'stock' => 20,
                                'status' => 'active',
                                'image' => 'images/products/trek-steel.png',
                            ],
                            [
                                'name' => 'Trek Sand',
                                'color_name' => 'Sand',
                                'color_hex' => '#C8B89E',
                                'sku' => 'ALX-TRK-SND',
                                'price' => 1299000,
                                'stock' => 20,
                                'status' => 'active',
                                'image' => 'images/products/trek-sand.png',
                            ],
                            [
                                'name' => 'Trek Onyx',
                                'color_name' => 'Onyx',
                                'color_hex' => '#111111',
                                'sku' => 'ALX-TRK-ONX',
                                'price' => 1299000,
                                'stock' => 20,
                                'status' => 'active',
                                'image' => 'images/products/trek-onyx.png',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($collectionsData as $collData) {
            $collection = Collection::updateOrCreate(
                ['slug' => $collData['slug']],
                [
                    'name' => $collData['name'],
                    'description' => $collData['description'],
                ]
            );

            foreach ($collData['products'] as $prodData) {
                $product = Product::updateOrCreate(
                    ['slug' => $prodData['slug']],
                    [
                        'collection_id' => $collection->id,
                        'name' => $prodData['name'],
                        'description' => $prodData['description'],
                        'capacity' => $prodData['capacity'],
                        'material' => $prodData['material'],
                        'lid_type' => $prodData['lid_type'],
                        'weight' => $prodData['weight'],
                        'status' => $prodData['status'],
                    ]
                );

                foreach ($prodData['variants'] as $varData) {
                    ProductVariant::updateOrCreate(
                        ['sku' => $varData['sku']],
                        [
                            'product_id' => $product->id,
                            'name' => $varData['name'],
                            'color_name' => $varData['color_name'],
                            'color_hex' => $varData['color_hex'],
                            'price' => $varData['price'],
                            'stock' => $varData['stock'],
                            'status' => $varData['status'],
                            'image' => $varData['image'],
                        ]
                    );
                }
            }
        }
    }
}
