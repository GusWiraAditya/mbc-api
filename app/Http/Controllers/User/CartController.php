<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use App\Models\User\Cart;
use Illuminate\Http\Request;
use App\Services\VoucherService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Admin\ProductVariant;
use Illuminate\Support\Facades\Auth;
use Exception;

class CartController extends Controller
{
    /**
     * Properti untuk menyimpan instance VoucherService.
     * @var VoucherService
     */
    protected $voucherService;

    /**
     * Constructor Controller.
     * Laravel akan secara otomatis membuat instance dari VoucherService
     * dan menyuntikkannya ke dalam controller ini (Dependency Injection).
     */
    public function __construct(VoucherService $voucherService)
    {
        $this->voucherService = $voucherService;
    }
    /**
     * "Golden Function"
     * Mengambil, menghitung, dan memformat seluruh state keranjang untuk pengguna.
     *
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    private function getCartResponseData(User $user)
    {
        $this->voucherService->revalidateAppliedVouchers($user);

        $user->refresh();
        // ... (kode untuk mengambil cartItems dan appliedVouchers) ...
        $cartItems = $user->carts()->with([
            'productVariant.product.category',
            'productVariant.color',
            'productVariant.size',
            'productVariant.material',
            'productVariant.images'
        ])->get();

        $appliedVouchers = $user->appliedVouchers;

        $formattedItems = $cartItems->map(function ($cartItem) {
            $variant = $cartItem->productVariant;
            $product = $variant->product;

            return [
                'cartItemId'    => $cartItem->id,
                'variantId'     => $variant->id,
                'productId'     => $product->id,
                'categoryId'    => $product->category_id, // <-- PERUBAHAN PENTING
                'productName'   => $product->product_name,
                'variantName'   => "{$variant->color->name} / {$variant->size->name} / {$variant->material->name}",
                'image'         => $variant->images->first()->path ?? null,
                'sku'           => $variant->sku,
                'price'         => (float) $variant->price,
                'stock'         => $variant->stock,
                'weight'        => $variant->weight,
                'quantity'      => $cartItem->quantity,
                'selected'      => (bool) $cartItem->selected,
            ];
        });
        
        // ... (sisa fungsi tidak berubah, ia akan menghitung summary dan mengembalikan JSON) ...
        $selectedItems = $formattedItems->where('selected', true);
        $subtotal = $selectedItems->sum(fn($item) => $item['price'] * $item['quantity']);
        $totalDiscount = $this->voucherService->calculateTotalDiscount($user, $selectedItems);
        $grandTotal = $subtotal - $totalDiscount;

        $data = [
            'items' => $formattedItems,
            'summary' => [
                'subtotal' => $subtotal,
                'totalDiscount' => $totalDiscount,
                'shippingCost' => 0,
                'grandTotal' => $grandTotal > 0 ? $grandTotal : 0,
            ],
            'applied_vouchers' => $appliedVouchers->map(function ($voucher) use ($user, $selectedItems) {
                 return [
                    'code' => $voucher->code,
                    'name' => $voucher->name,
                    'start_date' => $voucher->start_date ? $voucher->start_date->format('Y-m-d H:i:s') : null,
                    'end_date' => $voucher->end_date ? $voucher->end_date->format('Y-m-d H:i:s') : null,
                
                    'description' => $voucher->description,
                    'discountAmount' => $this->voucherService->calculateDiscountForVoucher($user, $voucher, $selectedItems),
                ];
            }),
        ];

        return response()->json($data);
    }
    /**
     * Mengambil state keranjang saat ini.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->getCartResponseData(Auth::user());
    }

        /**
     * Menambahkan item ke keranjang atau mengupdate kuantitas jika sudah ada.
     */
    public function add(Request $request)
    {
        $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $user = Auth::user();
        $variant = ProductVariant::findOrFail($request->variant_id);

        // Cek stok awal
        if ($variant->stock < 1) {
            return response()->json(['message' => 'Stok produk telah habis.'], 422);
        }

        // 1. Cari item atau buat instance baru di memori (tanpa menyimpan)
        $cartItem = Cart::firstOrNew([
            'user_id' => $user->id,
            'product_variant_id' => $variant->id,
        ]);

        // 2. Tentukan kuantitas baru
        // Jika item sudah ada di keranjang, tambahkan kuantitasnya.
        // Jika item baru, gunakan kuantitas dari request.
        $newQuantity = $cartItem->exists ? $cartItem->quantity + $request->quantity : $request->quantity;

        // 3. Set kuantitas, tapi jangan melebihi stok yang tersedia
        $cartItem->quantity = min($newQuantity, $variant->stock);
        $cartItem->selected = true; // Selalu tandai sebagai terpilih
        
        // 4. Simpan perubahan ke database
        $cartItem->save();

        // Kembalikan seluruh state keranjang yang sudah ter-update
        return $this->getCartResponseData($user);
    }

