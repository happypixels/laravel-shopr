<?php

namespace Happypixels\Shopr\Tests\Feature\Cart;

use Happypixels\Shopr\Facades\Cart;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;
use Happypixels\Shopr\Tests\TestCase;

class GetTaxTotalTest extends TestCase
{
    /** @test */
    public function it_returns_the_total_tax()
    {
        config(['shopr.tax' => 25]);

        Cart::add(TestShoppable::first(), ['quantity' => 5]);

        // 25% tax of 2500 = 500. Subtotal should be 2000.
        $this->assertEquals(500, Cart::taxTotal());
    }

    /** @test */
    public function test_net_tax()
    {
        config(['shopr.tax' => 25, 'shopr.tax_mode' => 'net']);

        Cart::add(TestShoppable::first());

        $this->assertEquals(125, Cart::taxTotal());
    }
}
