<?php

namespace App\Models\User;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Address extends Model
{
    use HasFactory;

    /**
     * Atribut yang bisa diisi secara massal (mass assignable).
     * Pastikan semua kolom dari migrasi ada di sini.
     */
    protected $fillable = [
        'user_id',
        'label',
        'recipient_name',
        'phone_number',
        'province_id',
        'province_name',
        'city_id',
        'city_name',
        'district_id',
        'district_name',
        'subdistrict_id',
        'subdistrict_name',
        'address_detail',
        'postal_code',
        'is_primary',
    ];

    /**
     * Atribut yang harus di-cast ke tipe data tertentu.
     * Ini memastikan 'is_primary' selalu menjadi boolean (true/false).
     */
    protected $casts = [
        'is_primary' => 'boolean',
    ];

    /**
     * Mendefinisikan relasi ke model User.
     * Satu alamat dimiliki oleh satu User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
