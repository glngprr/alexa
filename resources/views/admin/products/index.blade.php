@extends('layouts.admin')

@section('title', 'Product Catalog — Alexa Admin')

@section('content')
<div class="page-header">
    <div>
        <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.12em; color: var(--color-text-subtle); display: block; margin-bottom: 0.25rem;">Inventory</span>
        <h1 class="page-title">Products &amp; Variants</h1>
        <p class="page-subtitle">Manage drinking bottle models, technical specifications, and color variant stock.</p>
    </div>
    <div>
        <a href="{{ route('admin.products.create') }}" class="btn-editorial btn-editorial-primary" id="btn-create-product">
            + Add Product
        </a>
    </div>
</div>

<!-- Filters Bar -->
<div class="card" style="padding: 1.25rem 1.5rem; margin-bottom: 1.5rem;">
    <form action="{{ route('admin.products.index') }}" method="GET" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
        <div style="flex: 2; min-width: 200px;">
            <label class="form-label">Search</label>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Product name, description, SKU..." class="form-input" id="search-products">
        </div>

        <div style="flex: 1; min-width: 160px;">
            <label class="form-label">Collection</label>
            <select name="collection_id" class="form-select">
                <option value="">All Collections</option>
                @foreach($collections as $col)
                    <option value="{{ $col->id }}" {{ request('collection_id') == $col->id ? 'selected' : '' }}>
                        {{ $col->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div style="flex: 1; min-width: 140px;">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All Statuses</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn-editorial btn-editorial-primary" style="padding: 0.625rem 1rem;">
                Filter
            </button>
            @if(request()->anyFilled(['q', 'collection_id', 'status']))
                <a href="{{ route('admin.products.index') }}" class="btn-editorial btn-editorial-outline" style="padding: 0.625rem 1rem;">
                    Reset
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Products Table -->
<div class="card" style="padding: 0; overflow: hidden;">
    @if($products->count() > 0)
        <div class="admin-table-wrap" style="border: none;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 70px;">Image</th>
                        <th>Product &amp; Specs</th>
                        <th>Collection</th>
                        <th>Variants &amp; SKU</th>
                        <th>Total Stock</th>
                        <th>Status</th>
                        <th style="text-align: right; width: 160px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                        @php
                            $firstVariant = $product->variants->first();
                            $imgSrc = $firstVariant?->image ? asset($firstVariant->image) : asset('images/products/core-forma-midnight.png');
                            $totalStock = $product->variants->sum('stock');
                        @endphp
                        <tr>
                            <td>
                                <img src="{{ $imgSrc }}" alt="{{ $product->name }}" style="width: 48px; height: 48px; object-fit: cover; background: #F4F4F0; border: 1px solid var(--color-border); border-radius: 2px;">
                            </td>
                            <td>
                                <strong style="font-size: 0.9375rem; display: block; color: var(--color-text-main);">
                                    <a href="{{ route('admin.products.edit', $product->slug) }}">{{ $product->name }}</a>
                                </strong>
                                <span style="font-size: 0.75rem; color: var(--color-text-muted); display: block;">
                                    {{ $product->capacity }} &bull; {{ $product->material }} &bull; {{ $product->weight }}
                                </span>
                            </td>
                            <td>
                                <span style="font-size: 0.8125rem; font-weight: 500;">{{ $product->collection->name ?? '-' }}</span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.375rem; flex-wrap: wrap;">
                                    @foreach($product->variants as $variant)
                                        <span style="display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.6875rem; background: var(--color-surface-subtle); padding: 0.2rem 0.5rem; border-radius: 2px; border: 1px solid var(--color-border);">
                                            @if($variant->color_hex)
                                                <span style="width: 8px; height: 8px; border-radius: 50%; background: {{ $variant->color_hex }}; display: inline-block; border: 1px solid rgba(0,0,0,0.1);"></span>
                                            @endif
                                            <span>{{ $variant->color_name }} ({{ $variant->stock }})</span>
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                <strong style="font-size: 0.875rem; color: {{ $totalStock > 0 ? 'var(--color-text-main)' : '#991B1B' }};">
                                    {{ $totalStock }} units
                                </strong>
                            </td>
                            <td>
                                <span class="status-badge {{ $product->status === 'active' ? 'status-badge-active' : 'status-badge-inactive' }}">
                                    {{ ucfirst($product->status) }}
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 0.375rem; align-items: center;">
                                    <a href="{{ route('admin.products.edit', $product->slug) }}" class="btn-editorial btn-editorial-outline" style="padding: 0.375rem 0.625rem; font-size: 0.6875rem;">
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.products.destroy', $product->slug) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete product \'{{ addslashes($product->name) }}\'? This cannot be undone.');" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-editorial btn-editorial-danger" style="padding: 0.375rem 0.625rem; font-size: 0.6875rem;">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div style="padding: 1.25rem 1.5rem; border-top: 1px solid var(--color-border);">
                {{ $products->links() }}
            </div>
        @endif
    @else
        <div style="padding: 3.5rem; text-align: center; color: var(--color-text-muted);">
            <p style="font-size: 1rem; margin-bottom: 1rem;">No products match your current search or filter criteria.</p>
            <a href="{{ route('admin.products.create') }}" class="btn-editorial btn-editorial-primary">
                + Create New Product
            </a>
        </div>
    @endif
</div>
@endsection
