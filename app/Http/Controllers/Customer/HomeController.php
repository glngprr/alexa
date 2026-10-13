<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Product;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    /**
     * Display the customer-facing homepage.
     */
    public function index(): View
    {
        $collections = Collection::with(['products' => function ($query) {
            $query->where('status', 'active')
                ->with(['variants' => fn($q) => $q->where('status', 'active')]);
        }])->get();

        $products = Product::where('status', 'active')
            ->with([
                'collection',
                'variants' => fn($q) => $q->where('status', 'active'),
            ])
            ->get();

        return view('home', compact('collections', 'products'));
    }
}
