<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * 
 *
 * @property int $category_id
 * @property int $voucher_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryVoucher newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryVoucher newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryVoucher query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryVoucher whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryVoucher whereVoucherId($value)
 * @mixin \Eloquent
 */
class CategoryVoucher extends Pivot
{
    // Jika tabel pivot Anda menggunakan timestamps (created_at, updated_at),
    // set properti ini menjadi true. Migrasi kita tidak membuatnya, jadi biarkan false.
    public $timestamps = false;
}