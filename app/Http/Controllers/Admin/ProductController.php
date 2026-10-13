<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display a listing of products with search and collection filters.
     */
    public function index(Request $request): View
    {
        $query = Product::with(['collection', 'variants'])->latest();

        if ($request->filled('q')) {
            $search = trim($request->input('q'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('variants', function ($vq) use ($search) {
                        $vq->where('sku', 'like', "%{$search}%")
                            ->orWhere('color_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('collection_id')) {
            $query->where('collection_id', $request->input('collection_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $products = $query->paginate(10)->withQueryString();
        $collections = Collection::orderBy('name')->get();

        return view('admin.products.index', compact('products', 'collections'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create(): View
    {
        $collections = Collection::orderBy('name')->get();
        return view('admin.products.create', compact('collections'));
    }

    /**
     * Store a newly created product along with its initial variant in database.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'collection_id' => ['required', 'exists:collections,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:products,slug'],
            'description' => ['required', 'string'],
            'capacity' => ['required', 'string', 'max:100'],
            'material' => ['required', 'string', 'max:255'],
            'lid_type' => ['required', 'string', 'max:255'],
            'weight' => ['required', 'string', 'max:100'],
            'status' => ['required', 'in:active,inactive'],

            // Initial variant attributes
            'variant_name' => ['required', 'string', 'max:255'],
            'color_name' => ['required', 'string', 'max:255'],
            'color_hex' => ['nullable', 'string', 'max:25'],
            'sku' => ['required', 'string', 'max:100', 'unique:product_variants,sku'],
            'price' => ['required', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'string', 'max:255'],
            'image_file' => ['nullable', 'image', 'max:4096'],
        ]);

        $slug = !empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : Str::slug($validated['name']);

        // Ensure unique slug
        $baseSlug = $slug;
        $counter = 1;
        while (Product::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-" . $counter++;
        }

        // Handle image
        $imagePath = 'images/products/core-midnight.png';
        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $fileName = time() . '_' . Str::slug($validated['sku']) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/products'), $fileName);
            $imagePath = 'images/products/' . $fileName;
        } elseif (!empty($validated['image'])) {
            $imagePath = $validated['image'];
        }

        DB::transaction(function () use ($validated, $slug, $imagePath) {
            $product = Product::create([
                'collection_id' => $validated['collection_id'],
                'name' => $validated['name'],
                'slug' => $slug,
                'description' => $validated['description'],
                'capacity' => $validated['capacity'],
                'material' => $validated['material'],
                'lid_type' => $validated['lid_type'],
                'weight' => $validated['weight'],
                'status' => $validated['status'],
            ]);

            $product->variants()->create([
                'name' => $validated['variant_name'],
                'color_name' => $validated['color_name'],
                'color_hex' => $validated['color_hex'] ?? '#141414',
                'sku' => strtoupper($validated['sku']),
                'price' => $validated['price'],
                'stock' => $validated['stock'],
                'status' => $validated['status'],
                'image' => $imagePath,
            ]);
        });

        return redirect()->route('admin.products.index')
            ->with('success', "Product '{$validated['name']}' created successfully.");
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product): View
    {
        $product->load(['collection', 'variants']);
        $collections = Collection::orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'collections'));
    }

    /**
     * Update the specified product in database.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'collection_id' => ['required', 'exists:collections,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:products,slug,' . $product->id],
            'description' => ['required', 'string'],
            'capacity' => ['required', 'string', 'max:100'],
            'material' => ['required', 'string', 'max:255'],
            'lid_type' => ['required', 'string', 'max:255'],
            'weight' => ['required', 'string', 'max:100'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $slug = !empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : $product->slug;

        $product->update([
            'collection_id' => $validated['collection_id'],
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'],
            'capacity' => $validated['capacity'],
            'material' => $validated['material'],
            'lid_type' => $validated['lid_type'],
            'weight' => $validated['weight'],
            'status' => $validated['status'],
        ]);

        return redirect()->route('admin.products.edit', $product->slug)
            ->with('success', "Product '{$product->name}' updated successfully.");
    }

    /**
     * Remove the specified product from database if it has no associated orders.
     */
    public function destroy(Product $product): RedirectResponse
    {
        // Check if any variant is linked in order items
        $variantIds = $product->variants()->pluck('id');
        $hasOrders = OrderItem::whereIn('product_variant_id', $variantIds)->exists();

        if ($hasOrders) {
            return redirect()->route('admin.products.index')
                ->with('error', "Cannot delete product '{$product->name}' because it has historical orders. You can set its status to Inactive instead.");
        }

        DB::transaction(function () use ($product) {
            $product->variants()->delete();
            $product->delete();
        });

        return redirect()->route('admin.products.index')
            ->with('success', "Product '{$product->name}' deleted successfully.");
    }
}
