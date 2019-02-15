<?php

namespace Happypixels\Shopr\Tests\Feature\Unit;

use Happypixels\Shopr\Tests\TestCase;

class DefaultFormatterTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        app()->setLocale('sv_SE');
        config(['shopr.currency' => 'SEK']);
    }

    /** @test */
    public function it_defaults_to_chosen_currency_standards()
    {
        $formatter = app(config('shopr.money_formatter'));

        $this->assertEquals('25,00 kr', $formatter->format('25'));
    }

    /** @test */
    public function symbol_is_customizable()
    {
        $formatter = app(config('shopr.money_formatter'));
        $formatter->symbol = 'USD';

        $this->assertEquals('25,00 USD', $formatter->format(25));
    }

    /** @test */
    public function symbol_is_removable()
    {
        $formatter = app(config('shopr.money_formatter'));
        $formatter->symbol = false;

        $this->assertEquals('25,00', $formatter->format(25));
    }

    /** @test */
    public function thousand_separator_is_customizable()
    {
        $formatter = app(config('shopr.money_formatter'));
        $formatter->thousandSeparator = '-';

        $this->assertEquals('25-000-000,00 kr', $formatter->format(25000000));
    }

    /** @test */
    public function decimal_count_is_customizable()
    {
        $formatter = app(config('shopr.money_formatter'));
        $formatter->decimalCount = 4;

        $this->assertEquals('25,5000 kr', $formatter->format(25.5));

        $formatter = app(config('shopr.money_formatter'));
        $formatter->decimalCount = 0;

        $this->assertEquals('26 kr', $formatter->format(25.5));
    }

    /** @test */
    public function symbol_before_is_customizable()
    {
        // Swedish.
        $formatter = app(config('shopr.money_formatter'));
        $formatter->symbolBefore = '~ ';

        $this->assertEquals('~ 25,50 kr', $formatter->format(25.5));
    }

    /** @test */
    public function decimal_symbol_is_customizable()
    {
        // Swedish.
        $formatter = app(config('shopr.money_formatter'));
        $formatter->decimalSeparator = '^';

        $this->assertEquals('25^50 kr', $formatter->format(25.5));
    }

    /** @test */
    public function it_combines_the_settings()
    {
        $formatter = app(config('shopr.money_formatter'));
        $formatter->decimalSeparator = '^';
        $formatter->symbol = 'SYM';
        $formatter->decimalCount = 4;
        $formatter->thousandSeparator = '-';
        $formatter->symbolBefore = '~ ';

        $this->assertEquals('~ 25-000-000^5000 SYM', $formatter->format(25000000.5));
    }
}
