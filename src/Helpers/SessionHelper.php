<?php

namespace Happypixels\Shopr\Helpers;

use Illuminate\Support\Facades\Session;

class SessionHelper
{
    public function get($key)
    {
        return unserialize(Session::get($key));
    }

    public function put($key, $data)
    {
        return Session::put($key, serialize($data));
    }
}
