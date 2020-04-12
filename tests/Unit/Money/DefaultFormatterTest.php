<?php

namespace Happypixels\Shopr\Tests\Feature\Unit;

use Happypixels\Shopr\Tests\TestCase;

class DefaultFormatterTest extends TestCase
{
    protected $formatter;

    public function setUp(): void
    {
        parent::setUp();

        app()->setLocale('en_US');
        config(['shopr.currency' => 'USD']);
        $this->formatter = app(config('shopr.money_formatter'));
    }

    /** @test */
    public function it_defaults_to_chosen_currency_standards()
    {
        $this->assertEquals('$25.00', $this->formatter->format('25'));
    }

    /** @test */
    public function symbol_is_customizable()
    {
        $this->formatter->symbol = 'kr';

        $this->assertEquals('kr25.00', $this->formatter->format(25));
    }

    /** @test */
    public function symbol_is_removable()
    {
        $this->formatter->symbol = false;

        $this->assertEquals('25.00', $this->formatter->format(25));
    }

    /** @test */
    public function symbol_position_is_customizable()
    {
        // Using the default symbol.
        $this->formatter->symbolPosition = 'after';
        $this->assertEquals('25.00$', $this->formatter->format(25));

        $this->formatter->symbolPosition = 'before';
        $this->assertEquals('$25.00', $this->formatter->format(25));

        // Using a custom symbol.
        $this->formatter->symbol = '¢';
        $this->assertEquals('¢25.00', $this->formatter->format(25));

        $this->formatter->symbolPosition = 'after';
        $this->assertEquals('25.00¢', $this->formatter->format(25));
    }

    /** @test */
    public function thousand_separator_is_customizable()
    {
        $this->formatter->thousandSeparator = '-';

        $this->assertEquals('$25-000-000.00', $this->formatter->format(25000000));
    }

    /** @test */
    public function decimal_count_is_customizable()
    {
        $this->formatter->decimalCount = 4;
        $this->assertEquals('$25.5000', $this->formatter->format(25.5));

        $this->formatter->decimalCount = 0;
        $this->assertEquals('$26', $this->formatter->format(25.5));
    }

    /** @test */
    public function decimal_separator_is_customizable()
    {
        $this->formatter->decimalSeparator = '^';

        $this->assertEquals('$25^50', $this->formatter->format(25.5));
    }

    /** @test */
    public function it_combines_the_settings()
    {
        $formatter = app(config('shopr.money_formatter'));
        $formatter->decimalSeparator = '^';
        $formatter->symbol = 'SYM';
        $formatter->decimalCount = 4;
        $formatter->thousandSeparator = '-';
        $formatter->symbolPosition = 'after';

        $this->assertEquals('25-000-000^5000SYM', $formatter->format(25000000.5));
    }
}
