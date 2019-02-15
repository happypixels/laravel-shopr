<?php

namespace Happypixels\Shopr\Money;

use Money\Money;
use Money\Currency;
use NumberFormatter;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\IntlMoneyFormatter;

class Formatter
{
    /**
     * The decimal separator symbol.
     *
     * @var string
     */
    public $decimalSeparator;

    /**
     * The amount of decimals.
     *
     * @var int
     */
    public $decimalCount;

    /**
     * The default symbol to be used.
     *
     * @var string
     */
    public $symbol;

    /**
     * The desired position of the symbol. Can be 'before' or 'after'.
     * If unspecified, the symbol will be automatically positioned.
     *
     * @var string
     */
    public $symbolPosition;

    /**
     * The thousand separator symbol.
     *
     * @var string
     */
    public $thousandSeparator;

    /**
     * The formatter.
     *
     * @var NumberFormatter
     */
    protected $formatter;

    /**
     * The amount undergoing formatting.
     *
     * @var mixed
     */
    protected $amount;

    /**
     * The symbol that gets assigned to the format, either by customization or by PHP magic.
     *
     * @var string
     */
    protected $assignedSymbol;

    /**
     * Create the default formatter.
     */
    public function __construct()
    {
        $this->formatter = new NumberFormatter($this->getLocale(), NumberFormatter::CURRENCY);
    }

    /**
     * Format the amount into a human readable currency value.
     *
     * @return string
     */
    public function format($amount)
    {
        $this->amount = $amount;

        return $this->applySymbol()
            ->applyThousandSeparator()
            ->applyDecimalSeparator()
            ->applyDecimalCount()
            ->formatAmount()
            ->cleanUp()
            ->applySymbolPosition()
            ->getResult();
    }

    /**
     * Apply a custom symbol to the formatting.
     *
     * @return self
     */
    public function applySymbol()
    {
        if ($this->symbol !== null) {
            $this->formatter->setSymbol(NumberFormatter::CURRENCY_SYMBOL, $this->symbol);
        }

        $this->assignedSymbol = $this->formatter->getSymbol(NumberFormatter::CURRENCY_SYMBOL);

        return $this;
    }

    /**
     * Apply a custom thousand separator to the formatting.
     *
     * @return self
     */
    public function applyThousandSeparator()
    {
        if ($this->thousandSeparator) {
            $this->formatter->setSymbol(
                NumberFormatter::MONETARY_GROUPING_SEPARATOR_SYMBOL,
                $this->thousandSeparator
            );
        }

        return $this;
    }

    /**
     * Apply a specific decimal count.
     *
     * @return self
     */
    public function applyDecimalCount()
    {
        if ($this->decimalCount !== null) {
            $this->formatter->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, $this->decimalCount);
            $this->formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, $this->decimalCount);
        }

        return $this;
    }

    /**
     * Apply a custom decimal separator to the formatting.
     *
     * @return self
     */
    public function applyDecimalSeparator()
    {
        if ($this->decimalSeparator) {
            $this->formatter->setSymbol(
                NumberFormatter::MONETARY_SEPARATOR_SYMBOL,
                $this->decimalSeparator
            );
        }

        return $this;
    }

    /**
     * Returns the formatted amount.
     *
     * @return self
     */
    protected function getResult()
    {
        return $this->amount;
    }

    /**
     * Runs the PHP NumberFormatter on the amount.
     *
     * @return self
     */
    protected function formatAmount()
    {
        $this->amount = (new IntlMoneyFormatter($this->formatter, new ISOCurrencies()))->format(
            new Money(round($this->amount * 100), new Currency($this->getCurrency()))
        );

        return $this;
    }

    /**
     * Puts the symbol in the desired position.
     *
     * @return self
     */
    protected function applySymbolPosition()
    {
        if ($this->symbolPosition && in_array($this->symbolPosition, ['before', 'after'])) {
            // First, remove the assigned symbol from the formatted amount.
            $this->amount = trim(str_replace($this->assignedSymbol, '', $this->amount));

            // Then, add the symbol in it's correct position.
            if ($this->symbolPosition === 'before') {
                $this->amount = $this->assignedSymbol.$this->amount;
            } else {
                $this->amount .= $this->assignedSymbol;
            }
        }

        return $this;
    }

    /**
     * Cleans up unexpected characters and spaces returned by NumberFormatter for some reason.
     *
     * @return self
     */
    protected function cleanUp()
    {
        $this->amount = trim(str_replace(' ', ' ', $this->amount));

        return $this;
    }

    /**
     * Returns the current locale.
     *
     * @return string
     */
    protected function getLocale()
    {
        return app()->getLocale() ?? 'en';
    }

    /**
     * Returns the current currency code.
     *
     * @return string
     */
    protected function getCurrency()
    {
        return config('shopr.currency') ? strtoupper(config('shopr.currency')) : 'USD';
    }
}
