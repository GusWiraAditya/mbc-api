<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\RajaOngkirService;

class LocationController extends Controller
{
    protected $rajaOngkirService;

    public function __construct(RajaOngkirService $rajaOngkirService)
    {
        $this->rajaOngkirService = $rajaOngkirService;
    }

    public function getProvinces()
    {
        return response()->json($this->rajaOngkirService->getProvinces());
    }

    public function getCities($provinceId)
    {
        return response()->json($this->rajaOngkirService->getCities($provinceId));
    }

    public function getDistricts($cityId)
    {
        return response()->json($this->rajaOngkirService->getDistricts($cityId));
    }

    public function getSubdistricts($districtId)
    {
        return response()->json($this->rajaOngkirService->getSubdistricts($districtId));
    }
}