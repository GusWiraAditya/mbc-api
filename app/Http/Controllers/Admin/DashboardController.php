<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Admin\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request to fetch dashboard stats.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $totalProducts = Product::count();
        // $totalOrders = Order::count();
        // $totalSold = Order::where('status', 'delivered')->count();
        // $dailyIncome = Order::whereDate('created_at', Carbon::today())->sum('total_price');

        return response()->json([
            'success' => true,
            'data' => [
                'total_products' => $totalProducts,
                // 'total_orders' => $totalOrders,
                // 'total_sold' => $totalSold,
                // 'daily_income' => $dailyIncome,
            ],
            'message' => 'Dashboard data retrieved successfully.',
        ]);
    }
}
