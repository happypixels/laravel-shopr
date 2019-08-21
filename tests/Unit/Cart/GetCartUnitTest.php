<?php

namespace Happypixels\Shopr\Tests\Unit\Cart;

use Happypixels\Shopr\Facades\Cart;
use Happypixels\Shopr\Tests\TestCase;
use Happypixels\Shopr\Models\DiscountCoupon;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;

class GetCartUnitTest extends TestCase
{
    /** @test */
    public function it_returns_the_correct_data()
    {
        config(['shopr.tax' => 25]);

        $discount = factory(DiscountCoupon::class)->create(['value' => 50, 'is_fixed' => false]);
        $item = Cart::add(TestShoppable::first());
        $discount = Cart::addDiscount($discount);

        $data = Cart::get();

        $this->assertEquals([
            'items', 'discounts', 'sub_total', 'sub_total_formatted', 'tax_total',
            'tax_total_formatted', 'total', 'total_formatted', 'count',
        ], array_keys($data));

        $this->assertEquals(1, $data['items']->count());
        $this->assertEquals(1, $data['discounts']->count());
        $this->assertTrue($data['items']->first()->is($item));
        $this->assertTrue($data['discounts']->first()->is($discount));
    }
}
