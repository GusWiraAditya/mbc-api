<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'google_id',
        'password',
        'profile_picture',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * REVISI: Tambahkan Accessor/Mutator ini.
     * Atribut ini akan secara otomatis ditambahkan ke data JSON pengguna.
     * Ia akan bernilai true hanya jika pengguna memiliki google_id.
     *
     * @return bool
     */
    public function getIsGoogleAccountAttribute(): bool
    {
        return !empty($this->google_id);
    }

    /**
     * Kita perlu menambahkan atribut baru ini ke properti $appends
     * agar ia otomatis disertakan saat model diubah menjadi JSON.
     */
    protected $appends = [
        'is_google_account',
    ];
}
