<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Models\Admin\Setting;
use App\Services\RajaOngkirService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ShippingController extends Controller
{
    protected $rajaOngkirService;

    public function __construct(RajaOngkirService $rajaOngkirService)
    {
        $this->rajaOngkirService = $rajaOngkirService;
    }

    public function calculateCost(Request $request)
    {
        $validated = $request->validate([
            'destination_district_id' => 'required|integer',
        ]);

        try {
            // --- BLOK TRY: Mencoba alur normal ---
            $user = Auth::user();
            $cartItems = $user->carts()->where('selected', true)->with('productVariant')->get();

            if ($cartItems->isEmpty()) {
                return response()->json([], 400);
            }

            $totalWeight = max(1, $cartItems->sum(fn($item) => ($item->productVariant->weight ?? 0) * $item->quantity));
            $couriers = 'jne:sicepat:ide:sap:jnt:ninja:tiki:lion:anteraja:pos';

            // Panggil service RajaOngkir
            $shippingOptions = $this->rajaOngkirService->calculateCost(
                $validated['destination_district_id'],
                $totalWeight,
                $couriers
            );

            // Jika RajaOngkir berhasil tapi tidak ada opsi, kembalikan array kosong
            if (empty($shippingOptions)) {
                return response()->json([]);
            }

            return response()->json($shippingOptions);
        } catch (\Exception $e) {
            // --- BLOK CATCH: Alur darurat jika RajaOngkir gagal ---
            Log::error('RajaOngkir API failed, activating fallback. Error: ' . $e->getMessage());

            try {
                // Ambil ongkir default dari database Anda
                // Sesuaikan query ini dengan struktur database Anda
                $defaultFee = Setting::where('key', 'shipping_fee')->first();
                $defaultEtd = Setting::where('key', 'shipping_etd')->first();

                // Jika tidak ada setting default, kembalikan error
                if (!$defaultFee) {
                    return response()->json(['message' => 'Layanan pengiriman sedang tidak tersedia.'], 503);
                }

                // Buat format response yang MENIRU format RajaOngkir
                $fallbackResponse = [
                [
                    "code" => "FLATRATE",
                    "name" => "Pengiriman Standar",
                    "service" => "Reguler",
                    "description" => $defaultFee->label ?? "Ongkos Kirim Tetap",
                    "cost" => (int) $defaultFee->value,
                    "etd" => $defaultEtd->value ?? "3-5 Hari",
                ]
            ];

                // Kirim data fallback ini ke frontend seolah-olah ini adalah respons sukses
                return response()->json($fallbackResponse);
            } catch (\Exception $dbError) {
                // Tangani jika pengambilan data dari database juga gagal
                Log::error('Could not fetch fallback shipping fee. Error: ' . $dbError->getMessage());
                return response()->json(['message' => 'Layanan pengiriman sedang tidak tersedia.'], 503);
            }
        }
    }
}
