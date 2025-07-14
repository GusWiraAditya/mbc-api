<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\User\Order;
use App\Models\Admin\Product;
use App\Models\Admin\Voucher;
use App\Models\Admin\Category;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request to fetch dashboard stats.
     *
     * @return JsonResponse
     */
    public function getStats()
    {
        // Menghitung total pesanan
        $dailyIncome = Order::whereDate('paid_at', Carbon::today())->sum('subtotal');
        $totalProducts = Product::count();
        // $totalSold = Order::where('status', 'delivered')->count();
        $totalOrders = Order::count();

        // Menghitung total voucher
        $totalVouchers = Voucher::count();
        $totalCategories = Category::count();
        // Menghitung user berdasarkan role
        // $totalUsers = User::getRoleNames('customer')->count();
        // $totalAdmins = User::getRoleNames('admin')->count();

        // Mengembalikan semua data dalam satu response JSON
        return response()->json([
            'data' => [
                'dailyincome' => $dailyIncome,
                'totalProducts' => $totalProducts,
                'totalOrders' => $totalOrders,
                'totalVouchers' => $totalVouchers,
                'totalCategories' => $totalCategories,
                // 'totalAdmins' => $totalAdmins,
            ]
        ]);
    }
}
