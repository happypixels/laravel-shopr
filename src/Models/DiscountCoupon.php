<?php

namespace Happypixels\Shopr\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiscountCoupon extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'valid_from',
        'valid_until',
        'uses',
        'code',
        'is_fixed',
        'value',
    ];

    public function scopeValid($query)
    {
        $now = now();

        return $query->whereRaw('
            (valid_from IS NULL OR valid_from <= "'.$now.'") AND 
            (valid_until IS NULL OR valid_until >= "'.$now.'")
        ');
    }
}
