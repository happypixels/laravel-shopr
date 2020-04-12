<?php

namespace Happypixels\Shopr\Exceptions;

use Exception;

class CartItemNotFoundException extends Exception
{
    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        return response()->json(['message' => trans('shopr::cart.item_not_found')], 404);
    }
}
