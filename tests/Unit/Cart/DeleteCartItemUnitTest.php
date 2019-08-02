<?php

namespace Happypixels\Shopr\Tests\Unit\Cart;

use Happypixels\Shopr\Facades\Cart;
use Happypixels\Shopr\Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Happypixels\Shopr\Models\DiscountCoupon;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;

class DeleteCartItemUnitTest extends TestCase
{
    /** @test */
    public function it_deletes_the_item()
    {
        $item1 = Cart::add(TestShoppable::first())->save();
        $item2 = Cart::add(TestShoppable::first())->options(['color' => 'Green'])->save();

        $this->assertEquals(2, Cart::count());

        $this->assertTrue(Cart::delete($item2->id));

        $this->assertEquals(1, Cart::count());
        $this->assertEquals($item1->id, Cart::items()->first()->id);
    }

    /** @test */
    public function it_returns_false_if_item_is_not_found()
    {
        $this->assertFalse(Cart::delete('INVALID'));
    }

    /** @test */
    public function it_does_not_delete_discount_coupons()
    {
        $item1 = Cart::add(TestShoppable::first())->save();
        Cart::add(TestShoppable::first())->options(['color' => 'Green'])->save();
        Cart::addDiscount($discount = factory(DiscountCoupon::class)->create());

        $this->assertTrue(Cart::hasDiscount($discount->code));

        Cart::delete($item1->id);

        $this->assertTrue(Cart::hasDiscount($discount->code));
    }

    /** @test */
    public function removing_the_last_item_also_removes_all_discount_coupons()
    {
        $item = Cart::add(TestShoppable::first())->save();
        Cart::addDiscount($discount = factory(DiscountCoupon::class)->create());

        Cart::delete($item->id);

        $this->assertFalse(Cart::hasDiscount($discount->code));
    }

    /** @test */
    public function it_fires_the_event()
    {
        $item = Cart::add(TestShoppable::first())->save();

        Event::fake();

        Cart::delete($item->id);

        Event::assertDispatched('shopr.cart.items.deleted', function ($event, $data) use ($item) {
            return
                $item->id === $data->id &&
                serialize($item) === serialize($data);
        });
    }
}
