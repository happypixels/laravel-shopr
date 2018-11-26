<?php

namespace Happypixels\Shopr\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class DiscountCoupon extends Shoppable
{
    use SoftDeletes;

    protected $fillable = [
        'valid_from',
        'valid_until',
        'uses',
        'code',
        'description',
        'is_fixed',
        'value',
    ];

    /**
     * Only returns coupons where the current date is within their time limits.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeValidTimespan($query)
    {
        $now = now();

        return $query->whereRaw('
            (valid_from IS NULL OR valid_from <= "'.$now.'") AND 
            (valid_until IS NULL OR valid_until >= "'.$now.'")
        ');
    }

    /**
     * The name/title of the model.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->code;
    }

    /**
     * Whether or not the model is a discount coupon.
     *
     * @return boolean
     */
    public function isDiscount()
    {
        return true;
    }
}
