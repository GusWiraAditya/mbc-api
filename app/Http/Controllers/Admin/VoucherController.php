<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Voucher;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class VoucherController extends Controller
{
    /**
     * Menampilkan daftar semua voucher.
     */
    public function index()
    {
        // dd('METHOD INDEX DIPANGGIL'); 
        return Voucher::withCount(['products', 'categories'])->latest()->paginate(10);
    }

    /**
     * Menyimpan voucher baru ke database.
     */
    public function store(Request $request)
    {
        // Aturan validasi dasar

        $rules = [
            'code' => 'required|string|max:255|unique:vouchers,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => ['required', Rule::in(['fixed_transaction', 'percent_transaction', 'fixed_item', 'percent_item', 'free_shipping'])],
            'value' => 'required|numeric|min:0',
            'max_discount' => ['nullable', 'numeric', 'min:1', 'required_if:type,percent_transaction,percent_item'],
            'min_purchase' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:0',
            'usage_limit_per_user' => 'nullable|integer|min:0',
            'stacking_group' => ['nullable', Rule::in(['transaction_discount', 'item_discount', 'shipping_discount', 'unique'])],
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'required|boolean',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:product,id',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
        ];

        // Validasi kustom untuk memastikan item/kategori dipilih untuk tipe voucher item
        $validator = Validator::make($request->all(), $rules);
        $validator->after(function ($validator) use ($request) {
            $type = $request->input('type');
            if (in_array($type, ['fixed_item', 'percent_item']) && empty($request->input('product_ids')) && empty($request->input('category_ids'))) {
                $validator->errors()->add('product_ids', 'Pilih setidaknya satu produk atau kategori untuk tipe voucher ini.');
            }
        });

        $validated = $validator->validate();
        $voucher = Voucher::create($validated);

        if (!empty($validated['product_ids'])) {
            $voucher->products()->sync($validated['product_ids']);
        }
        if (!empty($validated['category_ids'])) {
            $voucher->categories()->sync($validated['category_ids']);
        }

        return response()->json($voucher->load('products', 'categories'), 201);
    }

    /**
     * Menampilkan detail satu voucher.
     */
    public function show($id)
    {
        // dd('METHOD SHOW DIPANGGIL');
        $voucher = Voucher::with(['products', 'categories'])->findOrFail($id);

        return response()->json($voucher);
    }

    /**
     * Memperbarui voucher yang sudah ada.
     */
    public function update(Request $request, Voucher $voucher)
    {
        $rules = [
            'code' => ['required', 'string', 'max:255', Rule::unique('vouchers')->ignore($voucher->id)],
            'name' => 'required|string|max:255',
            // ...sisa aturan sama seperti store
            'description' => 'nullable|string',
            'type' => ['required', Rule::in(['fixed_transaction', 'percent_transaction', 'fixed_item', 'percent_item', 'free_shipping'])],
            'value' => 'required|numeric|min:0',
            'max_discount' => ['nullable', 'numeric', 'min:1', 'required_if:type,percent_transaction,percent_item'],
            'min_purchase' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:0',
            'usage_limit_per_user' => 'nullable|integer|min:0',
            'stacking_group' => ['nullable', Rule::in(['transaction_discount', 'item_discount', 'shipping_discount', 'unique'])],

            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'required|boolean',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:product,id',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
        ];

        $validator = Validator::make($request->all(), $rules);
        $validator->after(function ($validator) use ($request) {
            $type = $request->input('type');
            if (in_array($type, ['fixed_item', 'percent_item']) && empty($request->input('product_ids')) && empty($request->input('category_ids'))) {
                $validator->errors()->add('product_ids', 'Pilih setidaknya satu produk atau kategori untuk tipe voucher ini.');
            }
        });

        $validated = $validator->validate();
        $voucher->update($validated);

        if ($request->has('product_ids')) {
            $voucher->products()->sync($validated['product_ids'] ?? []);
        }
        if ($request->has('category_ids')) {
            $voucher->categories()->sync($validated['category_ids'] ?? []);
        }

        return response()->json($voucher->load('products', 'categories'));
    }

    public function destroy(Voucher $voucher)
    {
        $voucher->delete();
        return response()->json(null, 204);
    }
}
