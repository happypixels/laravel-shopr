<?php

namespace Happypixels\Shopr\Tests\Feature\Cart;

use Happypixels\Shopr\Cart\CartItem;
use Happypixels\Shopr\Facades\Cart;
use Happypixels\Shopr\Models\DiscountCoupon;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;
use Happypixels\Shopr\Tests\TestCase;
use Illuminate\Support\Facades\Event;

class AddCartItemTest extends TestCase
{
    /** @test */
    public function it_adds_the_item_with_1_as_default_quantity()
    {
        $model = TestShoppable::first();

        Cart::add($model);

        $item = Cart::items()->first();
        $this->assertEquals(1, Cart::count());
        $this->assertTrue($item->shoppable->is($model));
        $this->assertEquals($model->getPrice(), $item->price);
        $this->assertEquals(1, $item->quantity);
        $this->assertEquals(collect(), $item->sub_items);
        $this->assertNull($item->options);
    }

    /** @test */
    public function it_accepts_quantity()
    {
        $model = TestShoppable::first();

        Cart::add($model, ['quantity' => 3]);

        $this->assertEquals(3, Cart::count());
        $this->assertEquals(1, Cart::items()->count());
        $this->assertTrue(Cart::items()->first()->shoppable->is($model));
    }

    /** @test */
    public function it_accepts_options()
    {
        Cart::add(TestShoppable::first(), ['options' => ['size' => 'Large']]);

        $this->assertEquals(['size' => 'Large'], Cart::items()->first()->options);
    }

    /** @test */
    public function it_accepts_price_override()
    {
        Cart::add(TestShoppable::first(), ['price' => 123123]);

        $this->assertEquals(123123, Cart::items()->first()->price);
        $this->assertEquals('$123,123.00', Cart::items()->first()->price_formatted);
    }

    /** @test */
    public function it_defaults_to_the_shoppable_price_if_not_overridden()
    {
        Cart::add($model = TestShoppable::first());

        $this->assertEquals(500, $model->getPrice());
        $this->assertEquals($model->getPrice(), Cart::items()->first()->price);
    }

    /** @test */
    public function it_accepts_sub_items()
    {
        Cart::add(TestShoppable::first(), [
            'sub_items' => [
                ['shoppable' => TestShoppable::first(), 'price' => 123],
                ['shoppable' => TestShoppable::first(), 'options' => ['color' => 'Green']],
            ],
        ]);

        $this->assertEquals(1, Cart::count());

        $subItems = Cart::items()->first()->sub_items;
        $this->assertEquals(2, $subItems->count());
        $this->assertTrue($subItems->first() instanceof CartItem);
        $this->assertEquals(123, $subItems->first()->price);
        $this->assertEquals(['color' => 'Green'], $subItems->last()->options);
        $this->assertEquals(500, $subItems->last()->price);
        $this->assertEquals('$500.00', $subItems->last()->price_formatted);
    }

    /** @test */
    public function sub_items_automatically_get_the_parent_quantity()
    {
        Cart::add(TestShoppable::first(), [
            'quantity' => 3,
            'sub_items' => [
                ['shoppable' => TestShoppable::first()],
            ],
        ]);

        $this->assertEquals(3, Cart::items()->first()->sub_items->first()->quantity);
    }

    /** @test */
    public function it_finds_identical_items_by_options()
    {
        Cart::add(TestShoppable::first(), ['options' => ['color' => 'Green', 'size' => 'L']]);
        Cart::add(TestShoppable::first(), ['options' => ['color' => 'Green', 'size' => 'L'], 'quantity' => 2]);
        Cart::add(TestShoppable::first(), ['options' => ['color' => 'Green', 'size' => 'S']]);

        $this->assertEquals(4, Cart::count());
        $this->assertEquals(2, Cart::items()->count());
        $this->assertEquals(3, Cart::first()->quantity);
        $this->assertEquals(1, Cart::last()->quantity);
    }

    /** @test */
    public function it_finds_identical_items_by_sub_items()
    {
        Cart::add(TestShoppable::first(), [
            'sub_items' => [
                ['shoppable' => TestShoppable::first()],
                ['shoppable' => TestShoppable::first(), 'options' => ['color' => 'Green']],
            ],
        ]);
        Cart::add(TestShoppable::first(), [
            'sub_items' => [
                ['shoppable' => TestShoppable::first()],
                ['shoppable' => TestShoppable::first(), 'options' => ['color' => 'Green']],
            ],
        ]);
        $this->assertEquals(1, Cart::items()->count());
        $this->assertEquals(2, Cart::count());

        Cart::add(TestShoppable::first(), [
            'sub_items' => [
                ['shoppable' => TestShoppable::first()],
                ['shoppable' => TestShoppable::first(), 'options' => ['color' => 'Green', 'size' => 'L']],
            ],
        ]);
        $this->assertEquals(2, Cart::items()->count());
        $this->assertEquals(3, Cart::count());
    }

    /** @test */
    public function item_total_price_reflects_quantity_and_sub_items()
    {
        $item = Cart::add(TestShoppable::first(), [
            'quantity' => 2,
            'sub_items' => [
                ['shoppable' => TestShoppable::first(), 'price' => 50],
                ['shoppable' => TestShoppable::first()],
            ],
        ]);

        // The price attribute is per unit.
        $this->assertEquals(1050, $item->price);
        $this->assertEquals('$1,050.00', $item->price_formatted);

        // (500 + 50 + 500) * 2.
        $this->assertEquals(2100, $item->total_price);
        $this->assertEquals('$2,100.00', $item->total_price_formatted);
    }

    /** @test */
    public function it_generates_an_id_hash()
    {
        Cart::add(TestShoppable::first());

        $this->assertNotNull(Cart::first()->id);
        $this->assertTrue(is_string(Cart::first()->id));
    }

    /** @test */
    public function it_returns_the_cart_item()
    {
        $item = Cart::add(TestShoppable::first());

        $this->assertTrue($item instanceof CartItem);
    }

    /** @test */
    public function it_updates_non_fixed_discount_percentage_values()
    {
        // Create a discount coupon for 50%.
        $discount = factory(DiscountCoupon::class)->create(['value' => 50, 'is_fixed' => 0]);

        Cart::add(TestShoppable::first());
        Cart::addDiscount($discount);

        $this->assertEquals(250, Cart::total());
        $this->assertEquals(500, Cart::totalWithoutDiscounts());

        Cart::add(TestShoppable::first());

        $this->assertEquals(2, Cart::count());
        $this->assertEquals(500, Cart::total());
        $this->assertEquals(1000, Cart::totalWithoutDiscounts());
    }

    /** @test */
    public function it_fires_the_correct_event()
    {
        Event::fake();

        $item = Cart::add(TestShoppable::first());

        // The first time the added event is fired.
        Event::assertDispatched('shopr.cart.items.added', function ($event, $data) use ($item) {
            return $data->is($item);
        });

        $item = Cart::add(TestShoppable::first());

        // The second time the updated event is fired.
        Event::assertDispatched('shopr.cart.items.updated', function ($event, $data) use ($item) {
            return $data->is($item) && $data->quantity === 2;
        });

        $item = Cart::add(TestShoppable::first(), [
            'options' => ['color' => 'Green'],
        ]);

        // A unique item triggers the added event again.
        Event::assertDispatched('shopr.cart.items.added', function ($event, $data) use ($item) {
            return $data->is($item);
        });
    }
}
