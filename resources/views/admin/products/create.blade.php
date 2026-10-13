@extends('layouts.admin')

@section('title', 'New Product — Alexa Admin')

@section('content')
<div class="page-header">
    <div>
        <a href="{{ route('admin.products.index') }}" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--color-text-muted); display: inline-block; margin-bottom: 0.5rem;">
            &larr; Back to Products
        </a>
        <h1 class="page-title">Add New Product</h1>
        <p class="page-subtitle">Configure bottle model specifications and define the primary launch colorway.</p>
    </div>
</div>

<form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; align-items: flex-start;">
        <!-- Left Column: Core Product Info -->
        <div>
            <!-- Basic Information Card -->
            <div class="card">
                <h3 class="serif" style="font-size: 1.125rem; font-weight: 500; margin-bottom: 1.25rem;">
                    Product Details
                </h3>

                <div class="form-group">
                    <label class="form-label" for="name">Product Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required class="form-input" placeholder="e.g. Alexa Horizon">
                    @error('name')
                        <span style="color: #991B1B; font-size: 0.75rem; display: block; margin-top: 0.25rem;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="slug">URL Slug (Optional)</label>
                    <input type="text" name="slug" id="slug" value="{{ old('slug') }}" class="form-input" placeholder="e.g. alexa-horizon (auto-generated if empty)">
                    @error('slug')
                        <span style="color: #991B1B; font-size: 0.75rem; display: block; margin-top: 0.25rem;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="description">Editorial Description *</label>
                    <textarea name="description" id="description" rows="5" required class="form-textarea" placeholder="Crafted with considered minimalism...">{{ old('description') }}</textarea>
                    @error('description')
                        <span style="color: #991B1B; font-size: 0.75rem; display: block; margin-top: 0.25rem;">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Technical Specifications Card -->
            <div class="card">
                <h3 class="serif" style="font-size: 1.125rem; font-weight: 500; margin-bottom: 1.25rem;">
                    Technical Specifications
                </h3>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="capacity">Capacity *</label>
                        <input type="text" name="capacity" id="capacity" value="{{ old('capacity', '500 ml') }}" required class="form-input" placeholder="e.g. 500 ml">
                        @error('capacity')
                            <span style="color: #991B1B; font-size: 0.75rem;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="weight">Weight *</label>
                        <input type="text" name="weight" id="weight" value="{{ old('weight', '±320g') }}" required class="form-input" placeholder="e.g. ±320g">
                        @error('weight')
                            <span style="color: #991B1B; font-size: 0.75rem;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="material">Material *</label>
                        <input type="text" name="material" id="material" value="{{ old('material', '18/8 stainless steel') }}" required class="form-input" placeholder="e.g. 18/8 stainless steel">
                        @error('material')
                            <span style="color: #991B1B; font-size: 0.75rem;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="lid_type">Lid Type *</label>
                        <input type="text" name="lid_type" id="lid_type" value="{{ old('lid_type', 'Insulated cap') }}" required class="form-input" placeholder="e.g. Insulated cap">
                        @error('lid_type')
                            <span style="color: #991B1B; font-size: 0.75rem;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Initial Variant Card -->
            <div class="card">
                <h3 class="serif" style="font-size: 1.125rem; font-weight: 500; margin-bottom: 0.5rem;">
                    Initial Color Variant
                </h3>
                <p style="font-size: 0.8125rem; color: var(--color-text-muted); margin-bottom: 1.25rem;">
                    Every product requires at least one colorway at launch. Additional variants can be configured after creation.
                </p>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="variant_name">Variant Title *</label>
                        <input type="text" name="variant_name" id="variant_name" value="{{ old('variant_name') }}" required class="form-input" placeholder="e.g. Horizon Obsidian">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="color_name">Colorway Name *</label>
                        <input type="text" name="color_name" id="color_name" value="{{ old('color_name') }}" required class="form-input" placeholder="e.g. Obsidian Black">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="color_hex">Hex Color Swatch</label>
                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                            <input type="color" id="color_picker" value="{{ old('color_hex', '#141414') }}" style="width: 40px; height: 38px; padding: 0; border: 1px solid var(--color-border); cursor: pointer;" onchange="document.getElementById('color_hex').value = this.value;">
                            <input type="text" name="color_hex" id="color_hex" value="{{ old('color_hex', '#141414') }}" class="form-input" placeholder="#141414" onchange="document.getElementById('color_picker').value = this.value;">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="sku">SKU Code *</label>
                        <input type="text" name="sku" id="sku" value="{{ old('sku') }}" required class="form-input" placeholder="e.g. ALX-HRZ-OBS" style="text-transform: uppercase;">
                        @error('sku')
                            <span style="color: #991B1B; font-size: 0.75rem;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="price">Price (IDR) *</label>
                        <input type="number" name="price" id="price" value="{{ old('price', 899000) }}" min="0" required class="form-input" placeholder="899000">
                        @error('price')
                            <span style="color: #991B1B; font-size: 0.75rem;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="stock">Initial Stock *</label>
                        <input type="number" name="stock" id="stock" value="{{ old('stock', 20) }}" min="0" required class="form-input" placeholder="20">
                        @error('stock')
                            <span style="color: #991B1B; font-size: 0.75rem;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Variant Image</label>
                    <input type="file" name="image_file" class="form-input" accept="image/*">
                    <span style="font-size: 0.75rem; color: var(--color-text-subtle); display: block; margin-top: 0.25rem;">
                        Or specify an asset path:
                    </span>
                    <input type="text" name="image" value="{{ old('image', 'images/products/core-midnight.png') }}" class="form-input" style="margin-top: 0.25rem;">
                </div>
            </div>
        </div>

        <!-- Right Column: Settings & Actions -->
        <div>
            <div class="card">
                <h3 class="serif" style="font-size: 1.125rem; font-weight: 500; margin-bottom: 1.25rem;">
                    Classification &amp; Publishing
                </h3>

                <div class="form-group">
                    <label class="form-label" for="collection_id">Collection *</label>
                    <select name="collection_id" id="collection_id" required class="form-select">
                        <option value="">Select Collection</option>
                        @foreach($collections as $col)
                            <option value="{{ $col->id }}" {{ old('collection_id') == $col->id ? 'selected' : '' }}>
                                {{ $col->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('collection_id')
                        <span style="color: #991B1B; font-size: 0.75rem;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="status">Catalog Status *</label>
                    <select name="status" id="status" required class="form-select">
                        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active (Visible in Store)</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive (Hidden)</option>
                    </select>
                </div>

                <div style="margin-top: 2rem;">
                    <button type="submit" class="btn-editorial btn-editorial-primary" style="width: 100%;" id="btn-save-product">
                        Create &amp; Publish Product &rarr;
                    </button>
                    <a href="{{ route('admin.products.index') }}" class="btn-editorial btn-editorial-outline" style="width: 100%; margin-top: 0.75rem; text-align: center;">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
