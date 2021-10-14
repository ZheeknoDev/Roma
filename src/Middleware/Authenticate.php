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

use Zheeknodev\Roma\Auth;
use Zheeknodev\Roma\Middleware\InterfaceMiddleware;
use Zheeknodev\Roma\Router\Response;

class Authenticate implements InterfaceMiddleware
{
    public function handle($request, callable $next)
    {
        # validate the API's token.
        if (Auth::via('default')->verifyApiToken('bearer')) {
            return $next($request);
        }
        return Response::instance()->redirect('/404');
    }
}
