<?php

namespace Tests\Feature;

use App\Models\Collection;
use App\Models\Product;
use App\Models\ProductVariant;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CatalogSeeder::class);
    }

    /**
     * 1. Catalog seeding creates exactly 3 collections.
     */
    public function test_catalog_seeding_creates_exactly_3_collections(): void
    {
        $this->assertDatabaseCount('collections', 3);
        $this->assertEquals(3, Collection::count());
    }

    /**
     * 2. Catalog seeding creates exactly 6 products.
     */
    public function test_catalog_seeding_creates_exactly_6_products(): void
    {
        $this->assertDatabaseCount('products', 6);
        $this->assertEquals(6, Product::count());
    }

    /**
     * 3. Catalog seeding creates exactly 18 variants.
     */
    public function test_catalog_seeding_creates_exactly_18_variants(): void
    {
        $this->assertDatabaseCount('product_variants', 18);
        $this->assertEquals(18, ProductVariant::count());
    }

    /**
     * 4. Variant distribution matches catalog specification (Forma 4, Sora 2, Flow 4, Pulse 2, Rover 4, Summit 2).
     */
    public function test_variant_distribution_matches_catalog_specification(): void
    {
        $forma = Product::where('slug', 'alexa-forma')->first();
        $sora = Product::where('slug', 'alexa-sora')->first();
        $flow = Product::where('slug', 'alexa-flow')->first();
        $pulse = Product::where('slug', 'alexa-pulse')->first();
        $rover = Product::where('slug', 'alexa-rover')->first();
        $summit = Product::where('slug', 'alexa-summit')->first();

        $this->assertNotNull($forma, 'Alexa Forma must exist in catalog.');
        $this->assertNotNull($sora, 'Alexa Sora must exist in catalog.');
        $this->assertNotNull($flow, 'Alexa Flow must exist in catalog.');
        $this->assertNotNull($pulse, 'Alexa Pulse must exist in catalog.');
        $this->assertNotNull($rover, 'Alexa Rover must exist in catalog.');
        $this->assertNotNull($summit, 'Alexa Summit must exist in catalog.');

        $this->assertCount(4, $forma->variants, 'Alexa Forma must have exactly 4 variants.');
        $this->assertCount(2, $sora->variants, 'Alexa Sora must have exactly 2 variants.');
        $this->assertCount(4, $flow->variants, 'Alexa Flow must have exactly 4 variants.');
        $this->assertCount(2, $pulse->variants, 'Alexa Pulse must have exactly 2 variants.');
        $this->assertCount(4, $rover->variants, 'Alexa Rover must have exactly 4 variants.');
        $this->assertCount(2, $summit->variants, 'Alexa Summit must have exactly 2 variants.');
    }

    /**
     * 5. Product/collection relationships work.
     */
    public function test_product_collection_relationships_work(): void
    {
        $collection = Collection::where('slug', 'alexa-core')->first();
        $this->assertNotNull($collection);
        $this->assertTrue($collection->products()->exists());
        $this->assertCount(2, $collection->products);

        $product = Product::where('slug', 'alexa-forma')->first();
        $this->assertNotNull($product);
        $this->assertInstanceOf(Collection::class, $product->collection);
        $this->assertEquals($collection->id, $product->collection->id);
    }

    /**
     * 6. Product/variant relationships work.
     */
    public function test_product_variant_relationships_work(): void
    {
        $product = Product::where('slug', 'alexa-forma')->first();
        $this->assertNotNull($product);

        $variant = $product->variants()->first();
        $this->assertNotNull($variant);
        $this->assertInstanceOf(Product::class, $variant->product);
        $this->assertEquals($product->id, $variant->product->id);
    }

    /**
     * 7. All SKUs are unique.
     */
    public function test_all_skus_are_unique(): void
    {
        $skus = ProductVariant::pluck('sku')->toArray();
        $this->assertCount(18, $skus);
        $this->assertCount(18, array_unique($skus));
    }

    /**
     * 8. Expected variant SKUs exist.
     */
    public function test_expected_variant_skus_exist(): void
    {
        $expectedSkus = [
            // Core: Forma (4), Sora (2)
            'ALX-FRM-IVY',
            'ALX-FRM-LAV',
            'ALX-FRM-MID',
            'ALX-FRM-SAG',
            'ALX-SRA-LAV',
            'ALX-SRA-SAG',

            // Active: Flow (4), Pulse (2)
            'ALX-FLW-BLS',
            'ALX-FLW-ECL',
            'ALX-FLW-SKY',
            'ALX-FLW-SNW',
            'ALX-PLS-MST',
            'ALX-PLS-ONX',

            // Trek: Rover (4), Summit (2)
            'ALX-RVR-IVY',
            'ALX-RVR-MID',
            'ALX-RVR-ONX',
            'ALX-RVR-STL',
            'ALX-SMT-BLS',
            'ALX-SMT-OBS',
        ];

        foreach ($expectedSkus as $sku) {
            $this->assertDatabaseHas('product_variants', ['sku' => $sku]);
        }
    }

    /**
     * 9. Variant prices match the expected confirmed prices.
     */
    public function test_variant_prices_match_expected_collection_prices(): void
    {
        $formaVariants = Product::where('slug', 'alexa-forma')->first()->variants;
        foreach ($formaVariants as $variant) {
            $this->assertEquals(899000, $variant->price);
        }

        $soraVariants = Product::where('slug', 'alexa-sora')->first()->variants;
        foreach ($soraVariants as $variant) {
            $this->assertEquals(849000, $variant->price);
        }

        $flowVariants = Product::where('slug', 'alexa-flow')->first()->variants;
        foreach ($flowVariants as $variant) {
            $this->assertEquals(1099000, $variant->price);
        }

        $pulseVariants = Product::where('slug', 'alexa-pulse')->first()->variants;
        foreach ($pulseVariants as $variant) {
            $this->assertEquals(1049000, $variant->price);
        }

        $roverVariants = Product::where('slug', 'alexa-rover')->first()->variants;
        foreach ($roverVariants as $variant) {
            $this->assertEquals(1299000, $variant->price);
        }

        $summitVariants = Product::where('slug', 'alexa-summit')->first()->variants;
        foreach ($summitVariants as $variant) {
            $this->assertEquals(1399000, $variant->price);
        }
    }

    /**
     * 10. Product image files exist in public/images/products/.
     */
    public function test_product_image_files_exist_in_public_directory(): void
    {
        $variants = ProductVariant::all();
        $this->assertCount(18, $variants);

        foreach ($variants as $variant) {
            $fullPath = public_path($variant->image);
            $this->assertFileExists($fullPath, "Image file not found: {$fullPath}");
        }
    }

    /**
     * 11. Homepage returns HTTP 200.
     */
    public function test_homepage_returns_http_200(): void
    {
        $response = $this->get(route('home'));
        $response->assertStatus(200);
        $response->assertSee('Alexa');
        $response->assertSee('Designed to go wherever the day takes you.');
        $response->assertSee('Alexa Core');
        $response->assertSee('Alexa Active');
        $response->assertSee('Alexa Trek');
        $response->assertSee('Alexa Forma');
        $response->assertSee('Alexa Flow');
        $response->assertSee('Alexa Rover');
    }

    /**
     * 12. Product listing returns HTTP 200.
     */
    public function test_product_listing_returns_http_200(): void
    {
        $response = $this->get(route('products.index'));
        $response->assertStatus(200);
        $response->assertSee('All Products');
        $response->assertSee('Alexa Forma');
        $response->assertSee('Alexa Sora');
        $response->assertSee('Alexa Flow');
        $response->assertSee('Alexa Pulse');
        $response->assertSee('Alexa Rover');
        $response->assertSee('Alexa Summit');
        $response->assertSee('In Stock');
    }

    /**
     * 13. Collection filtering works.
     */
    public function test_collection_filtering_works(): void
    {
        $response = $this->get(route('products.index', ['collection' => 'alexa-core']));
        $response->assertStatus(200);
        $response->assertSee('Alexa Forma');
        $response->assertSee('Alexa Sora');
        $response->assertDontSee('Alexa Flow &bull;', false);
        $response->assertDontSee('Alexa Rover &bull;', false);

        // Invalid collection query should return 404
        $invalidResponse = $this->get(route('products.index', ['collection' => 'non-existent-collection']));
        $invalidResponse->assertStatus(404);
    }

    /**
     * 14. Product detail page returns HTTP 200.
     */
    public function test_product_detail_page_returns_http_200(): void
    {
        $product = Product::where('slug', 'alexa-forma')->first();
        $response = $this->get(route('products.show', $product));

        $response->assertStatus(200);
        $response->assertSee($product->name);
        $response->assertSee('500 ml');
        $response->assertSee('18/8 stainless steel');
        $response->assertSee('Screw cap');
        $response->assertSee('±280g');
    }

    /**
     * 15. Product detail includes its variants.
     */
    public function test_product_detail_includes_its_variants(): void
    {
        $product = Product::where('slug', 'alexa-forma')->first();
        $response = $this->get(route('products.show', $product));

        $response->assertStatus(200);
        $response->assertSee('ALX-FRM-MID');
        $response->assertSee('ALX-FRM-SAG');
        $response->assertSee('ALX-FRM-IVY');
        $response->assertSee('ALX-FRM-LAV');
        $response->assertSee('Midnight');
        $response->assertSee('Sage');
        $response->assertSee('Ivory');
        $response->assertSee('Lavender');
    }

    /**
     * 16. Out-of-stock variants are correctly represented.
     */
    public function test_out_of_stock_variants_are_correctly_represented(): void
    {
        $product = Product::where('slug', 'alexa-forma')->first();
        // Set all variants to stock 0
        $product->variants()->update(['stock' => 0]);

        $response = $this->get(route('products.show', $product));
        $response->assertStatus(200);
        $response->assertSee('Out of Stock');
        $response->assertDontSee('20 available');
    }

    /**
     * 17. Inactive products are not publicly accessible.
     */
    public function test_inactive_products_are_not_publicly_accessible(): void
    {
        $product = Product::where('slug', 'alexa-forma')->first();
        $product->update(['status' => 'inactive']);

        // Direct show route should return 404
        $response = $this->get(route('products.show', $product));
        $response->assertStatus(404);

        // Index page should not include inactive product
        $indexResponse = $this->get(route('products.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertDontSee('id="product-card-alexa-forma"', false);
    }

    /**
     * 18. Inactive variants are not displayed publicly.
     */
    public function test_inactive_variants_are_not_displayed_publicly(): void
    {
        $product = Product::where('slug', 'alexa-forma')->first();
        $variant = $product->variants()->where('sku', 'ALX-FRM-MID')->first();
        $variant->update(['status' => 'inactive']);

        $response = $this->get(route('products.show', $product));
        $response->assertStatus(200);
        $response->assertDontSee('ALX-FRM-MID');
        $response->assertDontSee('id="variant-swatch-ALX-FRM-MID"', false);
    }
}
