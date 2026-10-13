<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of active products with optional collection filter.
     */
    public function index(Request $request): View
    {
        $collections = Collection::orderBy('id')->get();
        $selectedCollection = null;

        $query = Product::where('status', 'active')
            ->with([
                'collection',
                'variants' => fn($q) => $q->where('status', 'active'),
            ]);

        if ($request->has('collection')) {
            $collectionSlug = $request->query('collection');
            if ($collectionSlug !== null && $collectionSlug !== '') {
                $selectedCollection = Collection::where('slug', $collectionSlug)->firstOrFail();
                $query->where('collection_id', $selectedCollection->id);
            }
        }

        $products = $query->orderBy('id')->get();

        return view('products.index', compact('products', 'collections', 'selectedCollection'));
    }

    /**
     * Display the specified active product.
     */
    public function show(Product $product): View
    {
        if ($product->status !== 'active') {
            abort(404);
        }

        $product->load([
            'collection',
            'variants' => fn($q) => $q->where('status', 'active')->orderBy('id'),
        ]);

        return view('products.show', compact('product'));
    }
}