        /**
     * Menggabungkan item dari keranjang guest (local storage) ke keranjang pengguna di database.
     */
    public function merge(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $user = Auth::user();
        $guestItems = $request->items;

        // Gunakan transaksi database untuk memastikan semua operasi berhasil atau tidak sama sekali.
        DB::transaction(function () use ($user, $guestItems) {
            foreach ($guestItems as $guestItem) {
                $variant = ProductVariant::find($guestItem['variant_id']);
                
                // Lewati jika varian tidak ditemukan atau stok habis
                if (!$variant || $variant->stock <= 0) {
                    continue;
                }

                // Cari item yang sama di keranjang database
                $cartItem = Cart::firstOrNew([
                    'user_id' => $user->id,
                    'product_variant_id' => $variant->id,
                ]);

                // Jumlahkan kuantitas, tapi jangan melebihi stok
                $newQuantity = ($cartItem->quantity ?? 0) + $guestItem['quantity'];
                $cartItem->quantity = min($newQuantity, $variant->stock);
                $cartItem->selected = true; // Selalu tandai sebagai terpilih saat merge
                $cartItem->save();
            }
        });

        // Kembalikan state keranjang yang sudah final dan tergabung
        return $this->getCartResponseData($user);
    }

        /**
     * Mengubah kuantitas satu item di dalam keranjang.
     * Menggunakan Route Model Binding untuk mendapatkan $cart secara otomatis.
     */
    public function update(Request $request, Cart $cart)
    {
        // Pastikan pengguna hanya bisa mengupdate keranjangnya sendiri
        if ($cart->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $newQuantity = $request->quantity;
        $stock = $cart->productVariant->stock;

        // Batasi kuantitas agar tidak melebihi stok
        $cart->quantity = min($newQuantity, $stock);
        $cart->save();

        return $this->getCartResponseData(Auth::user());
    }

    /**
     * Menghapus satu atau lebih item dari keranjang.
     */
    public function remove(Request $request)
    {
        $request->validate([
            'cart_item_ids' => 'required|array',
            'cart_item_ids.*' => 'integer|exists:carts,id',
        ]);

        // Hapus hanya item yang dimiliki oleh pengguna yang sedang login
        Cart::where('user_id', Auth::id())
            ->whereIn('id', $request->cart_item_ids)
            ->delete();

        return $this->getCartResponseData(Auth::user());
    }

    /**
     * Mengosongkan seluruh isi keranjang pengguna.
     */
    public function clear()
    {
        Auth::user()->carts()->delete();

        return $this->getCartResponseData(Auth::user());
    }

    /**
     * Menandai atau membatalkan pilihan item untuk checkout.
     */
    public function toggleSelect(Request $request)
    {
        $request->validate([
            'cart_item_ids' => 'required|array',
            'cart_item_ids.*' => 'integer|exists:carts,id',
            'selected' => 'required|boolean',
        ]);

        Cart::where('user_id', Auth::id())
            ->whereIn('id', $request->cart_item_ids)
            ->update(['selected' => $request->selected]);

        return $this->getCartResponseData(Auth::user());
    }

    public function applyVoucher(Request $request)
    {
        $request->validate(['code' => 'required|string']);
        
        $user = Auth::user(); // Ambil instance user sekali saja

        try {
            // Delegasikan semua pekerjaan ke VoucherService
            $this->voucherService->apply($user, $request->code);
            
            // --- SOLUSI: SEGARKAN MODEL USER ---
            // Perintah ini memaksa Eloquent untuk memuat ulang semua data
            // dan relasi untuk user ini dari database.
            $user->refresh();

            // Sekarang, kembalikan state keranjang yang dijamin sudah ter-update
            return $this->getCartResponseData($user);

        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Menghapus voucher yang sudah diterapkan.
     */
    public function removeVoucher(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        // Delegasikan pekerjaan ke VoucherService
        $this->voucherService->remove(Auth::user(), $request->code);

        // Kembalikan state keranjang yang sudah ter-update
        return $this->getCartResponseData(Auth::user());
    }

}
