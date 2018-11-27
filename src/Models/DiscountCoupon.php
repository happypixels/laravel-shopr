<?php

namespace Happypixels\Shopr\Models;

use Happypixels\Shopr\Contracts\Cart;
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
     * The price of the model.
     *
     * @return mixed
     */
    public function getPrice()
    {
        $cart = app(Cart::class);

        if ($this->is_fixed) {
            $amount = -$this->value;
        } else {
            $percentage = $this->value / 100;
            $amount = -($cart->total() * $percentage);
        }

        return $amount;
    }

    /**
     * Whether or not the model is a discount coupon.
     *
     * @return boolean
     */
    public function isDiscount() : bool
    {
        return true;
    }
}
