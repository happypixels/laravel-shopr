<?php

namespace Happypixels\Shopr\Tests\Unit\Money;

use Happypixels\Shopr\Tests\TestCase;
use Happypixels\Shopr\Money\Formatter;

class FormatterUnitTest extends TestCase
{
    /** @test */
    public function it_handles_decimals_with_good_precision()
    {
        config(['shopr.currency' => 'USD']);

        $formatter = new Formatter;

        $this->assertEquals('$57.85', $formatter->format(57.851239669421));
        $this->assertEquals('$12.15', $formatter->format(12.148760330579));
    }
}
