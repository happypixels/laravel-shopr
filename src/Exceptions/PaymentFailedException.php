<?php

namespace Happypixels\Shopr\Exceptions;

use Exception;

class PaymentFailedException extends Exception
{
    protected $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        $response = trans('shopr::checkout.payment_failed', [
            'message' => $this->message,
        ]);

        return response()->json(['message' => $response], 400);
    }
}
