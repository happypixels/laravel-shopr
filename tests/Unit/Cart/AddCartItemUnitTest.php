<?php

namespace Happypixels\Shopr\Tests\Unit\Cart;

use Happypixels\Shopr\Facades\Cart;
use Happypixels\Shopr\Cart\CartItem;
use Happypixels\Shopr\Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Happypixels\Shopr\Models\DiscountCoupon;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;

class AddCartItemUnitTest extends TestCase
{
    /** @test */
    public function it_adds_the_item_with_1_as_default_quantity()
    {
        $model = TestShoppable::first();

        Cart::add($model)->save();

        $this->assertEquals(1, Cart::count());
        $this->assertEquals(get_class($model), get_class(Cart::items()->first()->shoppable));
        $this->assertEquals($model->id, Cart::items()->first()->shoppable->id);
        $this->assertEquals(1, Cart::count());
        $this->assertEquals($model, Cart::items()->first()->shoppable);
    }

    /** @test */
    public function it_accepts_sub_items()
    {
        Cart::add(TestShoppable::first())->subItems([
            ['shoppable' => TestShoppable::first(), 'price' => 123],
            ['shoppable' => TestShoppable::first(), 'options' => ['color' => 'Green']],
        ])->save();

        $this->assertEquals(1, Cart::count());

        $item = Cart::items()->first();
        $this->assertEquals(2, $item->subItems->count());
        $this->assertEquals(123, $item->subItems->first()->price);
        $this->assertEquals(['color' => 'Green'], $item->subItems->last()->options);
    }

    /** @test */
    public function sub_items_automatically_get_the_parent_quantity()
    {
        Cart::add(TestShoppable::first())->quantity(3)->subItems([
            ['shoppable' => TestShoppable::first()],
        ])->save();

        $this->assertEquals(3, Cart::items()->first()->subItems->first()->quantity);
    }

    /** @test */
    public function it_accepts_options()
    {
        Cart::add(TestShoppable::first())->options(['size' => 'Large'])->save();

        $this->assertEquals(['size' => 'Large'], Cart::items()->first()->options);
    }

    /** @test */
    public function it_accepts_price_override()
    {
        Cart::add(TestShoppable::first())->overridePrice(123123)->save();

        $this->assertEquals(123123, Cart::items()->first()->price);
        $this->assertEquals('$123,123.00', Cart::items()->first()->price_formatted);
    }

    /** @test */
    public function it_finds_identical_items_by_options()
    {
        Cart::add(TestShoppable::first())->options(['color' => 'Green', 'size' => 'L'])->save();
        Cart::add(TestShoppable::first())->quantity(2)->options(['color' => 'Green', 'size' => 'L'])->save();
        Cart::add(TestShoppable::first())->quantity(3)->options(['color' => 'Green', 'size' => 'S'])->save();

        $this->assertEquals(6, Cart::count());
        $this->assertEquals(2, Cart::items()->count());
        $this->assertEquals(3, Cart::items()->first()->quantity);
        $this->assertEquals(3, Cart::items()->last()->quantity);
    }

    /** @test */
    public function it_finds_identical_items_by_sub_items()
    {
        Cart::add(TestShoppable::first())->subItems([
            ['shoppable' => TestShoppable::first()],
            ['shoppable' => TestShoppable::first(), 'options' => ['color' => 'Green']],
        ])->save();
        Cart::add(TestShoppable::first())->subItems([
            ['shoppable' => TestShoppable::first()],
            ['shoppable' => TestShoppable::first(), 'options' => ['color' => 'Green']],
        ])->save();
        $this->assertEquals(1, Cart::items()->count());
        $this->assertEquals(2, Cart::count());

        Cart::add(TestShoppable::first())->subItems([
            ['shoppable' => TestShoppable::first()],
            ['shoppable' => TestShoppable::first(), 'options' => ['color' => 'Green', 'size' => 'L']],
        ])->save();
        $this->assertEquals(2, Cart::items()->count());
    }

    /** @test */
    public function it_generates_an_id_hash()
    {
        Cart::add(TestShoppable::first())->save();

        $this->assertNotNull(Cart::items()->first()->id);
        $this->assertTrue(is_string(Cart::items()->first()->id));
    }

    /** @test */
    public function it_does_not_remove_discount_coupons()
    {
        Cart::add(TestShoppable::first())->save();
        Cart::addDiscount($discount = factory(DiscountCoupon::class)->create());
        Cart::add(TestShoppable::first())->quantity(2)->save();

        $this->assertTrue(Cart::hasDiscount($discount->code));
        $this->assertEquals(3, Cart::items()->first()->quantity);
    }

    /** @test */
    public function it_returns_the_cart_item()
    {
        $item = Cart::add(TestShoppable::first())->save();

        $this->assertTrue($item instanceof CartItem);
    }

    /** @test */
    public function it_fires_the_correct_event()
    {
        Event::fake();

        $item = Cart::add(TestShoppable::first())->save();

        // The first time the added event is fired.
        Event::assertDispatched('shopr.cart.items.added', function ($event, $data) use ($item) {
            return serialize($item) === serialize($data);
        });

        $item = Cart::add(TestShoppable::first())->save();

        // The second time the updated event is fired.
        Event::assertDispatched('shopr.cart.items.updated', function ($event, $data) use ($item) {
            return $item->id === $data->id && $data->quantity === 2;
        });

        $item = Cart::add(TestShoppable::first())->options(['color' => 'Green'])->save();

        // A unique item triggers the added event again.
        Event::assertDispatched('shopr.cart.items.added', function ($event, $data) use ($item) {
            return serialize($item) === serialize($data);
        });
    }
}
