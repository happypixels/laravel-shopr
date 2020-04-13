<?php

namespace Happypixels\Shopr;

use Happypixels\Shopr\Middleware\EncryptCookies;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Session\Middleware\StartSession;

class Shopr
{
    /**
     * Returns the middleware enabled for the REST API.
     *
     * @return array
     */
    public static function getApiMiddleware()
    {
        $middleware = [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
        ];

        $customMiddleware = config('shopr.rest_api.middleware');

        if (! empty($customMiddleware) && is_array($customMiddleware)) {
            $middleware = array_merge($middleware, $customMiddleware);
        }

        return $middleware;
    }

    /**
     * Returns the prefix of the REST API.
     *
     * @return string
     */
    public static function getApiPrefix()
    {
        return config('shopr.rest_api.prefix') ?: 'api/shopr';
    }

    /**
     * Returns the configured tax mode.
     *
     * @return string
     */
    public static function getTaxMode()
    {
        return config('shopr.tax_mode') ?: 'gross';
    }

    /**
     * Returns true if the REST API hasn't been disabled.
     *
     * @return bool
     */
    public static function restApiEnabled()
    {
        return config('shopr.rest_api.enabled') !== false;
    }
}
