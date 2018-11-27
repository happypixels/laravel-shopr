<?php

namespace Happypixels\Shopr\Tests\Feature\Cart;

use Happypixels\Shopr\Contracts\Cart;
use Happypixels\Shopr\Models\DiscountCoupon;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;
use Happypixels\Shopr\Tests\TestCase;
use Illuminate\Support\Facades\Event;

class RemoveCartItemTest extends TestCase
{
    /** @test */
    public function it_removes_the_item()
    {
        $cart   = app(Cart::class);
        $model  = TestShoppable::first();
        $item   = $cart->addItem(get_class($model), 1, 1, $options = []);
        $item2  = $cart->addItem(get_class($model), 1, 1, $options = ['color' => 'Green']);

        $this->assertEquals(2, $cart->count());

        $this->json('DELETE', 'api/shopr/cart/items/' . $item2->id)
            ->assertStatus(200)
            ->assertJsonFragment(['count' => 1]);

        $this->assertEquals(1, $cart->count());
        $this->assertEquals($item->id, $cart->items()->first()->id);
    }

    /** @test */
    public function it_does_not_remove_discount_coupons()
    {
        $discount = factory(DiscountCoupon::class)->create();
        $cart = app(Cart::class);
        $model = TestShoppable::first();
        $item = $cart->addItem(get_class($model), $model->id, 1);
        $item2 = $cart->addItem(get_class($model), $model->id, 1, $options = ['size' => 'L']);

        $cart->addDiscount($discount);

        $this->json('DELETE', 'api/shopr/cart/items/' . $item2->id)
            ->assertStatus(200)
            ->assertJsonFragment(['count' => 1]);

        $this->assertTrue($cart->hasDiscount($discount->code));
    }

    /** @test */
    public function it_fires_event()
    {
        $cart   = app(Cart::class);
        $model  = TestShoppable::first();
        $item   = $cart->addItem(get_class($model), 1, 1, $options = []);

        Event::fake();

        $this->json('DELETE', 'api/shopr/cart/items/' . $item->id)
            ->assertStatus(200)
            ->assertJsonFragment(['count' => 0]);

        Event::assertDispatched('shopr.cart.items.deleted', function ($event, $data) use ($item) {
            return (
                $item->id === $data->id &&
                serialize($item) === serialize($data)
            );
        });
    }
}
