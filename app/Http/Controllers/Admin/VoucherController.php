<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Admin\Voucher;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVoucherRequest;
use App\Http\Requests\UpdateVoucherRequest;

class VoucherController extends Controller
{
    public function index()
    {
        $vouchers = Voucher::latest()->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $vouchers,
        ]);
    }

    public function store(StoreVoucherRequest $request)
    {
        $voucher = Voucher::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Voucher berhasil ditambahkan',
            'data' => $voucher,
        ], 201);
    }

    public function show(Voucher $voucher)
    {
        return response()->json([
            'success' => true,
            'data' => $voucher,
        ]);
    }

    public function update(UpdateVoucherRequest $request, Voucher $voucher)
    {
        $voucher->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Voucher berhasil diperbarui',
            'data' => $voucher,
        ]);
    }

    public function destroy(Voucher $voucher)
    {
        $voucher->delete();

        return response()->json([
            'success' => true,
            'message' => 'Voucher berhasil dihapus',
        ]);
    }
}
