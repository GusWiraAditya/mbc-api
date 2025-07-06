<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        return Setting::all()->pluck('value', 'key');
    }

    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'shop_name'    => 'nullable|string|max:255',
            'shop_tagline' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => ['nullable', 'string', 'min:10', 'max:15', 'regex:/^((\+62)|0|62)8[1-9][0-9]{6,11}$/'],
            'shop_address' => 'nullable|string',
            'shop_latitude' => 'nullable|numeric',
            'shop_longitude' => 'nullable|numeric',
            'shipping_fee' => 'nullable|numeric|min:0',
            'seo_meta_title' => 'nullable|string|max:70',
            'seo_meta_description' => 'nullable|string|max:160',
            'seo_meta_keywords' => 'nullable|string',
            'social_facebook_url' => 'nullable|url',
            'social_instagram_url' => 'nullable|url',
            'social_twitter_url' => 'nullable|url',
            'social_tiktok_url' => 'nullable|url',
            'social_youtube_url' => 'nullable|url',
            'shop_logo_primary' => 'nullable|image|mimes:png,jpg,jpeg,webp,svg|max:2048',
            'shop_logo_secondary' => 'nullable|image|mimes:png,jpg,jpeg,webp,svg|max:2048',
            'shop_favicon' => ['nullable', 'file', 'mimes:ico,png,svg,webp', 'max:512'],
            'hero_headline' => 'nullable|string|max:255',
            'hero_subheadline' => 'nullable|string',
            'hero_background_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3048',
        ]);
        foreach ($validatedData as $key => $value) {
            if (is_null($value)) continue;
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        if ($request->hasFile('shop_logo_primary')) {
            $path = $request->file('shop_logo_primary')->store('settings', 'public');
            Setting::updateOrCreate(['key' => 'shop_logo_primary'], ['value' => $path]);
        }
        if ($request->hasFile('shop_logo_secondary')) {
            $path = $request->file('shop_logo_secondary')->store('settings', 'public');
            Setting::updateOrCreate(['key' => 'shop_logo_secondary'], ['value' => $path]);
        }
        // --- REVISI: Tambahkan logika untuk menyimpan favicon ---
        if ($request->hasFile('shop_favicon')) {
            $path = $request->file('shop_favicon')->store('settings', 'public');
            Setting::updateOrCreate(['key' => 'shop_favicon'], ['value' => $path]);
        }
        if ($request->hasFile('hero_background_image')) {
            $path = $request->file('hero_background_image')->store('settings', 'public');
            Setting::updateOrCreate(['key' => 'hero_background_image'], ['value' => $path]);
        }
        return response()->json(['message' => 'Pengaturan berhasil diperbarui.']);
    }
}
