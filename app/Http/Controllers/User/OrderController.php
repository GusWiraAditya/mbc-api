<?php

namespace App\Http\Controllers\User;

use App\Models\User\Order;
use Illuminate\Support\Str;
use App\Models\User\Address;
use Illuminate\Http\Request;
use App\Services\VoucherService;
use Illuminate\Support\Facades\DB;
use Midtrans\Snap as MidtransSnap;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Midtrans\Config as MidtransConfig;

class OrderController extends Controller
{
    // use AuthorizesRequests;
    protected $voucherService;

    public function __construct(VoucherService $voucherService)
    {
        $this->voucherService = $voucherService;

        // Konfigurasi Midtrans yang diperbaiki
        $this->configureMidtrans();
    }

    public function index()
    {
        try {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        // Test apakah relasi orders ada
        $orders = $user->orders()
                       ->select('id', 'order_number', 'grand_total', 'order_status', 'created_at') 
                       ->latest()
                       ->paginate(10);

        return response()->json($orders);
    } catch (\Exception $e) {
        \Log::error('Order index error: ' . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
    }
    /**
     * Konfigurasi Midtrans dengan validasi
     */
    private function configureMidtrans()
    {
        try {
            $serverKey = config('services.midtrans.server_key');
            $isProduction = config('services.midtrans.is_production');
            $is3ds = config('services.midtrans.is_3ds');

            // Debug: Log konfigurasi untuk memastikan terbaca
            Log::info('Midtrans Config:', [
                'server_key_exists' => !empty($serverKey),
                'is_production' => $isProduction,
                'is_3ds' => $is3ds
            ]);

            if (empty($serverKey)) {
                throw new \Exception('Midtrans server key tidak ditemukan');
            }

            MidtransConfig::$serverKey = $serverKey;
            MidtransConfig::$isProduction = $isProduction;
            MidtransConfig::$isSanitized = true;
            MidtransConfig::$is3ds = $is3ds;
        } catch (\Exception $e) {
            Log::error('Midtrans configuration failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Membuat pesanan baru, memproses keranjang, dan menghasilkan token pembayaran Midtrans.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'address_id' => 'required|exists:addresses,id',
            'shipping_courier' => 'required|string',
            'shipping_service' => 'required|string',
            'shipping_cost' => 'required|numeric|min:0',
            'shipping_etd' => 'required|string',
            'voucher_codes' => 'sometimes|array'

        ]);

        $user = Auth::user();
        $address = Address::findOrFail($validated['address_id']);

        if ($address->user_id !== $user->id) {
            return response()->json(['message' => 'Alamat tidak valid.'], 403);
        }

        $orderData = null;

        try {
            DB::transaction(function () use ($user, $address, $validated, &$orderData) {

                $cartItems = $user->carts()->where('selected', true)->with(['productVariant.product', 'productVariant.color', 'productVariant.size'])->get();
                if ($cartItems->isEmpty()) {
                    throw new \Exception('Keranjang Anda kosong atau tidak ada item yang dipilih.');
                }

                // Validasi data yang tangguh
                foreach ($cartItems as $item) {
                    $variant = $item->productVariant;

                    if (!$variant) {
                        $item->delete();
                        throw new \Exception("Beberapa item di keranjang Anda tidak lagi tersedia dan telah dihapus. Mohon periksa kembali keranjang Anda sebelum melanjutkan.");
                    }

                    if ($variant->stock < $item->quantity) {
                        throw new \Exception("Stok untuk produk {$variant->product->product_name} tidak mencukupi. Sisa stok: {$variant->stock}.");
                    }
                }
                // 1. Hitung subtotal murni dari data di server
                $subtotal = $cartItems->sum(function ($cartItem) {
                    return $cartItem->productVariant->price * $cartItem->quantity;
                });

                // 2. Verifikasi ongkos kirim di backend
                $finalShippingCost = (float) $validated['shipping_cost'];
                $hasFreeShipping = $user->appliedVouchers->contains('type', 'free_shipping');
                if ($hasFreeShipping) {
                    $finalShippingCost = 0; // Paksa ongkir menjadi 0, abaikan input dari frontend
                }
                // 3. Hitung ulang total diskon di backend menggunakan VoucherService
                $formattedCartItems = $cartItems->map(function ($cartItem) {
                    return [
                        'productId'    => $cartItem->productVariant->product->id,
                        'categoryId'   => $cartItem->productVariant->product->category_id,
                        'price'        => (float) $cartItem->productVariant->price,
                        'quantity'     => $cartItem->quantity,
                    ];
                });
                $totalDiscount = $this->voucherService->calculateTotalDiscount($user, $formattedCartItems);

                // 4. Hitung grand total final di server
                $grandTotal = ($subtotal - $totalDiscount) + $finalShippingCost;

                // Validasi grand total
                if ($grandTotal < 0) { // Grand total tidak boleh negatif
                    throw new \Exception('Total pesanan tidak valid.');
                }

                $order = Order::create([
                    'user_id' => $user->id,
                    'shipping_address' => $address->toArray(),
                    'order_number' => 'INV/' . date('Ymd') . '/' . strtoupper(Str::random(6)),
                    'subtotal' => $subtotal,
                    'shipping_cost' => $finalShippingCost,
                    'discount_amount' => $totalDiscount,
                    'grand_total' => max(0, $grandTotal),
                    'shipping_courier' => $validated['shipping_courier'],
                    'shipping_service' => $validated['shipping_service'],
                    'shipping_etd' => $validated['shipping_etd'],
                    'payment_status' => 'pending',
                    'order_status' => 'pending_payment',
                ]);

                foreach ($cartItems as $item) {
                    $order->items()->create([
                        'product_variant_id' => $item->product_variant_id,
                        'product_name' => $item->productVariant->product->product_name,
                        'variant_name' => "{$item->productVariant->color->name} / {$item->productVariant->size->name} / {$item->productVariant->material->name}",
                        'quantity' => $item->quantity,
                        'price' => $item->productVariant->price,
                        'weight' => $item->productVariant->weight,
                    ]);
                    $item->productVariant->decrement('stock', $item->quantity);
                }

                foreach ($user->appliedVouchers as $voucher) {
                    $voucher->increment('times_used');
                    DB::table('voucher_usages')->insert([
                        'user_id' => $user->id,
                        'voucher_id' => $voucher->id,
                        'order_id' => $order->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $user->carts()->where('selected', true)->delete();
                $user->appliedVouchers()->sync([]);

                $orderData = ['order' => $order, 'cartItems' => $cartItems];
            });

            if (!$orderData) {
                return response()->json(['message' => 'Gagal memproses pesanan di dalam transaksi.'], 500);
            }

            // Generate Midtrans Snap Token
            $snapToken = $this->generateMidtransSnapToken($orderData['order'], $orderData['cartItems'], $user, $address);

            $orderData['order']->update(['midtrans_snap_token' => $snapToken]);

            return response()->json([
                'snap_token' => $snapToken,
                'order_id' => $orderData['order']->id,
                'order_number' => $orderData['order']->order_number
            ]);
        } catch (\Exception $e) {
            Log::error('Order creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id
            ]);

            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function show(Order $order)
    {
        // PENTING: Validasi bahwa pengguna hanya bisa melihat pesanannya sendiri
        // $this->authorize('view', $order);

        // Muat relasi item-item di dalam pesanan agar ikut terkirim
        $order->load('items');

        return response()->json($order);
    }
    /**
     * Generate Midtrans Snap Token
     */
    private function generateMidtransSnapToken($order, $cartItems, $user, $address)
    {
        try {
            // Prepare item details
            $itemDetails = collect();

            foreach ($cartItems as $item) {
                $itemDetails->push([
                    'id'       => 'ITEM_' . $item->product_variant_id,
                    'price'    => (int) $item->productVariant->price,
                    'quantity' => (int) $item->quantity,
                    'name'     => $this->sanitizeItemName($item->productVariant->product->product_name).' ('.$item->productVariant->color->name.$item->productVariant->size->name.$item->productVariant->material->name.')',
                ]);
            }

            // Add shipping cost if exists
            if ($order->shipping_cost > 0) {
                $itemDetails->push([
                    'id' => 'SHIPPING_COST',
                    'price' => (int) $order->shipping_cost,
                    'quantity' => 1,
                    'name' => 'Biaya Pengiriman',
                ]);
            }

            // Add discount if exists
            if ($order->discount_amount > 0) {
                $itemDetails->push([
                    'id' => 'VOUCHER_DISCOUNT',
                    'price' => - ((int) $order->discount_amount),
                    'quantity' => 1,
                    'name' => 'Diskon Voucher',
                ]);
            }

            // Validate total calculation
            $calculatedTotal = $itemDetails->sum(function ($item) {
                return $item['price'] * $item['quantity'];
            });

            if ($calculatedTotal !== (int) $order->grand_total) {
                Log::warning('Total mismatch', [
                    'calculated' => $calculatedTotal,
                    'order_total' => (int) $order->grand_total,
                    'order_id' => $order->id
                ]);
            }

            $midtransPayload = [
                'transaction_details' => [
                    'order_id'     => $order->order_number,
                    'gross_amount' => (int) $order->grand_total,
                ],
                'item_details' => $itemDetails->values()->toArray(),
                'customer_details' => [
                    'first_name' => $this->sanitizeString($user->name),
                    'email'      => $user->email,
                    'phone'      => $user->phone_number,
                    'shipping_address' => [
                        'first_name'   => $this->sanitizeString($address->recipient_name),
                        'phone'        => $address->phone_number,
                        'address'      => $this->sanitizeString($address->address_detail),
                        'city'         => $this->sanitizeString($address->city_name),
                        'postal_code'  => $address->postal_code,
                        'country_code' => 'IDN',
                    ]
                ],
                'callbacks' => [
                    'finish' => url('/payment/finish'),
                    'unfinish' => url('/payment/unfinish'),
                    'error' => url('/payment/error'),
                ]
            ];

            // Log payload for debugging
            Log::info('Midtrans Payload', [
                'order_id' => $order->order_number,
                'gross_amount' => (int) $order->grand_total,
                'item_count' => count($itemDetails),
                'payload' => $midtransPayload
            ]);

            $snapToken = MidtransSnap::getSnapToken($midtransPayload);

            return $snapToken;
        } catch (\Exception $e) {
            Log::error('Midtrans Snap Token generation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'order_id' => $order->id,
                'payload' => $midtransPayload ?? 'Payload not generated'
            ]);

            throw new \Exception('Gagal membuat sesi pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Sanitize item name for Midtrans
     */
    private function sanitizeItemName($name)
    {
        return substr(preg_replace('/[^a-zA-Z0-9\s\-\_\.]/', '', $name), 0, 50);
    }

    /**
     * Sanitize string for Midtrans
     */
    private function sanitizeString($string)
    {
        return preg_replace('/[^a-zA-Z0-9\s\-\_\.]/', '', $string);
    }
}
