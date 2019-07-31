<?php

namespace Happypixels\Shopr\Tests\Feature\Cart;

use Happypixels\Shopr\Facades\Cart;
use Happypixels\Shopr\Tests\TestCase;
use Happypixels\Shopr\Models\DiscountCoupon;

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
            'quantity'       => 1,
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
            'shoppable_type' => 'Happypixels\Shopr\Tests\Support\Models\TestShoppable',
        ])
        ->assertStatus(404);
    }

    /** @test */
    public function it_adds_an_item()
    {
        $this->assertEquals(0, Cart::count());

        $this->json('POST', 'api/shopr/cart/items', [
            'shoppable_type' => 'Happypixels\Shopr\Tests\Support\Models\TestShoppable',
            'shoppable_id' => 1,
            'quantity' => 1,
        ])->assertStatus(200);

        $this->assertEquals(1, Cart::count());
    }

    /** @test */
    public function adding_an_item_returns_cart_summary()
    {
        $this->json('POST', 'api/shopr/cart/items', [
            'shoppable_type' => 'Happypixels\Shopr\Tests\Support\Models\TestShoppable',
            'shoppable_id' => 1,
            'quantity' => 2,
        ])->assertJsonFragment([
            'count' => Cart::count(),
            'total' => Cart::total(),
            'sub_total' => Cart::subTotal(),
            'tax_total' => Cart::taxTotal(),
        ])->assertJsonStructure(['count', 'total', 'sub_total', 'tax_total', 'items']);
    }

    /** @test */
    public function it_adds_provided_quantity()
    {
        $this->assertEquals(0, Cart::count());

        $this->json('POST', 'api/shopr/cart/items', [
            'shoppable_type' => 'Happypixels\Shopr\Tests\Support\Models\TestShoppable',
            'shoppable_id' => 1,
            'quantity' => 3,
        ]);

        $this->assertEquals(3, Cart::count());
    }

    /** @test */
    public function it_defaults_to_1_if_no_quantity_is_given()
    {
        $this->assertEquals(0, Cart::count());

        $this->json('POST', 'api/shopr/cart/items', [
            'shoppable_type' => 'Happypixels\Shopr\Tests\Support\Models\TestShoppable',
            'shoppable_id' => 1,
        ]);

        $this->assertEquals(1, Cart::count());
    }

    /** @test */
    public function it_allows_price_overrides()
    {
        $this->json('POST', 'api/shopr/cart/items', [
            'shoppable_type' => 'Happypixels\Shopr\Tests\Support\Models\TestShoppable',
            'shoppable_id' => 1,
            'price' => 2018.50,
        ]);

        $this->assertEquals(2018.50, Cart::items()->first()->price);
        $this->assertEquals(2018.50, Cart::total());
    }

    /** @test */
    public function it_adds_subitems()
    {
        $data = [
            'shoppable_type' => 'Happypixels\Shopr\Tests\Support\Models\TestShoppable',
            'shoppable_id' => 1,
            'sub_items' => [
                [
                    'shoppable_type' => 'Happypixels\Shopr\Tests\Support\Models\TestShoppable',
                    'shoppable_id' => 1,
                    'price' => 50,
                ],
            ],
        ];

        $this->json('POST', 'api/shopr/cart/items', $data)->assertStatus(200);

        $subItems = Cart::items()->first()->subItems;
        $this->assertEquals(1, $subItems->count());
        $this->assertEquals(50, $subItems->first()->price);
    }

    /** @test */
    public function it_takes_options()
    {
        $this->json('POST', 'api/shopr/cart/items', [
            'shoppable_type' => 'Happypixels\Shopr\Tests\Support\Models\TestShoppable',
            'shoppable_id' => 1,
            'options' => ['color' => 'Green'],
        ]);

        $this->assertEquals(['color' => 'Green'], Cart::items()->first()->options);
    }
}
