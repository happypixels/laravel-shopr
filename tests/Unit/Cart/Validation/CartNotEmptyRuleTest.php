<?php

namespace Happypixels\Shopr\Tests\Unit\Cart\Validation;

use Happypixels\Shopr\Contracts\Cart;
use Happypixels\Shopr\Rules\Cart\CartNotEmpty;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;
use Happypixels\Shopr\Tests\TestCase;

class CartNotEmptyRuleTest extends TestCase
{
    /** @test */
    public function it_fails_if_the_cart_is_empty()
    {
        $this->assertFalse((new CartNotEmpty)->passes('', ''));
    }

    /** @test */
    public function it_passes_if_the_cart_has_items()
    {
        $cart  = app(Cart::class);
        $model = TestShoppable::first();
        $cart->addItem(get_class($model), $model->id, 1);

        $this->assertTrue((new CartNotEmpty)->passes('', ''));
    }
}
