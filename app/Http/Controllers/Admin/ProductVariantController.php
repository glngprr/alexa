<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductVariantController extends Controller
{
    /**
     * Store a newly created variant for a specific product.
     */
    public function store(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'color_name' => ['required', 'string', 'max:255'],
            'color_hex' => ['nullable', 'string', 'max:25'],
            'sku' => ['required', 'string', 'max:100', 'unique:product_variants,sku'],
            'price' => ['required', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
            'image' => ['nullable', 'string', 'max:255'],
            'image_file' => ['nullable', 'image', 'max:4096'],
        ]);

        $imagePath = $validated['image'] ?? 'images/products/core-midnight.png';
        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $fileName = time() . '_' . Str::slug($validated['sku']) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/products'), $fileName);
            $imagePath = 'images/products/' . $fileName;
        }

        $product->variants()->create([
            'name' => $validated['name'],
            'color_name' => $validated['color_name'],
            'color_hex' => $validated['color_hex'] ?? '#141414',
            'sku' => strtoupper($validated['sku']),
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'status' => $validated['status'],
            'image' => $imagePath,
        ]);

        return redirect()->route('admin.products.edit', $product->slug)
            ->with('success', "Variant '{$validated['name']}' added successfully.");
    }

    /**
     * Update the specified variant in database.
     */
    public function update(Request $request, ProductVariant $variant): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'color_name' => ['required', 'string', 'max:255'],
            'color_hex' => ['nullable', 'string', 'max:25'],
            'sku' => ['required', 'string', 'max:100', 'unique:product_variants,sku,' . $variant->id],
            'price' => ['required', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
            'image' => ['nullable', 'string', 'max:255'],
            'image_file' => ['nullable', 'image', 'max:4096'],
        ]);

        $imagePath = $variant->image;
        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $fileName = time() . '_' . Str::slug($validated['sku']) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/products'), $fileName);
            $imagePath = 'images/products/' . $fileName;
        } elseif (!empty($validated['image'])) {
            $imagePath = $validated['image'];
        }

        $variant->update([
            'name' => $validated['name'],
            'color_name' => $validated['color_name'],
            'color_hex' => $validated['color_hex'] ?? $variant->color_hex,
            'sku' => strtoupper($validated['sku']),
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'status' => $validated['status'],
            'image' => $imagePath,
        ]);

        return redirect()->route('admin.products.edit', $variant->product->slug)
            ->with('success', "Variant '{$variant->sku}' updated successfully.");
    }

    /**
     * Remove the specified variant from database if it has no associated orders.
     */
    public function destroy(ProductVariant $variant): RedirectResponse
    {
        $product = $variant->product;

        $hasOrders = OrderItem::where('product_variant_id', $variant->id)->exists();
        if ($hasOrders) {
            return redirect()->route('admin.products.edit', $product->slug)
                ->with('error', "Cannot delete variant '{$variant->sku}' because it has associated customer orders. Deactivate it instead.");
        }

        // Ensure product has at least one variant remaining
        if ($product->variants()->count() <= 1) {
            return redirect()->route('admin.products.edit', $product->slug)
                ->with('error', "Cannot delete the only variant of a product. Products must have at least one variant.");
        }

        $variant->delete();

        return redirect()->route('admin.products.edit', $product->slug)
            ->with('success', "Variant '{$variant->sku}' deleted successfully.");
    }
}
