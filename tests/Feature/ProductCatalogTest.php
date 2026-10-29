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
     * 2. Catalog seeding creates exactly 3 products.
     */
    public function test_catalog_seeding_creates_exactly_3_products(): void
    {
        $this->assertDatabaseCount('products', 3);
        $this->assertEquals(3, Product::count());
    }

    /**
     * 3. Catalog seeding creates exactly 12 variants.
     */
    public function test_catalog_seeding_creates_exactly_12_variants(): void
    {
        $this->assertDatabaseCount('product_variants', 12);
        $this->assertEquals(12, ProductVariant::count());
    }

    /**
     * 4. Each product has exactly 4 variants.
     */
    public function test_each_product_has_exactly_4_variants(): void
    {
        $products = Product::with('variants')->get();
        $this->assertCount(3, $products);

        foreach ($products as $product) {
            $this->assertCount(4, $product->variants, "Product {$product->name} does not have exactly 4 variants.");
        }
    }

    /**
     * 5. Product/collection relationships work.
     */
    public function test_product_collection_relationships_work(): void
    {
        $collection = Collection::where('slug', 'alexa-core')->first();
        $this->assertNotNull($collection);
        $this->assertTrue($collection->products()->exists());

        $product = Product::where('slug', 'alexa-core')->first();
        $this->assertNotNull($product);
        $this->assertInstanceOf(Collection::class, $product->collection);
        $this->assertEquals($collection->id, $product->collection->id);
    }

    /**
     * 6. Product/variant relationships work.
     */
    public function test_product_variant_relationships_work(): void
    {
        $product = Product::where('slug', 'alexa-core')->first();
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
        $this->assertCount(12, $skus);
        $this->assertCount(12, array_unique($skus));
    }

    /**
     * 8. Expected variant SKUs exist.
     */
    public function test_expected_variant_skus_exist(): void
    {
        $expectedSkus = [
            'ALX-CORE-MID',
            'ALX-CORE-SAG',
            'ALX-CORE-IVY',
            'ALX-CORE-LAV',
            'ALX-ACT-SNW',
            'ALX-ACT-ECL',
            'ALX-ACT-SKY',
            'ALX-ACT-BLS',
            'ALX-TRK-MID',
            'ALX-TRK-STL',
            'ALX-TRK-SND',
            'ALX-TRK-ONX',
        ];

        foreach ($expectedSkus as $sku) {
            $this->assertDatabaseHas('product_variants', ['sku' => $sku]);
        }
    }

    /**
     * 9. Variant prices match the expected collection prices.
     */
    public function test_variant_prices_match_expected_collection_prices(): void
    {
        $coreVariants = Product::where('slug', 'alexa-core')->first()->variants;
        foreach ($coreVariants as $variant) {
            $this->assertEquals(899000, $variant->price);
        }

        $activeVariants = Product::where('slug', 'alexa-active')->first()->variants;
        foreach ($activeVariants as $variant) {
            $this->assertEquals(1099000, $variant->price);
        }

        $trekVariants = Product::where('slug', 'alexa-trek')->first()->variants;
        foreach ($trekVariants as $variant) {
            $this->assertEquals(1299000, $variant->price);
        }
    }

    /**
     * 10. Product image files exist in public/images/products/.
     */
    public function test_product_image_files_exist_in_public_directory(): void
    {
        $variants = ProductVariant::all();
        $this->assertCount(12, $variants);

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
    }

    /**
     * 12. Product listing returns HTTP 200.
     */
    public function test_product_listing_returns_http_200(): void
    {
        $response = $this->get(route('products.index'));
        $response->assertStatus(200);
        $response->assertSee('All Products');
        $response->assertSee('Alexa Core');
        $response->assertSee('Alexa Active');
        $response->assertSee('Alexa Trek');
        $response->assertSee('In Stock');
    }

    /**
     * 13. Collection filtering works.
     */
    public function test_collection_filtering_works(): void
    {
        $response = $this->get(route('products.index', ['collection' => 'alexa-core']));
        $response->assertStatus(200);
        $response->assertSee('Alexa Core');
        $response->assertDontSee('Alexa Active &bull;', false);
        $response->assertDontSee('Alexa Trek &bull;', false);

        // Invalid collection query should return 404
        $invalidResponse = $this->get(route('products.index', ['collection' => 'non-existent-collection']));
        $invalidResponse->assertStatus(404);
    }

    /**
     * 14. Product detail page returns HTTP 200.
     */
    public function test_product_detail_page_returns_http_200(): void
    {
        $product = Product::where('slug', 'alexa-core')->first();
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
        $product = Product::where('slug', 'alexa-core')->first();
        $response = $this->get(route('products.show', $product));

        $response->assertStatus(200);
        $response->assertSee('ALX-CORE-MID');
        $response->assertSee('ALX-CORE-SAG');
        $response->assertSee('ALX-CORE-IVY');
        $response->assertSee('ALX-CORE-LAV');
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
        $product = Product::where('slug', 'alexa-core')->first();
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
        $product = Product::where('slug', 'alexa-core')->first();
        $product->update(['status' => 'inactive']);

        // Direct show route should return 404
        $response = $this->get(route('products.show', $product));
        $response->assertStatus(404);

        // Index page should not include inactive product
        $indexResponse = $this->get(route('products.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertDontSee('id="product-card-alexa-core"', false);
    }

    /**
     * 18. Inactive variants are not displayed publicly.
     */
    public function test_inactive_variants_are_not_displayed_publicly(): void
    {
        $product = Product::where('slug', 'alexa-core')->first();
        $variant = $product->variants()->where('sku', 'ALX-CORE-MID')->first();
        $variant->update(['status' => 'inactive']);

        $response = $this->get(route('products.show', $product));
        $response->assertStatus(200);
        $response->assertDontSee('ALX-CORE-MID');
        $response->assertDontSee('id="variant-swatch-ALX-CORE-MID"', false);
    }
}
