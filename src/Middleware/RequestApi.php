<?php

/**
 * AUTHENTICATE MIDDLEWARE
 * the filtering the request that from the API
 * @category Class
 * @package  zheeknodev/roma
 * @author   ZheeknoDev <million8.me@gmail.com>
 * @license  https://opensource.org/licenses/MIT - MIT License 
 * @link     https://github.com/ZheeknoDev/Roma
 */

namespace Zheeknodev\Roma\Middleware;

use Zheeknodev\Roma\Middleware\InterfaceMiddleware;
use Zheeknodev\Roma\Router\Response;

class RequestApi implements InterfaceMiddleware
{
    public function handle($request, callable $next)
    {
        # if the request that from API and JSON object.
        if ($request->viaRequest('api', 'application/json')) {
            return $next($request);
        }
        return Response::instance()->redirect('/404');
    }
}