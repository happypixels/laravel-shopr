<?php

namespace Happypixels\Shopr\Tests\Feature\Cart;

use Happypixels\Shopr\Facades\Cart;
use Happypixels\Shopr\Models\DiscountCoupon;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;
use Happypixels\Shopr\Tests\TestCase;

class GetCartTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        config(['shopr.tax' => 25]);

        $this->item = Cart::add(TestShoppable::first());
    }

    /** @test */
    public function it_returns_the_expected_structure()
    {
        $this->assertEquals([
            'items', 'discounts', 'sub_total', 'sub_total_formatted', 'tax_total',
            'tax_total_formatted', 'total', 'total_formatted', 'count',
        ], array_keys(Cart::get()));
    }

    /** @test */
    public function it_has_added_items()
    {
        $data = Cart::get();

        $this->assertEquals(1, $data['items']->count());
        $this->assertTrue($data['items']->first()->is($this->item));
    }

    /** @test */
    public function it_has_added_discounts()
    {
        $discount = Cart::addDiscount(
            factory(DiscountCoupon::class)->create(['value' => 50, 'is_fixed' => false])
        );

        $data = Cart::get();

        $this->assertEquals(1, $data['discounts']->count());
        $this->assertTrue($data['discounts']->first()->is($discount));
    }
}
