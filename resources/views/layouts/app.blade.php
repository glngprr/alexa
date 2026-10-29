<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Alexa — Refined Hydration')</title>
    <meta name="description" content="@yield('meta_description', 'Alexa creates premium, minimal insulated drinkware designed for considered daily routines.')">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">

    <!-- Styles -->
    <style>
        :root {
            --font-sans: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            --font-serif: 'Playfair Display', Georgia, Cambria, 'Times New Roman', Times, serif;
            --color-bg: #FFFFFF;
            --color-surface: #F8F8F6;
            --color-surface-subtle: #F2F2EF;
            --color-text-main: #141414;
            --color-text-muted: #666663;
            --color-text-subtle: #999996;
            --color-border: #E8E8E4;
            --color-border-dark: #141414;
            --color-accent: #8E8D88;
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
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .serif {
            font-family: var(--font-serif);
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        img {
            max-width: 100%;
            height: auto;
            display: block;
        }

        button {
            font-family: inherit;
            cursor: pointer;
            border: none;
            background: none;
        }

        .container-custom {
            width: 100%;
            max-width: 1360px;
            margin-left: auto;
            margin-right: auto;
            padding-left: 1.5rem;
            padding-right: 1.5rem;
        }

        @media (min-width: 768px) {
            .container-custom {
                padding-left: 2.5rem;
                padding-right: 2.5rem;
            }
        }

        @media (min-width: 1280px) {
            .container-custom {
                padding-left: 3.5rem;
                padding-right: 3.5rem;
            }
        }

        /* Navigation */
        .site-nav {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--color-border);
            z-index: 50;
            transition: all 0.3s ease;
        }

        .nav-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 5rem;
        }

        .nav-brand {
            font-family: var(--font-serif);
            font-size: 1.625rem;
            letter-spacing: -0.02em;
            color: var(--color-text-main);
            font-weight: 500;
        }

        .nav-links {
            display: none;
            align-items: center;
            gap: 2.5rem;
        }

        @media (min-width: 768px) {
            .nav-links {
                display: flex;
            }
        }

        .nav-link {
            font-size: 0.8125rem;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--color-text-muted);
            transition: color 0.2s ease;
            position: relative;
            padding: 0.5rem 0;
        }

        .nav-link:hover, .nav-link.active {
            color: var(--color-text-main);
        }

        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 1.5px;
            background: var(--color-text-main);
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .nav-icon-btn,
        .nav-cart-btn {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            color: var(--color-text-main);
            background: transparent;
            border: none;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .nav-icon-btn:hover,
        .nav-cart-btn:hover {
            background-color: var(--color-surface);
            color: #000000;
        }

        /* Profile Dropdown */
        .nav-profile-dropdown-wrap {
            position: relative;
            display: inline-flex;
            align-items: center;
        }

        .profile-dropdown-menu {
            position: absolute;
            top: calc(100% + 0.5rem);
            right: 0;
            min-width: 210px;
            background: #FFFFFF;
            border: 1px solid var(--color-border);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.07);
            padding: 0.75rem 0;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-6px);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            z-index: 60;
        }

        .profile-dropdown-menu.open {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .profile-dropdown-header {
            padding: 0.5rem 1.25rem 0.625rem;
            display: flex;
            flex-direction: column;
            gap: 0.125rem;
        }

        .profile-name {
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--color-text-main);
        }

        .profile-email {
            font-size: 0.75rem;
            color: var(--color-text-muted);
            word-break: break-all;
        }

        .profile-dropdown-divider {
            height: 1px;
            background: var(--color-border);
            margin: 0.375rem 0;
        }

        .profile-dropdown-item {
            width: 100%;
            display: flex;
            align-items: center;
            padding: 0.625rem 1.25rem;
            font-size: 0.8125rem;
            color: var(--color-text-main);
            background: none;
            border: none;
            text-align: left;
            cursor: pointer;
            transition: background-color 0.15s ease, color 0.15s ease;
            font-family: var(--font-sans);
        }

        .profile-dropdown-item:hover {
            background-color: var(--color-surface);
            color: #842029;
        }

        .cart-badge {
            position: absolute;
            top: 0.15rem;
            right: 0.15rem;
            min-width: 1.125rem;
            height: 1.125rem;
            padding: 0 0.25rem;
            font-size: 0.625rem;
            font-weight: 600;
            line-height: 1.125rem;
            text-align: center;
            color: #FFFFFF;
            background-color: var(--color-text-main);
            border-radius: 9999px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s ease;
        }

        .nav-cart-btn:hover .cart-badge {
            transform: scale(1.1);
        }

        .nav-mobile-toggle {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            color: var(--color-text-main);
            transition: background-color 0.2s ease;
        }

        .nav-mobile-toggle:hover {
            background-color: var(--color-surface);
        }

        @media (min-width: 768px) {
            .nav-mobile-toggle {
                display: none;
            }
        }

        /* Mobile Drawer */
        .mobile-drawer {
            position: fixed;
            top: 5rem;
            left: 0;
            width: 100%;
            background: var(--color-bg);
            border-bottom: 1px solid var(--color-border);
            padding: 1.5rem 2rem 2.5rem;
            transform: translateY(-120%);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            z-index: 49;
        }

        .mobile-drawer.open {
            transform: translateY(0);
            opacity: 1;
            visibility: visible;
        }

        .mobile-nav-links {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .mobile-nav-link {
            font-size: 0.9375rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--color-text-main);
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--color-surface-subtle);
        }

        /* Buttons & Utilities */
        .btn-editorial {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            padding: 1rem 2.25rem;
            font-size: 0.8125rem;
            text-transform: uppercase;
            letter-spacing: 0.14em;
            font-weight: 500;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            cursor: pointer;
        }

        .btn-editorial-primary {
            background-color: var(--color-text-main);
            color: #FFFFFF;
            border: 1px solid var(--color-text-main);
        }

        .btn-editorial-primary:hover {
            background-color: #333333;
            border-color: #333333;
        }

        .btn-editorial-outline {
            background-color: transparent;
            color: var(--color-text-main);
            border: 1px solid var(--color-text-main);
        }

        .btn-editorial-outline:hover {
            background-color: var(--color-text-main);
            color: #FFFFFF;
        }

        .eyebrow {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.18em;
            color: var(--color-text-muted);
            margin-bottom: 0.75rem;
            display: block;
        }

        /* Flash Alerts */
        .alert-toast {
            padding: 1rem 1.5rem;
            font-size: 0.875rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-radius: 2px;
        }

        .alert-toast.alert-success {
            background-color: #F0F5F0;
            border: 1px solid #D5E5D3;
            color: #2D5A27;
        }

        .alert-toast.alert-error {
            background-color: #FDF2F2;
            border: 1px solid #F8D7DA;
            color: #842029;
        }

        .alert-toast.alert-info {
            background-color: #F0F4F8;
            border: 1px solid #D0DCE5;
            color: #1A365D;
        }

        /* Footer */
        .site-footer {
            background-color: #111111;
            color: #FFFFFF;
            padding-top: 5rem;
            padding-bottom: 3rem;
            margin-top: 6rem;
            border-top: 1px solid #222222;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 3.5rem;
            margin-bottom: 4rem;
        }

        @media (min-width: 768px) {
            .footer-grid {
                grid-template-columns: 1.5fr 1fr 1fr;
            }
        }

        .footer-heading {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.16em;
            color: #A0A09C;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .footer-links {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 0.875rem;
        }

        .footer-link {
            font-size: 0.875rem;
            color: #CCCCCC;
            transition: color 0.2s ease;
        }

        .footer-link:hover {
            color: #FFFFFF;
        }

        .footer-bottom {
            padding-top: 2rem;
            border-top: 1px solid #222222;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            font-size: 0.75rem;
            color: #777774;
        }

        @media (min-width: 768px) {
            .footer-bottom {
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
            }
        }        /* Scroll Anchor Margin for Sticky Header */
        html {
            scroll-padding-top: 5.5rem;
        }

        section[id] {
            scroll-margin-top: 5.5rem;
        }

        /* SVG Icons */
        .icon {
            display: inline-block;
            width: 1.25rem;
            height: 1.25rem;
            stroke-width: 1.5;
            stroke: currentColor;
            fill: none;
            stroke-linecap: round;
            stroke-linejoin: round;
            vertical-align: middle;
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Header / Navigation -->
    <header class="site-nav">
        <div class="container-custom nav-inner">
            <!-- Brand Logo -->
            <a href="{{ route('home') }}" class="nav-brand" id="nav-brand-logo">
                Alexa
            </a>

            <!-- Desktop Links -->
            <nav class="nav-links">
                <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" id="nav-all-products">
                    All Products
                </a>
                <a href="{{ route('home') }}#story" class="nav-link" id="nav-story">
                    Story
                </a>
            </nav>

            <!-- Right Actions: Profile, Cart & Mobile Menu -->
            <div class="nav-actions">
                @php
                    $cartItemCount = 0;
                    if (auth()->check() && auth()->user()->cart) {
                        $cartItemCount = auth()->user()->cart->itemsCount();
                    }
                @endphp

                <!-- Profile / User Account Dropdown -->
                @auth
                    <div class="nav-profile-dropdown-wrap" id="profile-dropdown-wrap">
                        <button type="button" class="nav-icon-btn" id="nav-profile-btn" aria-label="User Account" aria-expanded="false">
                            <svg class="icon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </button>
                        <div class="profile-dropdown-menu" id="profile-dropdown-menu">
                            <div class="profile-dropdown-header">
                                <span class="profile-name">{{ auth()->user()->name }}</span>
                                <span class="profile-email">{{ auth()->user()->email }}</span>
                            </div>
                            <div class="profile-dropdown-divider"></div>
                            <a href="{{ route('orders.index') }}" class="profile-dropdown-item" id="link-my-orders" style="color: #141414; font-weight: 500;">
                                <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 0.5rem; opacity: 0.7;">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <line x1="16" y1="13" x2="8" y2="13"></line>
                                    <line x1="16" y1="17" x2="8" y2="17"></line>
                                    <polyline points="10 9 9 9 8 9"></polyline>
                                </svg>
                                My Orders
                            </a>
                            <div class="profile-dropdown-divider"></div>
                            @if(auth()->user()->isAdmin())
                                <a href="{{ route('admin.dashboard') }}" class="profile-dropdown-item" id="link-admin-dashboard" style="color: #141414; font-weight: 500;">
                                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 0.5rem; opacity: 0.7;">
                                        <rect x="3" y="3" width="7" height="7"></rect>
                                        <rect x="14" y="3" width="7" height="7"></rect>
                                        <rect x="14" y="14" width="7" height="7"></rect>
                                        <rect x="3" y="14" width="7" height="7"></rect>
                                    </svg>
                                    Admin Dashboard
                                </a>
                                <div class="profile-dropdown-divider"></div>
                            @endif
                            <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                                @csrf
                                <button type="submit" class="profile-dropdown-item" id="btn-dropdown-logout">
                                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 0.5rem; opacity: 0.7;">
                                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                        <polyline points="16 17 21 12 16 7"></polyline>
                                        <line x1="21" y1="12" x2="9" y2="12"></line>
                                    </svg>
                                    Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="nav-icon-btn" id="nav-login-btn" aria-label="Sign In">
                        <svg class="icon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </a>
                @endauth

                <!-- Shopping Cart Button -->
                <a href="{{ route('cart.index') }}" class="nav-cart-btn" id="nav-cart-btn" aria-label="Shopping Cart">
                    <svg class="icon cart-icon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6l-3-4z"></path>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                    </svg>
                    <span class="cart-badge" id="cart-badge-count">{{ $cartItemCount }}</span>
                </a>

                <!-- Mobile Menu Toggle Button -->
                <button class="nav-mobile-toggle" id="mobile-menu-btn" aria-label="Toggle Menu">
                    <svg class="icon" viewBox="0 0 24 24" id="hamburger-icon">
                        <line x1="4" y1="7" x2="20" y2="7"></line>
                        <line x1="4" y1="12" x2="20" y2="12"></line>
                        <line x1="4" y1="17" x2="20" y2="17"></line>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Drawer -->
        <div class="mobile-drawer" id="mobile-drawer">
            <div class="mobile-nav-links">
                <a href="{{ route('products.index') }}" class="mobile-nav-link" id="mobile-nav-all">All Products</a>
                <a href="{{ route('cart.index') }}" class="mobile-nav-link" id="mobile-nav-cart">Shopping Cart ({{ $cartItemCount }})</a>
                <a href="{{ route('home') }}#story" class="mobile-nav-link" id="mobile-nav-story">Story</a>
                @auth
                    <a href="{{ route('orders.index') }}" class="mobile-nav-link" id="mobile-nav-orders">My Orders</a>
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="mobile-nav-link" id="mobile-nav-admin">Admin Dashboard</a>
                    @endif
                    <form action="{{ route('logout') }}" method="POST" style="margin-top: 0.5rem;">
                        @csrf
                        <button type="submit" class="mobile-nav-link" style="width: 100%; text-align: left; background: none; border: none; cursor: pointer;">
                            Sign Out ({{ auth()->user()->name }})
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="mobile-nav-link" id="mobile-nav-login">Sign In</a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main style="padding-top: 5rem;">
        @if(session('success') || session('error') || session('info'))
            <div class="container-custom" style="padding-top: 1.5rem;">
                @if(session('success'))
                    <div class="alert-toast alert-success" id="flash-success">
                        <span>{{ session('success') }}</span>
                        <button type="button" onclick="this.parentElement.remove()" style="font-size: 1.25rem; line-height: 1; color: inherit;">&times;</button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert-toast alert-error" id="flash-error">
                        <span>{{ session('error') }}</span>
                        <button type="button" onclick="this.parentElement.remove()" style="font-size: 1.25rem; line-height: 1; color: inherit;">&times;</button>
                    </div>
                @endif

                @if(session('info'))
                    <div class="alert-toast alert-info" id="flash-info">
                        <span>{{ session('info') }}</span>
                        <button type="button" onclick="this.parentElement.remove()" style="font-size: 1.25rem; line-height: 1; color: inherit;">&times;</button>
                    </div>
                @endif
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="container-custom">
            <div class="footer-grid">
                <!-- Brand Overview -->
                <div>
                    <span class="serif" style="font-size: 1.75rem; font-weight: 500; display: block; margin-bottom: 1rem; letter-spacing: -0.01em;">
                        Alexa
                    </span>
                    <p style="color: #999996; font-size: 0.875rem; line-height: 1.7; max-width: 340px;">
                        Everyday essentials defined by simplicity, refined proportions, and understated character. Crafted for considered hydration routines.
                    </p>
                </div>

                <!-- Collections -->
                <div>
                    <h4 class="footer-heading">Collections</h4>
                    <ul class="footer-links">
                        <li><a href="{{ route('products.index', ['collection' => 'alexa-core']) }}" class="footer-link">Alexa Core (500 ml)</a></li>
                        <li><a href="{{ route('products.index', ['collection' => 'alexa-active']) }}" class="footer-link">Alexa Active (600 ml)</a></li>
                        <li><a href="{{ route('products.index', ['collection' => 'alexa-trek']) }}" class="footer-link">Alexa Trek (750 ml)</a></li>
                    </ul>
                </div>

                <!-- Navigation -->
                <div>
                    <h4 class="footer-heading">Explore</h4>
                    <ul class="footer-links">
                        <li><a href="{{ route('products.index') }}" class="footer-link">All Products</a></li>
                        <li><a href="{{ route('cart.index') }}" class="footer-link">Shopping Cart</a></li>
                        <li><a href="{{ route('home') }}#story" class="footer-link">Design Philosophy</a></li>
                        <li><a href="{{ route('home') }}#specifications" class="footer-link">Materials & Performance</a></li>
                    </ul>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} Alexa. All rights reserved.</p>
                <p style="color: #666663;">Designed for considered daily routines.</p>
            </div>
        </div>
    </footer>

    <!-- Script for mobile menu toggle & profile dropdown -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const menuBtn = document.getElementById('mobile-menu-btn');
            const drawer = document.getElementById('mobile-drawer');
            const profileBtn = document.getElementById('nav-profile-btn');
            const profileDropdown = document.getElementById('profile-dropdown-menu');

            // Mobile menu toggle
            if (menuBtn && drawer) {
                menuBtn.addEventListener('click', function () {
                    drawer.classList.toggle('open');
                    if (profileDropdown) profileDropdown.classList.remove('open');
                });

                document.addEventListener('click', function (event) {
                    if (!menuBtn.contains(event.target) && !drawer.contains(event.target)) {
                        drawer.classList.remove('open');
                    }
                });
            }

            // Profile dropdown toggle
            if (profileBtn && profileDropdown) {
                profileBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const isOpen = profileDropdown.classList.toggle('open');
                    profileBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                });

                document.addEventListener('click', function (event) {
                    if (!profileBtn.contains(event.target) && !profileDropdown.contains(event.target)) {
                        profileDropdown.classList.remove('open');
                        profileBtn.setAttribute('aria-expanded', 'false');
                    }
                });

                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape' && profileDropdown.classList.contains('open')) {
                        profileDropdown.classList.remove('open');
                        profileBtn.setAttribute('aria-expanded', 'false');
                        profileBtn.focus();
                    }
                });
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
