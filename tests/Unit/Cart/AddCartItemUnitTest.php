<?php

namespace Happypixels\Shopr\Tests\Unit\Cart;

use Happypixels\Shopr\Cart\Cart;
use Happypixels\Shopr\Cart\CartItem;
use Happypixels\Shopr\Models\DiscountCoupon;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;
use Happypixels\Shopr\Tests\TestCase;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Event;

class AddCartItemUnitTest extends TestCase
{
    /** @test */
    public function it_adds_the_item()
    {
        $cart = app(Cart::class);
        $model = TestShoppable::first();
        $item = $cart->addItem(get_class($model), $model->id, 1, ['size' => 'Large', 'color' => 'Red']);

        $this->assertEquals(1, $cart->items()->count());

        $item = $cart->items()->first();
        $this->assertEquals(get_class($model), $item->shoppableType);
        $this->assertEquals($model->id, $item->shoppableId);
        $this->assertEquals(1, $item->quantity);
        $this->assertEquals($model, $item->shoppable);
        $this->assertEquals('Large', $item->options['size']);
        $this->assertEquals('Red', $item->options['color']);
    }

    /** @test */
    public function it_accepts_sub_items()
    {
        $cart = app(Cart::class);
        $model = TestShoppable::first();
        $item = $cart->addItem(get_class($model), $model->id, 1, [], [
            ['shoppable_type' => get_class($model), 'shoppable_id' => 1],
            ['shoppable_type' => get_class($model), 'shoppable_id' => 1, 'options' => ['color' => 'Green']],
        ]);

        $item = $cart->items()->first();
        $this->assertEquals(2, $item->subItems->count());
        $this->assertEquals(1, $item->subItems->first()->shoppable->id);
        $this->assertEquals(get_class($model), get_class($item->subItems->last()->shoppable));
        $this->assertEquals('Green', $item->subItems->last()->options['color']);
    }

    /** @test */
    public function sub_items_automatically_get_the_parent_quantity()
    {
        $cart = app(Cart::class);
        $model = TestShoppable::first();

        $item = $cart->addItem(get_class($model), $model->id, 3, [], [
            ['shoppable_type' => get_class($model), 'shoppable_id' => 1],
            ['shoppable_type' => get_class($model), 'shoppable_id' => 1, 'options' => ['color' => 'Green']],
        ]);

        $item = $cart->items()->first();
        $this->assertEquals(3, $item->subItems->first()->quantity);
    }

    /** @test */
    public function quantity_defaults_to_1_if_not_specified()
    {
        $cart = app(Cart::class);
        $model = TestShoppable::first();
        $item = $cart->addItem(get_class($model), $model->id, null);

        $this->assertEquals(1, $cart->items()->first()->quantity);
    }

    /** @test */
    public function it_finds_identical_items_by_options()
    {
        $cart = app(Cart::class);
        $model = TestShoppable::first();

        $item = $cart->addItem(get_class($model), $model->id, 1, ['color' => 'Green', 'size' => 'Large']);
        $item = $cart->addItem(get_class($model), $model->id, 2, ['color' => 'Green', 'size' => 'Large']);
        $this->assertEquals(1, $cart->items()->count());
        $this->assertEquals(3, $cart->items()->first()->quantity);

        $item = $cart->addItem(get_class($model), $model->id, 1, ['color' => 'Red', 'size' => 'Large']);
        $this->assertEquals(2, $cart->items()->count());
    }

    /** @test */
    public function it_finds_identical_items_by_sub_items()
    {
        $cart = app(Cart::class);
        $model = TestShoppable::first();
        $item = $cart->addItem(get_class($model), $model->id, 1, [], [
            ['shoppable_type' => get_class($model), 'shoppable_id' => 1],
            ['shoppable_type' => get_class($model), 'shoppable_id' => 1, 'options' => ['color' => 'Green']],
        ]);
        $item = $cart->addItem(get_class($model), $model->id, 1, [], [
            ['shoppable_type' => get_class($model), 'shoppable_id' => 1],
            ['shoppable_type' => get_class($model), 'shoppable_id' => 1, 'options' => ['color' => 'Green']],
        ]);
        $this->assertEquals(1, $cart->items()->count());
        $this->assertEquals(2, $cart->items()->first()->quantity);

        $item = $cart->addItem(get_class($model), $model->id, 1, [], [
            ['shoppable_type' => get_class($model), 'shoppable_id' => 1],
            ['shoppable_type' => get_class($model), 'shoppable_id' => 1, 'options' => ['color' => 'Green', 'size' => 'Small']],
        ]);
        $this->assertEquals(2, $cart->items()->count());
    }

    /** @test */
    public function it_returns_404_if_shoppable_is_not_found()
    {
        $cart = app(Cart::class);
        $model = TestShoppable::first();

        try {
            $item = $cart->addItem(get_class($model), 2, null);
        } catch (ModelNotFoundException $e) {
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->fail('Wrong exception caught:'.$e->getMessage());
        }
    }

    /** @test */
    public function it_generates_an_id_hash()
    {
        $cart = app(Cart::class);
        $model = TestShoppable::first();
        $item = $cart->addItem(get_class($model), $model->id, null);

        $this->assertNotNull($cart->items()->first()->id);
    }

    /** @test */
    public function it_does_not_remove_discount_coupons()
    {
        $discount = factory(DiscountCoupon::class)->create();
        $cart = app(Cart::class);
        $model = TestShoppable::first();
        $item = $cart->addItem(get_class($model), $model->id, null);

        $cart->addDiscount($discount);

        $cart->addItem(get_class($model), $model->id, 2);

        $this->assertTrue($cart->hasDiscount($discount->code));
        $this->assertEquals(3, $cart->items()->first()->quantity);
    }

    /** @test */
    public function it_returns_the_cart_item()
    {
        $cart = app(Cart::class);
        $model = TestShoppable::first();
        $item = $cart->addItem(get_class($model), $model->id, null);

        $this->assertTrue($item instanceof CartItem);
    }

    /** @test */
    public function it_fires_the_correct_event()
    {
        $cart = app(Cart::class);

        Event::fake();

        $model = TestShoppable::first();
        $item = $cart->addItem(get_class($model), $model->id, null);

        // The first time the added event is fired.
        Event::assertDispatched('shopr.cart.items.added', function ($event, $data) use ($item) {
            return serialize($item) === serialize($data);
        });

        $item = $cart->addItem(get_class($model), $model->id, null);

        // The second time the updated event is fired.
        Event::assertDispatched('shopr.cart.items.updated', function ($event, $data) use ($item) {
            return $item->id === $data->id && $data->quantity === 2;
        });
    }
}
