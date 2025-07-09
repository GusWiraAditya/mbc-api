<?php

namespace App\Services;

use App\Models\User;
use App\Models\Admin\Voucher;
use Illuminate\Support\Collection;
use Exception;
use Carbon\Carbon;

/**
 * Class ini bertanggung jawab untuk semua logika bisnis yang terkait dengan voucher.
 * Validasi, kalkulasi, dan aplikasi voucher terjadi di sini.
 */
class VoucherService
{
    /**
     * Fungsi utama untuk menerapkan sebuah voucher ke keranjang pengguna.
     */
    public function apply(User $user, string $code): void
    {
        $voucher = Voucher::where('code', strtoupper($code))->first();

        // Panggil semua gerbang validasi
        $this->validateVoucher($user, $voucher);
        $this->validateStacking($user, $voucher);

        // Logika khusus untuk voucher 'unique'
        if ($voucher->stacking_group === 'unique') {
            // Hapus semua voucher lain yang mungkin sudah ada
            $user->appliedVouchers()->sync([]);
        }

        // Terapkan voucher baru
        $user->appliedVouchers()->syncWithoutDetaching([$voucher->id]);
    }

    /**
     * Menghapus voucher yang sudah diterapkan dari keranjang pengguna.
     */
    public function remove(User $user, string $code): void
    {
        $voucher = Voucher::where('code', strtoupper($code))->first();
        if ($voucher) {
            $user->appliedVouchers()->detach($voucher->id);
        }
    }

    public function revalidateAppliedVouchers(User $user): void
    {
        // Ambil semua voucher yang sedang diterapkan oleh pengguna.
        $appliedVouchers = $user->appliedVouchers;

        foreach ($appliedVouchers as $voucher) {
            try {
                // Coba validasi setiap voucher dengan kondisi keranjang saat ini.
                $this->validateVoucher($user, $voucher);
            } catch (Exception $e) {
                // Jika validasi gagal (melempar Exception), berarti voucher ini
                // sudah tidak valid lagi. Hapus dari daftar.
                $this->remove($user, $voucher->code);
            }
        }
    }

    /**
     * Menghitung total diskon dari semua voucher yang diterapkan pada item yang dipilih.
     */
    public function calculateTotalDiscount(User $user, Collection $selectedItems): float
    {
        if ($selectedItems->isEmpty()) {
            return 0;
        }

        return $user->appliedVouchers->sum(function ($voucher) use ($user, $selectedItems) {
            return $this->calculateDiscountForVoucher($user, $voucher, $selectedItems);
        });
    }

    /**
     * Menghitung jumlah diskon untuk SATU voucher spesifik.
     */
    public function calculateDiscountForVoucher(User $user, Voucher $voucher, Collection $selectedItems): float
    {
        $subtotal = $selectedItems->sum(fn($item) => $item['price'] * $item['quantity']);

        switch ($voucher->type) {
            case 'fixed_transaction':
                return (float) $voucher->value;

            case 'percent_transaction':
                $discount = ($subtotal * $voucher->value) / 100;
                return (float) min($discount, $voucher->max_discount ?? $discount);

            case 'fixed_item':
                $applicableItems = $this->getApplicableItems($voucher, $selectedItems);
                $totalQuantity = $applicableItems->sum('quantity');
                return (float) $voucher->value * $totalQuantity;

            case 'percent_item':
                $applicableItems = $this->getApplicableItems($voucher, $selectedItems);
                $applicableSubtotal = $applicableItems->sum(fn($item) => $item['price'] * $item['quantity']);
                $discount = ($applicableSubtotal * $voucher->value) / 100;
                return (float) min($discount, $voucher->max_discount ?? $discount);

            case 'free_shipping':
                // Diskon ongkir ditangani secara terpisah, bukan di sini.
                return 0.0;

            default:
                return 0.0;
        }
    }

    // =====================================================================
    // PRIVATE HELPER METHODS
    // =====================================================================

