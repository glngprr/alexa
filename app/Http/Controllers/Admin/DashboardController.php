<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the Admin Dashboard overview.
     */
    public function index(Request $request): View
    {
        $totalProducts = Product::count();
        $totalOrders = Order::count();
        $totalCustomers = User::where('is_admin', false)->count();

        // Revenue strictly calculated from payments with status 'paid'
        $totalRevenue = (int) Payment::where('status', 'paid')->sum('amount');

        // Order status breakdown
        $statusCounts = [
            'pending' => Order::where('order_status', 'pending')->count(),
            'processing' => Order::where('order_status', 'processing')->count(),
            'shipped' => Order::where('order_status', 'shipped')->count(),
            'delivered' => Order::where('order_status', 'delivered')->count(),
            'cancelled' => Order::where('order_status', 'cancelled')->count(),
        ];

        // Recent orders with customer and payment relations
        $recentOrders = Order::with(['user', 'payment', 'shipment'])
            ->latest()
            ->take(8)
            ->get();

        return view('admin.dashboard', compact(
            'totalProducts',
            'totalOrders',
            'totalCustomers',
            'totalRevenue',
            'statusCounts',
            'recentOrders'
        ));
    }
}
