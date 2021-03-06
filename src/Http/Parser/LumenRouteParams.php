<?php

/*
 * This file is part of jwt-auth.
 *
 * (c) Anla sheng <anlasheng@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Anla\JWTAuth\Http\Parser;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;

class LumenRouteParams extends RouteParams
{
    /**
     * Try to get the token from the route parameters.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return null|string
     */
    public function parse(Request $request)
    {
        // WARNING: Only use this parser if you know what you're doing!
        // It will only work with poorly-specified aspects of certain Lumen releases.
        $route = $request->route();

        if (is_array($route) && $token = Arr::get($route, '2.'.$this->key)) {
            // Route is the expected kind of array, and has a parameter with the key we want.
            return $token;
        }
    }
}
