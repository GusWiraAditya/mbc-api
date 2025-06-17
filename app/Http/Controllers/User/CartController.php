<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    // Tampilkan semua item keranjang user yang login
    public function index()
    {
        $userId = Auth::id();

        $cartItems = Cart::with('product')
            ->where('customer_id', $userId)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $cartItems,
        ]);
    }

    // Tambah produk ke keranjang atau update quantity jika sudah ada
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:product,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $userId = Auth::id();

        $cartItem = Cart::where('customer_id', $userId)
            ->where('product_id', $request->product_id)
            ->first();

        if ($cartItem) {
            // Update quantity
            $cartItem->quantity += $request->quantity;
            $cartItem->save();
        } else {
            // Buat item baru
            $cartItem = Cart::create([
                'customer_id' => $userId,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil ditambahkan ke keranjang',
            'data' => $cartItem->load('product'),
        ], 201);
    }

    // Update quantity item di keranjang
    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $userId = Auth::id();

        $cartItem = Cart::where('id', $id)
            ->where('customer_id', $userId)
            ->first();

        if (!$cartItem) {
            return response()->json([
                'success' => false,
                'message' => 'Item keranjang tidak ditemukan',
            ], 404);
        }

        $cartItem->quantity = $request->quantity;
        $cartItem->save();

        return response()->json([
            'success' => true,
            'message' => 'Jumlah produk di keranjang berhasil diperbarui',
            'data' => $cartItem->load('product'),
        ]);
    }

    // Hapus item dari keranjang
    public function destroy($id)
    {
        $userId = Auth::id();

        $cartItem = Cart::where('id', $id)
            ->where('customer_id', $userId)
            ->first();

        if (!$cartItem) {
            return response()->json([
                'success' => false,
                'message' => 'Item keranjang tidak ditemukan',
            ], 404);
        }

        $cartItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil dihapus dari keranjang',
        ]);
    }
}
