<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard — Alexa')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">

    <style>
        :root {
            --font-sans: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            --font-serif: 'Playfair Display', Georgia, serif;
            --color-bg: #FBFBFA;
            --color-surface: #FFFFFF;
            --color-surface-subtle: #F4F4F0;
            --color-text-main: #141414;
            --color-text-muted: #666663;
            --color-text-subtle: #999996;
            --color-border: #E8E8E4;
            --color-border-dark: #141414;
            --sidebar-width: 250px;
        }

        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font-sans);
            background-color: var(--color-bg);
            color: var(--color-text-main);
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        .serif {
            font-family: var(--font-serif);
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        /* Layout structure */
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .admin-sidebar {
            width: var(--sidebar-width);
            background: #FFFFFF;
            border-right: 1px solid var(--color-border);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 40;
        }

        .sidebar-brand {
            padding: 1.75rem 1.5rem;
            border-bottom: 1px solid var(--color-border);
            display: flex;
            align-items: baseline;
            gap: 0.5rem;
        }

        .sidebar-brand-name {
            font-family: var(--font-serif);
            font-size: 1.375rem;
            font-weight: 500;
            letter-spacing: -0.02em;
        }

        .sidebar-brand-tag {
            font-size: 0.6875rem;
            text-transform: uppercase;
            letter-spacing: 0.14em;
            color: var(--color-text-subtle);
            font-weight: 600;
        }

        .sidebar-nav {
            padding: 1.5rem 0.75rem;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 0.375rem;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.625rem 0.875rem;
            border-radius: 3px;
            font-size: 0.8125rem;
            font-weight: 500;
            color: var(--color-text-muted);
            transition: all 0.15s ease;
        }

        .nav-item:hover {
            color: var(--color-text-main);
            background: var(--color-surface-subtle);
        }

        .nav-item.active {
            color: #FFFFFF;
            background: var(--color-text-main);
        }

        .nav-item svg {
            width: 17px;
            height: 17px;
            stroke-width: 1.8;
        }

        .sidebar-footer {
            padding: 1.25rem 1.5rem;
            border-top: 1px solid var(--color-border);
            background: #FAFAF8;
        }

        /* Top Header */
        .admin-main {
            margin-left: var(--sidebar-width);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .admin-header {
            height: 64px;
            background: #FFFFFF;
            border-bottom: 1px solid var(--color-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            position: sticky;
            top: 0;
            z-index: 30;
        }

        .admin-header-title {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--color-text-muted);
        }

        .admin-header-actions {
            display: flex;
            align-items: center;
            gap: 1.25rem;
        }

        .link-storefront {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--color-text-muted);
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
        }

        .link-storefront:hover {
            color: var(--color-text-main);
        }

        .admin-content {
            padding: 2.25rem 2rem 4rem;
            flex: 1;
            max-width: 1360px;
            width: 100%;
            margin: 0 auto;
        }

        /* Editorial components */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .page-title {
            font-family: var(--font-serif);
            font-size: 1.875rem;
            font-weight: 400;
            letter-spacing: -0.01em;
            margin-bottom: 0.25rem;
        }

        .page-subtitle {
            font-size: 0.875rem;
            color: var(--color-text-muted);
        }

        .card {
            background: #FFFFFF;
            border: 1px solid var(--color-border);
            padding: 1.75rem;
            margin-bottom: 1.75rem;
        }

        /* Buttons */
        .btn-editorial {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            font-weight: 600;
            border-radius: 2px;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 1px solid transparent;
            font-family: var(--font-sans);
        }

        .btn-editorial-primary {
            background-color: var(--color-text-main);
            color: #FFFFFF;
            border-color: var(--color-text-main);
        }

        .btn-editorial-primary:hover {
            background-color: #333333;
        }

        .btn-editorial-outline {
            background-color: transparent;
            color: var(--color-text-main);
            border-color: var(--color-border);
        }

        .btn-editorial-outline:hover {
            background-color: var(--color-surface-subtle);
            border-color: var(--color-text-muted);
        }

        .btn-editorial-danger {
            background-color: #FEF2F2;
            color: #991B1B;
            border-color: #FECACA;
        }

        .btn-editorial-danger:hover {
            background-color: #FEE2E2;
        }

        /* Form elements */
        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--color-text-muted);
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 0.625rem 0.875rem;
            background: #FFFFFF;
            border: 1px solid var(--color-border);
            font-family: var(--font-sans);
            font-size: 0.875rem;
            color: var(--color-text-main);
            border-radius: 2px;
            transition: border-color 0.15s ease;
        }

        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: var(--color-text-main);
        }

        /* Table design */
        .admin-table-wrap {
            width: 100%;
            overflow-x: auto;
            border: 1px solid var(--color-border);
            background: #FFFFFF;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8125rem;
            text-align: left;
        }

        .admin-table th {
            padding: 0.875rem 1.25rem;
            background: #FAFAF8;
            font-size: 0.6875rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--color-text-muted);
            font-weight: 600;
            border-bottom: 1px solid var(--color-border);
        }

        .admin-table td {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--color-border);
            vertical-align: middle;
        }

        .admin-table tr:last-child td {
            border-bottom: none;
        }

        .admin-table tr:hover td {
            background: #FBFBFA;
        }

        /* Badges */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.625rem;
            font-size: 0.6875rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 600;
            border-radius: 2px;
        }

        .status-badge-pending {
            background-color: #FFF8E6;
            color: #8F6B00;
            border: 1px solid #FFE6A3;
        }

        .status-badge-paid, .status-badge-processing, .status-badge-delivered, .status-badge-active {
            background-color: #F0FDF4;
            color: #15803D;
            border: 1px solid #BBF7D0;
        }

        .status-badge-shipped, .status-badge-in_transit, .status-badge-packed {
            background-color: #EFF6FF;
            color: #1E40AF;
            border: 1px solid #BFDBFE;
        }

        .status-badge-failed, .status-badge-cancelled, .status-badge-inactive {
            background-color: #FEF2F2;
            color: #991B1B;
            border: 1px solid #FECACA;
        }

        .status-badge-expired {
            background-color: #F3F4F6;
            color: #4B5563;
            border: 1px solid #E5E7EB;
        }

        /* Alerts */
        .alert-banner {
            padding: 0.875rem 1.25rem;
            font-size: 0.8125rem;
            line-height: 1.5;
            margin-bottom: 1.75rem;
            border: 1px solid;
            border-radius: 2px;
        }

        .alert-banner-success {
            background-color: #F0FDF4;
            border-color: #BBF7D0;
            color: #15803D;
        }

        .alert-banner-error {
            background-color: #FEF2F2;
            border-color: #FECACA;
            color: #991B1B;
        }

        .alert-banner-info {
            background-color: #EFF6FF;
            border-color: #BFDBFE;
            color: #1E40AF;
        }

        @media (max-width: 900px) {
            .admin-sidebar {
                display: none;
            }
            .admin-main {
                margin-left: 0;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-brand">
                <a href="{{ route('admin.dashboard') }}" class="sidebar-brand-name">Alexa</a>
                <span class="sidebar-brand-tag">Admin</span>
            </div>

            <nav class="sidebar-nav">
                <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" id="nav-admin-dashboard">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="14" y="14" width="7" height="7"></rect>
                        <rect x="3" y="14" width="7" height="7"></rect>
                    </svg>
                    Dashboard
                </a>

                <a href="{{ route('admin.products.index') }}" class="nav-item {{ request()->routeIs('admin.products.*') ? 'active' : '' }}" id="nav-admin-products">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                    </svg>
                    Products &amp; Stock
                </a>

                <a href="{{ route('admin.orders.index') }}" class="nav-item {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" id="nav-admin-orders">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                    Orders &amp; Shipping
                </a>
            </nav>

            <div class="sidebar-footer">
                <div style="font-size: 0.75rem; font-weight: 600; color: var(--color-text-main); margin-bottom: 0.25rem;">
                    {{ auth()->user()->name }}
                </div>
                <div style="font-size: 0.6875rem; color: var(--color-text-subtle); margin-bottom: 0.75rem; word-break: break-all;">
                    {{ auth()->user()->email }}
                </div>
                <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" style="font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.1em; color: #991B1B; background: none; border: none; cursor: pointer; padding: 0;" id="btn-admin-logout">
                        &larr; Sign Out
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Wrapper -->
        <div class="admin-main">
            <!-- Header -->
            <header class="admin-header">
                <div class="admin-header-title">
                    Alexa E-Commerce Management Console
                </div>
                <div class="admin-header-actions">
                    <a href="{{ route('home') }}" class="link-storefront" target="_blank" rel="noopener">
                        View Storefront &nearr;
                    </a>
                </div>
            </header>

            <!-- Content Area -->
            <main class="admin-content">
                @if(session('success'))
                    <div class="alert-banner alert-banner-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert-banner alert-banner-error">
                        {{ session('error') }}
                    </div>
                @endif

                @if(session('info'))
                    <div class="alert-banner alert-banner-info">
                        {{ session('info') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
