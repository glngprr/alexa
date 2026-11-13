<?php

namespace Database\Seeders;

use App\Models\Collection;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $collectionsData = [
            [
                'name' => 'Alexa Core',
                'slug' => 'alexa-core',
                'description' => 'Everyday essentials defined by simplicity, refined proportions, and understated character.',
                'products' => [
                    [
                        'name' => 'Alexa Forma',
                        'slug' => 'alexa-forma',
                        'description' => 'Alexa Forma is sculpted around pure, minimal geometry. Its clean silhouette, balanced proportions, and double-wall vacuum insulation make it an enduring companion for desktop work, focused routines, and daily hydration.',
                        'capacity' => '500 ml',
                        'material' => '18/8 stainless steel',
                        'lid_type' => 'Screw cap',
                        'weight' => '±280g',
                        'status' => 'active',
                        'variants' => [
                            [
                                'name' => 'Forma Ivory',
                                'color_name' => 'Ivory',
                                'color_hex' => '#F2EEE6',
                                'sku' => 'ALX-FRM-IVY',
                                'price' => 899000,
                                'stock' => 20,
                                'status' => 'active',
                                'image' => 'images/products/core-forma-ivory.png',
                            ],
                            [
                                'name' => 'Forma Lavender',
                                'color_name' => 'Lavender',
                                'color_hex' => '#C8BDD9',
                                'sku' => 'ALX-FRM-LAV',
                                'price' => 899000,
                                'stock' => 20,
                                'status' => 'active',
                                'image' => 'images/products/core-forma-lavender.png',
                            ],
                            [
                                'name' => 'Forma Midnight',
                                'color_name' => 'Midnight',
                                'color_hex' => '#1C1C1C',
                                'sku' => 'ALX-FRM-MID',
                                'price' => 899000,
                                'stock' => 20,
                                'status' => 'active',
                                'image' => 'images/products/core-forma-midnight.png',
                            ],
                            [
                                'name' => 'Forma Sage',
                                'color_name' => 'Sage',
                                'color_hex' => '#A8B5A2',
                                'sku' => 'ALX-FRM-SAG',
                                'price' => 899000,
                                'stock' => 20,
                                'status' => 'active',
                                'image' => 'images/products/core-forma-sage.png',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Alexa Sora',
                        'slug' => 'alexa-sora',
                        'description' => 'Alexa Sora brings an elevated, compact profile designed for light travel, gentle transitions, and quiet moments. Compact yet thermally resolute, it slips effortlessly into everyday carry bags.',
                        'capacity' => '350 ml',
                        'material' => '18/8 stainless steel',
                        'lid_type' => 'Insulated screw cap with silicone seal',
                        'weight' => '±240g',
                        'status' => 'active',
                        'variants' => [
                            [
                                'name' => 'Sora Lavender',
                                'color_name' => 'Lavender',
                                'color_hex' => '#C8BDD9',
                                'sku' => 'ALX-SRA-LAV',
                                'price' => 849000,
                                'stock' => 20,
                                'status' => 'active',
                                'image' => 'images/products/core-sora-lavender.png',
                            ],
                            [
                                'name' => 'Sora Sage',
                                'color_name' => 'Sage',
                                'color_hex' => '#A8B5A2',
                                'sku' => 'ALX-SRA-SAG',
                                'price' => 849000,
                                'stock' => 20,
                                'status' => 'active',
                                'image' => 'images/products/core-sora-sage.png',
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
                        'name' => 'Alexa Flow',
                        'slug' => 'alexa-flow',
                        'description' => 'Alexa Flow balances performance and aesthetic poise. Designed for training sessions, fitness commutes, and fluid mobility, it features an ergonomic sport spout and an integrated tactile carry handle.',
                        'capacity' => '600 ml',
                        'material' => '18/8 stainless steel',
                        'lid_type' => 'Sport spout with integrated carry handle',
                        'weight' => '±320g',
                        'status' => 'active',
                        'variants' => [
                            [
                                'name' => 'Flow Blush',
                                'color_name' => 'Blush',
                                'color_hex' => '#DDB8B5',
                                'sku' => 'ALX-FLW-BLS',
                                'price' => 1099000,
                                'stock' => 20,
                                'status' => 'active',
                                'image' => 'images/products/active-flow-blush.png',
                            ],
                            [
                                'name' => 'Flow Eclipse',
                                'color_name' => 'Eclipse',
                                'color_hex' => '#292929',
                                'sku' => 'ALX-FLW-ECL',
                                'price' => 1099000,
                                'stock' => 20,
                                'status' => 'active',
                                'image' => 'images/products/active-flow-eclipse.png',
                            ],
                            [
                                'name' => 'Flow Sky',
                                'color_name' => 'Sky',
                                'color_hex' => '#AFC7D8',
                                'sku' => 'ALX-FLW-SKY',
                                'price' => 1099000,
                                'stock' => 20,
                                'status' => 'active',
                                'image' => 'images/products/active-flow-sky.png',
                            ],
                            [
                                'name' => 'Flow Snow',
                                'color_name' => 'Snow',
                                'color_hex' => '#F5F5F3',
                                'sku' => 'ALX-FLW-SNW',
                                'price' => 1099000,
                                'stock' => 20,
                                'status' => 'active',
                                'image' => 'images/products/active-flow-snow.png',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Alexa Pulse',
                        'slug' => 'alexa-pulse',
                        'description' => 'Alexa Pulse is engineered for high-energy routines. Built with a rapid-hydration sip valve and streamlined grip, it delivers instant hydration without interrupting your pace.',
                        'capacity' => '500 ml',
                        'material' => '18/8 stainless steel',
                        'lid_type' => 'High-flow sport lid with textured grip',
                        'weight' => '±300g',
                        'status' => 'active',
                        'variants' => [
                            [
                                'name' => 'Pulse Mist',
                                'color_name' => 'Mist',
                                'color_hex' => '#D4DCDD',
                                'sku' => 'ALX-PLS-MST',
                                'price' => 1049000,
                                'stock' => 20,
                                'status' => 'active',
                                'image' => 'images/products/active-pulse-mist.png',
                            ],
                            [
                                'name' => 'Pulse Onyx',
                                'color_name' => 'Onyx',
                                'color_hex' => '#111111',
                                'sku' => 'ALX-PLS-ONX',
                                'price' => 1049000,
                                'stock' => 20,
                                'status' => 'active',
                                'image' => 'images/products/active-pulse-onyx.png',
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
                        'name' => 'Alexa Rover',
                        'slug' => 'alexa-rover',
                        'description' => 'Alexa Rover is built for extended expeditions and all-day journeys. Its wide-mouth opening simplifies ice filling and thorough cleaning, while rugged double-wall construction withstands outdoor demands.',
                        'capacity' => '750 ml',
                        'material' => '18/8 stainless steel',
                        'lid_type' => 'Wide-mouth with integrated carry handle',
                        'weight' => '±390g',
                        'status' => 'active',
                        'variants' => [
                            [
                                'name' => 'Rover Ivory',
                                'color_name' => 'Ivory',
                                'color_hex' => '#F2EEE6',
                                'sku' => 'ALX-RVR-IVY',
                                'price' => 1299000,
                                'stock' => 20,
                                'status' => 'active',
                                'image' => 'images/products/trek-rover-ivory.png',
                            ],
                            [
                                'name' => 'Rover Midnight',
                                'color_name' => 'Midnight',
                                'color_hex' => '#1C1C1C',
                                'sku' => 'ALX-RVR-MID',
                                'price' => 1299000,
                                'stock' => 20,
                                'status' => 'active',
                                'image' => 'images/products/trek-rover-midnight.png',
                            ],
                            [
                                'name' => 'Rover Onyx',
                                'color_name' => 'Onyx',
                                'color_hex' => '#111111',
                                'sku' => 'ALX-RVR-ONX',
                                'price' => 1299000,
                                'stock' => 20,
                                'status' => 'active',
                                'image' => 'images/products/trek-rover-onyx.png',
                            ],
                            [
                                'name' => 'Rover Steel',
                                'color_name' => 'Steel',
                                'color_hex' => '#8C9296',
                                'sku' => 'ALX-RVR-STL',
                                'price' => 1299000,
                                'stock' => 20,
                                'status' => 'active',
                                'image' => 'images/products/trek-rover-steel.png',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Alexa Summit',
                        'slug' => 'alexa-summit',
                        'description' => 'Alexa Summit offers high-volume endurance hydration. Engineered for remote trails, road journeys, and backcountry treks, it preserves ice-cold refreshment across 24+ hours.',
                        'capacity' => '950 ml',
                        'material' => '18/8 stainless steel',
                        'lid_type' => 'Heavy-duty insulated loop cap',
                        'weight' => '±450g',
                        'status' => 'active',
                        'variants' => [
                            [
                                'name' => 'Summit Blush',
                                'color_name' => 'Blush',
                                'color_hex' => '#DDB8B5',
                                'sku' => 'ALX-SMT-BLS',
                                'price' => 1399000,
                                'stock' => 20,
                                'status' => 'active',
                                'image' => 'images/products/trek-summit-blush.png',
                            ],
                            [
                                'name' => 'Summit Obsidian',
                                'color_name' => 'Obsidian',
                                'color_hex' => '#1A1A1A',
                                'sku' => 'ALX-SMT-OBS',
                                'price' => 1399000,
                                'stock' => 20,
                                'status' => 'active',
                                'image' => 'images/products/trek-summit-obsidian.png',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $newProductSlugs = [];
        $newVariantSkus = [];

        foreach ($collectionsData as $collData) {
            $collection = Collection::updateOrCreate(
                ['slug' => $collData['slug']],
                [
                    'name' => $collData['name'],
                    'description' => $collData['description'],
                ]
            );

            foreach ($collData['products'] as $prodData) {
                $newProductSlugs[] = $prodData['slug'];

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
                    $newVariantSkus[] = $varData['sku'];

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

        // Clean up obsolete products/variants if they have no associated orders
        ProductVariant::whereNotIn('sku', $newVariantSkus)->doesntHave('orderItems')->delete();
        Product::whereNotIn('slug', $newProductSlugs)->doesntHave('variants.orderItems')->delete();
        });
    }
}