    /**
     * Melakukan serangkaian validasi untuk sebuah voucher terhadap pengguna dan keranjangnya.
     */
    private function validateVoucher(User $user, ?Voucher $voucher): void
    {
        // ... (Kode validasi lengkap dari Langkah 5 sudah ada di sini) ...
        if (!$voucher) throw new Exception('Kode voucher tidak ditemukan.');
        if (!$voucher->is_active) throw new Exception('Voucher ini sedang tidak aktif.');
        $now = Carbon::now();
        if ($voucher->start_date && $now->isBefore($voucher->start_date)) throw new Exception("Voucher baru bisa digunakan mulai tanggal " . Carbon::parse($voucher->start_date)->format('d M Y'));
        if ($voucher->end_date && $now->isAfter($voucher->end_date)) throw new Exception('Maaf, voucher ini sudah kedaluwarsa.');
        if ($voucher->usage_limit !== null && $voucher->times_used >= $voucher->usage_limit) throw new Exception('Maaf, kuota penggunaan voucher ini sudah habis.');
        if ($voucher->usage_limit_per_user !== null) {
            $userUsageCount = \DB::table('voucher_usages')->where('user_id', $user->id)->where('voucher_id', $voucher->id)->count();
            if ($userUsageCount >= $voucher->usage_limit_per_user) throw new Exception('Anda sudah mencapai batas maksimal penggunaan voucher ini.');
        }
        $selectedCartItems = $user->carts()->where('selected', true)->with('productVariant.product')->get();
        if ($selectedCartItems->isEmpty()) throw new Exception('Pilih setidaknya satu item di keranjang untuk menerapkan voucher.');
        if ($voucher->min_purchase > 0) {
            $subtotal = $selectedCartItems->sum(fn($cartItem) => $cartItem->productVariant->price * $cartItem->quantity);
            if ($subtotal < $voucher->min_purchase) throw new Exception('Minimal pembelian untuk voucher ini adalah ' . number_format($voucher->min_purchase, 0, ',', '.'));
        }
        if (in_array($voucher->type, ['fixed_item', 'percent_item'])) {
            $voucherProductIds = $voucher->products()->pluck('id');
            $voucherCategoryIds = $voucher->categories()->pluck('id');
            if ($voucherProductIds->isNotEmpty() || $voucherCategoryIds->isNotEmpty()) {
                $isApplicable = $selectedCartItems->contains(function ($cartItem) use ($voucherProductIds, $voucherCategoryIds) {
                    $product = $cartItem->productVariant->product;
                    return $voucherProductIds->contains($product->id) || $voucherCategoryIds->contains($product->category_id);
                });
                if (!$isApplicable) throw new Exception('Voucher ini tidak berlaku untuk produk yang ada di keranjang Anda.');
            }
        }
    }

    /**
     * Memvalidasi apakah voucher baru bisa digabungkan dengan yang sudah ada.
     */
    private function validateStacking(User $user, Voucher $newVoucher): void
    {
        $appliedVouchers = $user->appliedVouchers;

        if ($appliedVouchers->isEmpty()) {
            return; // Jika belum ada voucher, pasti bisa diterapkan.
        }

        // Aturan 1: Jika ada voucher 'unique' yang sudah diterapkan, tidak ada lagi yang bisa masuk.
        if ($appliedVouchers->contains('stacking_group', 'unique')) {
            throw new Exception('Voucher ini tidak bisa digabung dengan voucher yang sudah Anda gunakan.');
        }

        // Aturan 2: Jika voucher baru adalah 'unique', tidak bisa diterapkan jika sudah ada voucher lain.
        if ($newVoucher->stacking_group === 'unique') {
            throw new Exception('Voucher ini tidak bisa digabung dengan voucher lain.');
        }

        // Aturan 3: Cek apakah grup dari voucher baru sudah digunakan.
        $newVoucherGroup = $newVoucher->stacking_group;
        if ($appliedVouchers->contains('stacking_group', $newVoucherGroup)) {
            // throw new Exception("Anda sudah menggunakan voucher dari grup '{$newVoucherGroup}'.");
            throw new Exception("Anda sudah menggunakan voucher dengan tipe yang sama.");
        }
    }

    /**
     * Mengambil item dari keranjang yang memenuhi syarat untuk voucher tipe item-specific.
     */
    private function getApplicableItems(Voucher $voucher, Collection $selectedItems): Collection
    {
        $voucherProductIds = $voucher->products()->pluck('id');
        $voucherCategoryIds = $voucher->categories()->pluck('id');

        // Jika voucher tidak terikat pada produk/kategori, maka berlaku untuk semua item.
        if ($voucherProductIds->isEmpty() && $voucherCategoryIds->isEmpty()) {
            return $selectedItems;
        }

        return $selectedItems->filter(function ($item) use ($voucherProductIds, $voucherCategoryIds) {
            // Kita perlu mengambil data produk dari item yang sudah diformat
            $productId = $item['productId']; // Asumsi 'productId' ada di array item
            // TODO: Kita juga perlu categoryId di sini. Ini perlu perbaikan di getCartResponseData
            // $categoryId = $item['categoryId']; 

            return $voucherProductIds->contains($productId) /* || $voucherCategoryIds->contains($categoryId) */;
        });
    }
}
