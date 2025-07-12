<?php
// =====================================================================
// FILE: app/Http/Controllers/LocationController.php (REVISI TOTAL)
// =====================================================================

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class LocationController extends Controller
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.rajaongkir.api_key');
        // PASTIKAN .env Anda menggunakan: https://rajaongkir.komerce.id/api/v1
        $this->baseUrl = config('services.rajaongkir.base_url');
    }

    public function getProvinces()
    {
        $response = Http::withHeaders(['key' => $this->apiKey])->get("{$this->baseUrl}/destination/province");
        if ($response->failed()) return response()->json(['message' => 'Gagal mengambil data provinsi.'], 500);
        return response()->json($response->json()['data']);
    }

    public function getCities($provinceId)
    {
        $response = Http::withHeaders(['key' => $this->apiKey])->get("{$this->baseUrl}/destination/city/{$provinceId}");
        if ($response->failed()) return response()->json(['message' => 'Gagal mengambil data kota.'], 500);
        return response()->json($response->json()['data']);
    }

    // NAMA FUNGSI DIREVISI: Ini sekarang mengambil Kecamatan (District)
    public function getDistricts($cityId)
    {
        $response = Http::withHeaders(['key' => $this->apiKey])->get("{$this->baseUrl}/destination/district/{$cityId}");
        if ($response->failed()) return response()->json(['message' => 'Gagal mengambil data kecamatan.'], 500);
        return response()->json($response->json()['data']);
    }
    
    // NAMA FUNGSI DIREVISI: Ini sekarang mengambil Kelurahan (Sub-district)
    public function getSubdistricts($districtId)
    {
        $response = Http::withHeaders(['key' => $this->apiKey])->get("{$this->baseUrl}/destination/sub-district/{$districtId}");
        if ($response->failed()) return response()->json(['message' => 'Gagal mengambil data kelurahan.'], 500);
        return response()->json($response->json()['data']);
    }

    /**
     * REVISI TOTAL: Menghitung ongkir dari Kecamatan ke Kecamatan.
     */
    public function calculateCost(Request $request)
    {
        $validated = $request->validate([
            'destination_district_id' => 'required|integer',
        ]);

        $user = Auth::user();
        $cartItems = $user->carts()->where('selected', true)->with('productVariant')->get();
        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Keranjang Anda kosong.'], 422);
        }

        $totalWeight = max(1, $cartItems->sum(fn($item) => ($item->productVariant->weight ?? 0) * $item->quantity));
        
        // ASUMSI: Anda punya ID Kecamatan asal di .env
        $originDistrictId = config('services.rajaongkir.origin_district_id'); 
        $destinationDistrictId = $validated['destination_district_id'];
        $couriers = 'jne:sicepat:ide:sap:jnt:ninja:tiki:lion:anteraja:pos';

        try {
            $response = Http::withHeaders(['key' => $this->apiKey])
                            ->asForm()
                            ->post("{$this->baseUrl}/calculate/district/domestic-cost", [
                                'origin' => $originDistrictId,
                                'destination' => $destinationDistrictId,
                                'weight' => $totalWeight,
                                'courier' => $couriers,
                                'price' => 'lowest'
                            ]);

            if ($response->failed()) throw new \Exception('Gagal berkomunikasi dengan layanan pengiriman.');
            
            return response()->json($response->json()['data'] ?? []);
        } catch (\Exception $e) {
            Log::error('RajaOngkir Cost Calculation Failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Gagal menghitung ongkos kirim saat ini.'], 500);
        }
    }
}