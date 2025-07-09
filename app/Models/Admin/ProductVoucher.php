<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * 
 *
 * @property int $product_id
 * @property int $voucher_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVoucher newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVoucher newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVoucher query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVoucher whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVoucher whereVoucherId($value)
 * @mixin \Eloquent
 */
class ProductVoucher extends Pivot
{
    public $timestamps = false;
}