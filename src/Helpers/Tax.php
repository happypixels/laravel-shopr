<?php

namespace Happypixels\Shopr\Helpers;

class Tax
{
    /**
     * Returns the total tax of the given amount.
     *
     * @param  float $amount
     * @return float
     */
    public static function getTax($amount)
    {
        $taxRate = config('shopr.tax');

        if (!$taxRate || $taxRate <= 0) {
            return 0;
        }

        return $amount * $taxRate / (100 + $taxRate);
    }

    /**
     * Returns the sub total of the given amount.
     *
     * @param  float $amount
     * @return float
     */
    public static function getSubTotal($amount)
    {
        $tax = self::getTax($amount);

        return $amount - $tax;
    }
}
