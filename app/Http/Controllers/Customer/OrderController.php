<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of orders placed by the authenticated customer.
     */
    public function index(Request $request): View
    {
        $orders = $request->user()
            ->orders()
            ->with([
                'orderItems.productVariant.product',
                'payment',
                'shipment',
            ])
            ->latest('id')
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }
}
