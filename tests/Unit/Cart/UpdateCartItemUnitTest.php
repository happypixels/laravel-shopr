<?php

namespace Happypixels\Shopr\Tests\Unit\Cart;

use Happypixels\Shopr\Facades\Cart;
use Happypixels\Shopr\Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Happypixels\Shopr\Models\DiscountCoupon;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;
use Happypixels\Shopr\Exceptions\CartItemNotFoundException;

class UpdateCartItemUnitTest extends TestCase
{
    /** @test */
    public function it_throws_404_if_item_is_not_found()
    {
        try {
            Cart::update('INVALID', ['quantity' => 2]);

            $this->fail('No exception thrown when updating a cart item.');
        } catch (\Exception $e) {
            $this->assertTrue($e instanceof CartItemNotFoundException);
        }
    }

    /** @test */
    public function it_updates_quantity()
    {
        $item = Cart::add(TestShoppable::first());
        $item = Cart::update($item, ['quantity' => 2]);

        $this->assertEquals(2, $item->quantity);
        $this->assertEquals(2, Cart::count());
        $this->assertEquals(1, Cart::items()->count());
    }

    /** @test */
    public function it_refreshes_the_price()
    {
        $item = Cart::add(TestShoppable::first());
        $item = Cart::update($item, ['quantity' => 2]);

        $this->assertEquals(500, $item->price);
        $this->assertEquals(1000, $item->total_price);
        $this->assertEquals('$1,000.00', $item->total_price_formatted);
        $this->assertEquals(1000, Cart::total());
    }

    /** @test */
    public function it_accepts_both_id_and_item()
    {
        $item = Cart::add(TestShoppable::first());

        $item = Cart::update($item->id, ['quantity' => 2]);
        $this->assertEquals(2, $item->quantity);

        $item = Cart::update($item, ['quantity' => 5]);
        $this->assertEquals(5, $item->quantity);
    }

    /** @test */
    public function it_updates_percentage_discount_value()
    {
        // Create a discount coupon for 50%.
        $discount = factory(DiscountCoupon::class)->create(['value' => 50, 'is_fixed' => 0]);

        $item = Cart::add(TestShoppable::first());
        Cart::addDiscount($discount);

        $this->assertEquals(250, Cart::total());
        $this->assertEquals(500, Cart::totalWithoutDiscounts());

        Cart::update($item, ['quantity' => 2]);

        $this->assertEquals(500, Cart::total());
        $this->assertEquals(1000, Cart::totalWithoutDiscounts());
    }

    /** @test */
    public function it_fires_the_correct_event()
    {
        Event::fake();

        $item = Cart::add(TestShoppable::first());
        $item = Cart::update($item, ['quantity' => 5]);

        Event::assertDispatched('shopr.cart.items.updated', function ($event, $data) use ($item) {
            return $data->is($item) && $data->quantity === 5;
        });
    }
}
