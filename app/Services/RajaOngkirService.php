<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class RajaOngkirService
{
    protected $apiKey;
    protected $baseUrl;
    protected $originDistrictId;

    public function __construct()
    {
        $this->apiKey = config('services.rajaongkir.api_key');
        $this->baseUrl = config('services.rajaongkir.base_url');
        $this->originDistrictId = config('services.rajaongkir.origin_district_id');

        if (!$this->apiKey || !$this->baseUrl || !$this->originDistrictId) {
            throw new Exception('Konfigurasi RajaOngkir tidak lengkap. Pastikan RAJAONGKIR_API_KEY, BASE_URL, dan ORIGIN_DISTRICT_ID sudah diatur.');
        }
    }

    /**
     * Mengambil daftar provinsi dari RajaOngkir.
     */
    public function getProvinces(): array
    {
        return $this->makeRequest('get', '/destination/province');
    }

    /**
     * Mengambil daftar kota berdasarkan ID provinsi.
     */
    public function getCities(string $provinceId): array
    {
        return $this->makeRequest('get', "/destination/city/{$provinceId}");
    }

    /**
     * Mengambil daftar kecamatan berdasarkan ID kota.
     */
    public function getDistricts(string $cityId): array
    {
        return $this->makeRequest('get', "/destination/district/{$cityId}");
    }

    /**
     * Mengambil daftar kelurahan berdasarkan ID kecamatan.
     */
    public function getSubdistricts(string $districtId): array
    {
        return $this->makeRequest('get', "/destination/sub-district/{$districtId}");
    }

    /**
     * Menghitung ongkos kirim dari API RajaOngkir.
     */
    public function calculateCost(int $destinationDistrictId, int $weight, string $couriers): array
    {
        $payload = [
            'origin' => $this->originDistrictId,
            'destination' => $destinationDistrictId,
            'weight' => $weight,
            'courier' => strtolower($couriers),
            'price' => 'lowest', // Asumsi parameter ini selalu ada
        ];

        // dump($payload); // Debugging line, remove in production
        return $this->makeRequest('post', '/calculate/district/domestic-cost', $payload);
    }
    
    /**
     * Helper privat untuk melakukan request ke API.
     */
    private function makeRequest(string $method, string $endpoint, array $payload = []): array
    {
        try {
            $request = Http::withHeaders(['key' => $this->apiKey]);

            if ($method === 'post') {
                $response = $request->asForm()->post($this->baseUrl . $endpoint, $payload);
            } else {
                $response = $request->get($this->baseUrl . $endpoint);
            }

            $response->throw(); // Lempar exception jika status bukan 2xx

            return $response->json('data') ?? [];

        } catch (\Illuminate\Http\Client\RequestException $e) {
            $errorData = $e->response->json('rajaongkir.status.description') ?? $e->getMessage();
            Log::error('RajaOngkir Request Failed', ['error' => $errorData, 'endpoint' => $endpoint]);
            throw new Exception('Gagal berkomunikasi dengan layanan pengiriman: ' . $errorData);
        } catch (Exception $e) {
            Log::error('RajaOngkir General Error', ['error' => $e->getMessage(), 'endpoint' => $endpoint]);
            throw new Exception('Terjadi masalah pada layanan pengiriman.');
        }
    }
}