<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User\Order; // Pastikan path model Order benar
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Menampilkan daftar semua pesanan dengan filter, pencarian, dan pagination.
     */
    public function index(Request $request)
    {
        $query = Order::withoutGlobalScopes()->with('user:id,name')->latest(); // Urutkan dari yang terbaru

        // Logika untuk filter berdasarkan status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('order_status', $request->status);
        }

        // Logika untuk pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->paginate(10)->withQueryString();

        return response()->json($orders);
    }

    /**
     * Menampilkan detail satu pesanan spesifik untuk admin.
     */
    public function show(Order $order)
    {
        // Eager load semua relasi yang dibutuhkan untuk halaman detail admin
        $order->load([
            'user', 
            'items.productVariant.images', 
        ]);
        
        return response()->json($order);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:processing,shipped,completed,cancelled',
        ]);

        $order->order_status = $validated['status'];
        $order->save();

        // Kirim email notifikasi ke pelanggan (akan kita buat nanti)
        // event(new OrderStatusUpdated($order));

        return response()->json(['message' => 'Order status updated successfully.']);
    }
    
    /**
     * Menambahkan nomor resi pengiriman ke pesanan.
     */
    public function addTrackingNumber(Request $request, Order $order)
    {
        $validated = $request->validate([
            'shipping_tracking_number' => 'required|string|max:255',
        ]);

        // Simpan nomor resi
        $order->shipping_tracking_number = $validated['shipping_tracking_number'];
        
        // Otomatis ubah status menjadi "shipped" saat resi ditambahkan
        $order->order_status = 'shipped';
        
        $order->save();

        // Kirim email notifikasi pengiriman ke pelanggan (akan kita buat nanti)
        // event(new OrderShipped($order));

        return response()->json(['message' => 'Tracking number added and order marked as shipped.']);
    }
}