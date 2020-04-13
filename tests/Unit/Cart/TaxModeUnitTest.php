<?php

namespace Happypixels\Shopr\Tests\Unit\Cart;

use Happypixels\Shopr\Facades\Cart;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;
use Happypixels\Shopr\Tests\TestCase;

class TaxModeUnitTest extends TestCase
{
    /** @test */
    public function gross_mode()
    {
        config(['shopr.tax' => 25, 'shopr.tax_mode' => 'gross']);

        Cart::add(TestShoppable::first());

        $this->assertEquals(100, Cart::taxTotal());
        $this->assertEquals(400, Cart::subTotal());
        $this->assertEquals(500, Cart::total());
    }

    /** @test */
    public function net_mode()
    {
        config(['shopr.tax' => 25, 'shopr.tax_mode' => 'net']);

        Cart::add(TestShoppable::first());

        $this->assertEquals(125, Cart::taxTotal());
        $this->assertEquals(500, Cart::subTotal());
        $this->assertEquals(625, Cart::total());
    }

    /** @test */
    public function it_defaults_to_gross_mode()
    {
        config(['shopr.tax' => 25, 'shopr.tax_mode' => null]);

        Cart::add(TestShoppable::first());

        $this->assertEquals(100, Cart::taxTotal());
        $this->assertEquals(400, Cart::subTotal());
        $this->assertEquals(500, Cart::total());
    }
}
