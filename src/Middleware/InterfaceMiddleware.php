<?php

/**
 * @category Class
 * @package  zheeknodev/roma
 * @author   ZheeknoDev <million8.me@gmail.com>
 * @license  https://opensource.org/licenses/MIT - MIT License 
 * @link     https://github.com/ZheeknoDev/Roma
 */

namespace Zheeknodev\Roma\Middleware;

interface InterfaceMiddleware
{
    /**
     * @param request $request
     * @param callable $next
     * @return void
     */
    public function handle($request, callable $next);
}
