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
        'is_fixed',
        'value',
    ];

    /**
     * The name/title of the model.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->code;
    }

    public function scopeValid($query)
    {
        $now = now();

        return $query->whereRaw('
            (valid_from IS NULL OR valid_from <= "'.$now.'") AND 
            (valid_until IS NULL OR valid_until >= "'.$now.'")
        ');
    }
}
