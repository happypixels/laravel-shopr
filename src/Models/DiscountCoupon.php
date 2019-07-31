<?php

namespace Happypixels\Shopr\Models;

use Happypixels\Shopr\Cart\Cart;
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
        'lower_cart_limit',
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
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
     * The calculated, negative value of the discount coupon.
     *
     * @return mixed
     */
    public function getPrice()
    {
        return -($this->getCalculatedPositiveValue());
    }

    /**
     * Returns the positive calculated value of the coupon.
     * Can be used to determine how much the coupon is worth.
     *
     * @return float
     */
    public function getCalculatedPositiveValue()
    {
        if ($this->is_fixed) {
            return $this->value;
        }

        $percentage = $this->value / 100;

        return app(Cart::class)->totalWithoutDiscounts() * $percentage;
    }

    /**
     * Whether or not the model is a discount coupon.
     *
     * @return bool
     */
    public function isDiscount() : bool
    {
        return true;
    }

    public function shouldBeIncludedInItemList() : bool
    {
        return false;
    }
}
