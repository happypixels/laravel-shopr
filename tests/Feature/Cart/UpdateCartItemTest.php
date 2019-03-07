<?php

namespace Happypixels\Shopr\Tests\Feature\Cart;

use Happypixels\Shopr\Cart\Cart;
use Happypixels\Shopr\Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Happypixels\Shopr\Models\DiscountCoupon;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;

class UpdateCartItemTest extends TestCase
{
    /** @test */
    public function it_updates_the_item_quantity()
    {
        $cart = app(Cart::class);
        $model = TestShoppable::first();
        $item = $cart->addItem(get_class($model), 1, 1);

        $this->assertEquals(1, $cart->items()->first()->quantity);

        $response = $this->json('PATCH', 'api/shopr/cart/items/'.$item->id, ['quantity' => 2])
            ->assertStatus(200)
            ->assertJsonFragment(['count' => 2]);

        $this->assertEquals(2, $cart->count());
        $this->assertEquals(2, $cart->items()->first()->quantity);
    }

    /** @test */
    public function it_updates_the_cart_totals_correctly()
    {
        $cart = app(Cart::class);
        $model = TestShoppable::first();
        $item = $cart->addItem(get_class($model), $model->id, 1, [], [
            ['shoppable_type' => get_class($model), 'shoppable_id' => 1],
            ['shoppable_type' => get_class($model), 'shoppable_id' => 1, 'options' => ['color' => 'Green']],
        ]);

        $response = $this->json('PATCH', 'api/shopr/cart/items/'.$item->id, ['quantity' => 2])
            ->assertStatus(200)
            ->assertJsonFragment(['count' => 2, 'total' => 3000]);

        // Make sure the quantity and totals of all subItems are updated as well.
        $subItems = $cart->items()->first()->subItems;
        $this->assertEquals([2, 2], $subItems->pluck('quantity')->toArray());
        $this->assertEquals([1000, 1000], $subItems->pluck('total')->toArray());
    }

    /** @test */
    public function it_does_not_remove_discount_coupons()
    {
        $discount = factory(DiscountCoupon::class)->create();
        $cart = app(Cart::class);
        $model = TestShoppable::first();
        $item = $cart->addItem(get_class($model), $model->id, null);

        $cart->addDiscount($discount);

        $response = $this->json('PATCH', 'api/shopr/cart/items/'.$item->id, ['quantity' => 2])
            ->assertStatus(200)
            ->assertJsonFragment(['count' => 2]);

        $this->assertTrue($cart->hasDiscount($discount->code));
        $this->assertEquals(2, $cart->items()->first()->quantity);
    }

    /** @test */
    public function it_updates_percentage_discount_value()
    {
        $discount = factory(DiscountCoupon::class)->create(['value' => 50, 'is_fixed' => 0]);

        $cart = app(Cart::class);
        $model = TestShoppable::first();
        $item = $cart->addItem(get_class($model), $model->id, null);

        $cart->addDiscount($discount);

        $this->assertEquals(250, $cart->summary()['total']);

        $response = $this->json('PATCH', 'api/shopr/cart/items/'.$item->id, ['quantity' => 2])
            ->assertStatus(200)
            ->assertJsonFragment(['count' => 2]);

        $this->assertEquals(500, $cart->summary()['total']);
        $this->assertEquals(-500, $cart->discounts()->first()->total);
        $this->assertEquals(-500, $cart->discounts()->first()->price);
        $this->assertEquals('-$500.00', $cart->discounts()->first()->price_formatted);
    }

    /** @test */
    public function it_fires_event()
    {
        $cart = app(Cart::class);
        $model = TestShoppable::first();
        $item = $cart->addItem(get_class($model), 1, 1);

        Event::fake();

        $response = $this->json('PATCH', 'api/shopr/cart/items/'.$item->id, ['quantity' => 2])
            ->assertStatus(200)
            ->assertJsonFragment(['count' => 2]);

        $item = $cart->items()->first();

        Event::assertDispatched('shopr.cart.items.updated', function ($event, $data) use ($item) {
            return
                $item->id === $data->id &&
                serialize($item) === serialize($data);
        });
    }
}
