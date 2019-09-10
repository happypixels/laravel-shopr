<?php

namespace Happypixels\Shopr\Exceptions;

use Exception;

class PaymentFailedException extends Exception
{
    /**
     * The message, typically the reason why the payment has failed.
     *
     * @var string
     */
    protected $message;

    /**
     * Create an instance of the exception.
     *
     * @param string $message
     */
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
        return response()->json([
            'message' => trans('shopr::checkout.payment_failed'),
            'reason' => $this->message,
        ], 400);
    }
}
