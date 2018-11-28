<?php

namespace Happypixels\Shopr\Money;

use Money\Money;
use Money\Currency;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\IntlMoneyFormatter;

class Formatter
{
    public function format($amount)
    {
        $money = new Money(round($amount * 100), new Currency(strtoupper($this->getCurrency())));

        $numberFormatter = new \NumberFormatter($this->getLocale(), \NumberFormatter::CURRENCY);
        $moneyFormatter = new IntlMoneyFormatter($numberFormatter, new ISOCurrencies());

        return $moneyFormatter->format($money);
    }

    protected function getLocale()
    {
        return app()->getLocale() ?? 'en';
    }

    protected function getCurrency()
    {
        return config('shopr.currency') ?? 'USD';
    }
}
