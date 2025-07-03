<?php

namespace App\Models\Admin;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'full_name',
        'phone_number',
        'province',
        'city',
        'district',
        'postal_code',
        'street_name',
        'address_detail',
        'maps_link',
        'address_type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
