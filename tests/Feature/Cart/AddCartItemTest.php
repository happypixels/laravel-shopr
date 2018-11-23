<?php

namespace Happypixels\Shopr\Tests\Feature\Cart;

use Happypixels\Shopr\Contracts\Cart;
use Happypixels\Shopr\Models\DiscountCoupon;
use Happypixels\Shopr\Tests\TestCase;

class AddCartItemTest extends TestCase
{
    /** @test */
    public function it_validates_the_post_data()
    {
        $this->json('POST', 'api/shopr/cart/items')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['shoppable_type', 'shoppable_id']);
    }

    /** @test */
    public function discounts_are_not_allowed()
    {
        $discount = factory(DiscountCoupon::class)->create();

        $this->json('POST', 'api/shopr/cart/items', [
            'shoppable_type' => get_class($discount),
            'shoppable_id'   => $discount->id,
            'quantity'       => 1
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['shoppable_type'])
        ->assertJsonFragment(['Invalid shoppable.']);
    }

    /** @test */
    public function it_throws_404_error_if_shoppable_is_not_found()
    {
        $this->json('POST', 'api/shopr/cart/items', [
                'shoppable_id'   => 2,
                'shoppable_type' => 'Happypixels\Shopr\Tests\Support\Models\TestShoppable'
            ])
            ->assertStatus(404);
    }

    /** @test */
    public function it_adds_an_item()
    {
        $cart = app(Cart::class);

        $this->assertEquals(0, $cart->count());

        $this->json('POST', 'api/shopr/cart/items', [
            'shoppable_type' => 'Happypixels\Shopr\Tests\Support\Models\TestShoppable',
            'shoppable_id'   => 1,
            'quantity'       => 1
        ])->assertStatus(200);

        $this->assertEquals(1, $cart->count());
    }

    /** @test */
    public function adding_an_item_returns_cart_summary()
    {
        $cart = app(Cart::class);

        $this->json('POST', 'api/shopr/cart/items', [
            'shoppable_type' => 'Happypixels\Shopr\Tests\Support\Models\TestShoppable',
            'shoppable_id'   => 1,
            'quantity'       => 2
        ])->assertJsonFragment([
            'count'     => $cart->count(),
            'total'     => $cart->total(),
            'sub_total' => $cart->subTotal(),
            'tax_total' => $cart->taxTotal(),
        ])->assertJsonStructure(['count', 'total', 'sub_total', 'tax_total', 'items']);
    }

    /** @test */
    public function it_adds_provided_quantity()
    {
        $cart = app(Cart::class);

        $this->assertEquals(0, $cart->count());

        $this->json('POST', 'api/shopr/cart/items', [
            'shoppable_type' => 'Happypixels\Shopr\Tests\Support\Models\TestShoppable',
            'shoppable_id'   => 1,
            'quantity'       => 3
        ]);

        $this->assertEquals(3, $cart->count());
    }

    /** @test */
    public function it_defaults_to_1_if_no_quantity_is_given()
    {
        $cart = app(Cart::class);

        $this->assertEquals(0, $cart->count());

        $this->json('POST', 'api/shopr/cart/items', [
            'shoppable_type' => 'Happypixels\Shopr\Tests\Support\Models\TestShoppable',
            'shoppable_id'   => 1,
        ]);

        $this->assertEquals(1, $cart->count());
    }

    /** @test */
    public function it_allows_price_overrides()
    {
        $cart = app(Cart::class);

        $this->json('POST', 'api/shopr/cart/items', [
            'shoppable_type' => 'Happypixels\Shopr\Tests\Support\Models\TestShoppable',
            'shoppable_id'   => 1,
            'price'          => 2018.50,
        ]);

        $items = $cart->items();

        $this->assertEquals(2018.50, $items->first()->price);
        $this->assertEquals(2018.50, $cart->total());
    }

    /** @test */
    public function it_adds_subitems()
    {
        $cart = app(Cart::class);

        $data = [
            'shoppable_type' => 'Happypixels\Shopr\Tests\Support\Models\TestShoppable',
            'shoppable_id'   => 1,
            'quantity'       => 2,
            'price'          => 50,
        ];

        // Add an identical sub item.
        $data['sub_items'] = [$data];

        $response = $this->json('POST', 'api/shopr/cart/items', $data)->assertStatus(200);

        $subItems = $cart->items()->first()->subItems;
        $this->assertEquals(1, $subItems->count());
        $this->assertEquals(50, $subItems->first()->price);
    }
}
