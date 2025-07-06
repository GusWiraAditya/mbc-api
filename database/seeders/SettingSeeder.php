<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Admin\Setting;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        Setting::truncate();
        $settings = [
            ['key' => 'shop_name', 'value' => 'MadeByCan', 'label' => 'Nama Toko'],
            ['key' => 'shop_tagline', 'value' => 'Handcrafted Genuine Leather', 'label' => 'Tagline Toko'],
            ['key' => 'contact_email', 'value' => 'support@madebycan.com', 'label' => 'Email Kontak'],
            ['key' => 'contact_phone', 'value' => '081234567890', 'label' => 'Nomor Telepon'],
            ['key' => 'shop_address', 'value' => 'Jl. Cibaduyut Raya No. 123, Bandung, Jawa Barat, Indonesia', 'label' => 'Alamat Toko'],
            ['key' => 'shop_latitude', 'value' => '-6.9501', 'label' => 'Latitude Lokasi'],
            ['key' => 'shop_longitude', 'value' => '107.5896', 'label' => 'Longitude Lokasi'],
            ['key' => 'shop_logo_primary', 'value' => null, 'label' => 'Logo untuk Latar Terang)'],
            ['key' => 'shop_logo_secondary', 'value' => null, 'label' => 'Logo untuk Latar Gelap)'],
            ['key' => 'shop_favicon', 'value' => null, 'label' => 'Ikon Favicon'],
            ['key' => 'shipping_fee', 'value' => '15000', 'label' => 'Biaya Ongkos Kirim Flat'],
            ['key' => 'seo_meta_title', 'value' => 'MadeByCan | Tas Kulit Asli Buatan Tangan', 'label' => 'Judul Meta SEO'],
            ['key' => 'seo_meta_description', 'value' => 'Temukan tas kulit asli, ransel, dan aksesoris buatan tangan berkualitas tinggi dari Bandung.', 'label' => 'Deskripsi Meta SEO'],
            ['key' => 'seo_meta_keywords', 'value' => 'tas kulit, tas kulit asli, tas buatan tangan, tas pria, tas wanita, leather bag', 'label' => 'Keyword SEO'],
            ['key' => 'social_facebook_url', 'value' => 'https://facebook.com', 'label' => 'URL Facebook'],
            ['key' => 'social_tiktok_url', 'value' => 'https://tiktok.com', 'label' => 'URL TikTok'],
            ['key' => 'social_instagram_url', 'value' => 'https://instagram.com', 'label' => 'URL Instagram'],
            ['key' => 'social_twitter_url', 'value' => 'https://twitter.com', 'label' => 'URL Twitter'],
            ['key' => 'social_youtube_url', 'value' => 'https://youtube.com', 'label' => 'URL YouTube'],
            ['key' => 'hero_headline', 'value' => 'Handcrafted Genuine<br/>Leather Camera.', 'label' => 'Judul Utama Homepage'],
            ['key' => 'hero_subheadline', 'value' => 'This elegant camera bag is crafted from high-quality genuine leather, offering durability, style, and practical organization.', 'label' => 'Sub-Judul Homepage'],
            ['key' => 'hero_background_image', 'value' => null, 'label' => 'Gambar Latar Homepage'],
        ];
        foreach ($settings as $setting) { Setting::create($setting); }
    }
}