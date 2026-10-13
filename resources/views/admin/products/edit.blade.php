@extends('layouts.admin')

@section('title', 'Edit ' . $product->name . ' — Alexa Admin')

@section('content')
<div class="page-header">
    <div>
        <a href="{{ route('admin.products.index') }}" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--color-text-muted); display: inline-block; margin-bottom: 0.5rem;">
            &larr; Back to Products
        </a>
        <h1 class="page-title">{{ $product->name }}</h1>
        <p class="page-subtitle">Collection: <strong>{{ $product->collection->name ?? 'Unassigned' }}</strong> &bull; {{ $product->variants->count() }} Variants</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <a href="{{ route('products.show', $product->slug) }}" target="_blank" class="btn-editorial btn-editorial-outline">
            View on Storefront &nearr;
        </a>
    </div>
</div>

<!-- Product Details Edit Form -->
<form action="{{ route('admin.products.update', $product->slug) }}" method="POST">
    @csrf
    @method('PUT')

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; align-items: flex-start; margin-bottom: 2.5rem;">
        <div>
            <!-- Core Details -->
            <div class="card">
                <h3 class="serif" style="font-size: 1.125rem; font-weight: 500; margin-bottom: 1.25rem;">
                    Product Details
                </h3>

                <div class="form-group">
                    <label class="form-label" for="name">Product Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" required class="form-input">
                    @error('name')
                        <span style="color: #991B1B; font-size: 0.75rem;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="slug">URL Slug</label>
                    <input type="text" name="slug" id="slug" value="{{ old('slug', $product->slug) }}" required class="form-input">
                    @error('slug')
                        <span style="color: #991B1B; font-size: 0.75rem;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="description">Editorial Description *</label>
                    <textarea name="description" id="description" rows="5" required class="form-textarea">{{ old('description', $product->description) }}</textarea>
                    @error('description')
                        <span style="color: #991B1B; font-size: 0.75rem;">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Technical Specifications -->
            <div class="card">
                <h3 class="serif" style="font-size: 1.125rem; font-weight: 500; margin-bottom: 1.25rem;">
                    Technical Specifications
                </h3>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="capacity">Capacity *</label>
                        <input type="text" name="capacity" id="capacity" value="{{ old('capacity', $product->capacity) }}" required class="form-input">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="weight">Weight *</label>
                        <input type="text" name="weight" id="weight" value="{{ old('weight', $product->weight) }}" required class="form-input">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="material">Material *</label>
                        <input type="text" name="material" id="material" value="{{ old('material', $product->material) }}" required class="form-input">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="lid_type">Lid Type *</label>
                        <input type="text" name="lid_type" id="lid_type" value="{{ old('lid_type', $product->lid_type) }}" required class="form-input">
                    </div>
                </div>
            </div>
        </div>

        <div>
            <!-- Classification & Save -->
            <div class="card">
                <h3 class="serif" style="font-size: 1.125rem; font-weight: 500; margin-bottom: 1.25rem;">
                    Classification &amp; Status
                </h3>

                <div class="form-group">
                    <label class="form-label" for="collection_id">Collection *</label>
                    <select name="collection_id" id="collection_id" required class="form-select">
                        @foreach($collections as $col)
                            <option value="{{ $col->id }}" {{ old('collection_id', $product->collection_id) == $col->id ? 'selected' : '' }}>
                                {{ $col->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="status">Status *</label>
                    <select name="status" id="status" required class="form-select">
                        <option value="active" {{ old('status', $product->status) === 'active' ? 'selected' : '' }}>Active (Visible)</option>
                        <option value="inactive" {{ old('status', $product->status) === 'inactive' ? 'selected' : '' }}>Inactive (Hidden)</option>
                    </select>
                </div>

                <div style="margin-top: 1.5rem;">
                    <button type="submit" class="btn-editorial btn-editorial-primary" style="width: 100%;" id="btn-update-product">
                        Update Product Specs
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<hr style="border: none; border-top: 1px solid var(--color-border); margin: 2.5rem 0;">

<!-- Product Variants Section -->
<div class="page-header" style="margin-bottom: 1.5rem;">
    <div>
        <h2 class="serif" style="font-size: 1.5rem; font-weight: 400; margin-bottom: 0.25rem;">
            Colorways &amp; Inventory Variants
        </h2>
        <p class="page-subtitle">Configure pricing, SKU codes, color hex swatches, and real-time inventory levels.</p>
    </div>
</div>

<div class="card" style="padding: 0; overflow: hidden; margin-bottom: 2rem;">
    <div class="admin-table-wrap" style="border: none;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width: 60px;">Image</th>
                    <th>Color &amp; Title</th>
                    <th>SKU</th>
                    <th>Price</th>
                    <th>Stock Level</th>
                    <th>Status</th>
                    <th style="text-align: right; width: 220px;">Save / Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($product->variants as $variant)
                    <tr>
                        <form action="{{ route('admin.variants.update', $variant->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <td>
                                <img src="{{ asset($variant->image) }}" alt="{{ $variant->name }}" style="width: 44px; height: 44px; object-fit: cover; background: #F4F4F0; border: 1px solid var(--color-border); border-radius: 2px;">
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.375rem;">
                                    @if($variant->color_hex)
                                        <span style="width: 12px; height: 12px; border-radius: 50%; background: {{ $variant->color_hex }}; display: inline-block; border: 1px solid rgba(0,0,0,0.15);"></span>
                                    @endif
                                    <input type="text" name="name" value="{{ $variant->name }}" class="form-input" style="padding: 0.25rem 0.5rem; font-size: 0.75rem; width: 140px;" required>
                                </div>
                                <div style="display: flex; gap: 0.375rem;">
                                    <input type="text" name="color_name" value="{{ $variant->color_name }}" class="form-input" style="padding: 0.25rem 0.5rem; font-size: 0.75rem; width: 100px;" placeholder="Color Name" required>
                                    <input type="text" name="color_hex" value="{{ $variant->color_hex }}" class="form-input" style="padding: 0.25rem 0.5rem; font-size: 0.75rem; width: 75px;" placeholder="#Hex">
                                </div>
                            </td>
                            <td>
                                <input type="text" name="sku" value="{{ $variant->sku }}" class="form-input" style="padding: 0.25rem 0.5rem; font-size: 0.75rem; width: 130px; font-family: monospace; text-transform: uppercase;" required>
                            </td>
                            <td>
                                <input type="number" name="price" value="{{ $variant->price }}" min="0" class="form-input" style="padding: 0.25rem 0.5rem; font-size: 0.75rem; width: 110px;" required>
                            </td>
                            <td>
                                <input type="number" name="stock" value="{{ $variant->stock }}" min="0" class="form-input" style="padding: 0.25rem 0.5rem; font-size: 0.75rem; width: 80px;" required>
                            </td>
                            <td>
                                <select name="status" class="form-select" style="padding: 0.25rem 0.5rem; font-size: 0.75rem; width: 90px;">
                                    <option value="active" {{ $variant->status === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ $variant->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 0.375rem; align-items: center;">
                                    <button type="submit" class="btn-editorial btn-editorial-outline" style="padding: 0.375rem 0.625rem; font-size: 0.6875rem;" title="Update variant">
                                        Save
                                    </button>
                        </form>
                                    @if($product->variants->count() > 1)
                                        <form action="{{ route('admin.variants.destroy', $variant->id) }}" method="POST" onsubmit="return confirm('Delete variant {{ $variant->sku }}?');" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-editorial btn-editorial-danger" style="padding: 0.375rem 0.5rem; font-size: 0.6875rem;" title="Delete variant">
                                                &times;
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Add New Variant Form Card -->
<div class="card">
    <h3 class="serif" style="font-size: 1.125rem; font-weight: 500; margin-bottom: 1.25rem;">
        + Add Another Variant / Colorway
    </h3>

    <form action="{{ route('admin.products.variants.store', $product->slug) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
            <div>
                <label class="form-label">Variant Name *</label>
                <input type="text" name="name" placeholder="e.g. {{ $product->name }} Sand" required class="form-input">
            </div>

            <div>
                <label class="form-label">Color Name *</label>
                <input type="text" name="color_name" placeholder="e.g. Desert Sand" required class="form-input">
            </div>

            <div>
                <label class="form-label">Color Hex</label>
                <input type="text" name="color_hex" placeholder="#D4C5B9" class="form-input">
            </div>

            <div>
                <label class="form-label">SKU *</label>
                <input type="text" name="sku" placeholder="ALX-SND-01" required class="form-input" style="text-transform: uppercase;">
            </div>

            <div>
                <label class="form-label">Price (IDR) *</label>
                <input type="number" name="price" value="{{ $product->variants->first()?->price ?? 899000 }}" min="0" required class="form-input">
            </div>

            <div>
                <label class="form-label">Stock *</label>
                <input type="number" name="stock" value="20" min="0" required class="form-input">
            </div>

            <div>
                <label class="form-label">Status *</label>
                <select name="status" class="form-select">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>

        <div style="display: flex; gap: 1rem; align-items: center; justify-content: space-between; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 250px;">
                <label class="form-label">Variant Image</label>
                <input type="file" name="image_file" class="form-input" accept="image/*">
            </div>
            <div>
                <button type="submit" class="btn-editorial btn-editorial-primary" style="margin-top: 1.25rem;">
                    Add Variant &rarr;
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
