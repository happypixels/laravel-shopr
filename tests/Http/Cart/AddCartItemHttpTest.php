<?php

namespace Happypixels\Shopr\Tests\Http\Cart;

use Happypixels\Shopr\Facades\Cart;
use Happypixels\Shopr\Models\DiscountCoupon;
use Happypixels\Shopr\Tests\TestCase;

class AddCartItemHttpTest extends TestCase
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
            'shoppable_id' => $discount->id,
            'quantity' => 1,
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['shoppable_type'])
        ->assertJsonFragment(['Invalid shoppable.']);
    }

    /** @test */
    public function it_throws_404_error_if_shoppable_is_not_found()
    {
        $this->json('POST', 'api/shopr/cart/items', [
            'shoppable_id' => 2,
            'shoppable_type' => 'Happypixels\Shopr\Tests\Support\Models\TestShoppable',
        ])->assertStatus(404);
    }

    /** @test */
    public function it_adds_an_item()
    {
        $this->withoutExceptionHandling();

        $this->assertEquals(0, Cart::count());

        $this->json('POST', 'api/shopr/cart/items', [
            'shoppable_type' => 'Happypixels\Shopr\Tests\Support\Models\TestShoppable',
            'shoppable_id' => 1,
            'quantity' => 2,
            'options' => ['size' => 'L'],
            'sub_items' => [
                [
                    'shoppable_type' => 'Happypixels\Shopr\Tests\Support\Models\TestShoppable',
                    'shoppable_id' => '1',
                ],
            ],
            'price' => 50,
        ])->assertStatus(200);

        $this->assertEquals(2, Cart::count());

        $item = Cart::first();
        $this->assertEquals(['size' => 'L'], $item->options);
        $this->assertEquals(550, $item->price);
        $this->assertEquals(1, $item->sub_items->count());
    }

    /** @test */
    public function it_uses_defaults()
    {
        $this->withoutExceptionHandling();

        $this->json('POST', 'api/shopr/cart/items', [
            'shoppable_type' => 'Happypixels\Shopr\Tests\Support\Models\TestShoppable',
            'shoppable_id' => 1,
        ])->assertStatus(200);

        $this->assertEquals(1, Cart::count());

        $item = Cart::first();
        $this->assertNull($item->options);
        $this->assertEquals(500, $item->price);
        $this->assertEquals(0, $item->sub_items->count());
    }

    /** @test */
    public function adding_an_item_returns_cart_summary()
    {
        $this->withoutExceptionHandling();

        $this->json('POST', 'api/shopr/cart/items', [
            'shoppable_type' => 'Happypixels\Shopr\Tests\Support\Models\TestShoppable',
            'shoppable_id' => 1,
            'quantity' => 2,
        ])->assertJsonStructure(array_keys(Cart::get()));
    }
}
