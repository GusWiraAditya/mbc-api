<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CategoryVoucher extends Pivot
{
    // Jika tabel pivot Anda menggunakan timestamps (created_at, updated_at),
    // set properti ini menjadi true. Migrasi kita tidak membuatnya, jadi biarkan false.
    public $timestamps = false;
}