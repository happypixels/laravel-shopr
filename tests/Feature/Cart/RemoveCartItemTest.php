<?php

namespace Happypixels\Shopr\Tests\Feature\Cart;

use Happypixels\Shopr\Facades\Cart;
use Happypixels\Shopr\Models\DiscountCoupon;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;
use Happypixels\Shopr\Tests\TestCase;
use Illuminate\Support\Facades\Event;

class RemoveCartItemTest extends TestCase
{
    /** @test */
    public function it_deletes_the_item_and_returns_true()
    {
        $item1 = Cart::add(TestShoppable::first());
        $item2 = Cart::add(TestShoppable::first(), ['options' => ['color' => 'Green']]);

        $this->assertEquals(2, Cart::count());
        $this->assertTrue(Cart::delete($item2->id));
        $this->assertEquals(1, Cart::count());
        $this->assertEquals($item1->id, Cart::items()->first()->id);
    }

    /** @test */
    public function it_accepts_both_the_id_and_the_item()
    {
        $item1 = Cart::add(TestShoppable::first());
        $item2 = Cart::add(TestShoppable::first(), ['options' => ['color' => 'Green']]);

        $this->assertTrue(Cart::delete($item2->id));
        $this->assertTrue(Cart::delete($item1));
        $this->assertEquals(0, Cart::count());
    }

    /** @test */
    public function it_refreshes_relative_discount_values()
    {
        // Create a discount coupon for 50%.
        $discount = factory(DiscountCoupon::class)->create(['value' => 50, 'is_fixed' => 0]);

        $item = Cart::add(TestShoppable::first());
        Cart::add(TestShoppable::first(), ['options' => ['color' => 'Green']]);
        Cart::addDiscount($discount);

        $this->assertEquals(500, Cart::total());

        Cart::delete($item);

        $this->assertEquals(250, Cart::total());
    }

    /** @test */
    public function it_removes_any_discounts_if_last_item_is_removed()
    {
        // Create a discount coupon for 50%.
        $discount = factory(DiscountCoupon::class)->create(['value' => 50, 'is_fixed' => 0]);

        $item = Cart::add(TestShoppable::first());
        Cart::addDiscount($discount);

        Cart::delete($item);

        $this->assertEquals(0, Cart::total());
        $this->assertFalse(Cart::hasDiscount($discount->code));
    }

    /** @test */
    public function it_returns_false_if_item_is_not_found()
    {
        $this->assertFalse(Cart::delete('INVALID'));
    }

    /** @test */
    public function it_fires_the_deleted_event()
    {
        $item = Cart::add(TestShoppable::first());

        Event::fake();

        Cart::delete($item->id);

        Event::assertDispatched('shopr.cart.items.deleted', function ($event, $data) use ($item) {
            return $data->is($item);
        });
    }
}
