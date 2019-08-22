<?php

namespace Happypixels\Shopr\Exceptions;

use Exception;

class DiscountValidationException extends Exception
{
    /**
     * The message of the exception.
     *
     * @var string
     */
    protected $message;

    /**
     * The status code of the exception.
     *
     * @var int
     */
    protected $code = 422;

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
        return response()->json(['message' => $this->message], $this->code);
    }
}
