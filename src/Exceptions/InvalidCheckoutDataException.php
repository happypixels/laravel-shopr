<?php

namespace Happypixels\Shopr\Exceptions;

use Exception;

class InvalidCheckoutDataException extends Exception
{
    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        return response()->json(['message' => trans('shopr::checkout.invalid_data')], 422);
    }
}
