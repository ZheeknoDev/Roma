<?php

/**
 * AUTHENTICATE MIDDLEWARE
 * the filtering the request that gets authorized
 * @category Class
 * @package  zheeknodev/roma
 * @author   ZheeknoDev <million8.me@gmail.com>
 * @license  https://opensource.org/licenses/MIT - MIT License 
 * @link     https://github.com/ZheeknoDev/Roma
 */

namespace Zheeknodev\Roma\Middleware;

use Zheeknodev\Roma\BasicAuth;
use Zheeknodev\Roma\Middleware\InterfaceMiddleware;
use Zheeknodev\Roma\Router\Response;

class RequestAuth implements InterfaceMiddleware
{
    public function handle($request, callable $next)
    {
        # validate the API's token.
        if (BasicAuth::instance()->hasAuthorized('bearer')) {
            return $next($request);
        }
        return Response::instance()->redirect('/404');
    }
}
