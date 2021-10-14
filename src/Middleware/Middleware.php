<?php

/**
 * @category Class
 * @package  zheeknodev/roma
 * @author   ZheeknoDev <million8.me@gmail.com>
 * @license  https://opensource.org/licenses/MIT - MIT License 
 * @link     https://github.com/ZheeknoDev/Roma
 */

namespace Zheeknodev\Roma\Middleware;

use Zheeknodev\Roma\Middleware\InterfaceMiddleware;
use Zheeknodev\Roma\Router\Request;

final class Middleware
{
    private $callable;
    private $collect = [];
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->callable = function ($request) {
            return $request;
        };
    }

    final public function __debugInfo()
    {
        return;
    }


    /**
     * @param string $name
     * @param object $middleware
     * @return void
     */
    final public function add($name, InterfaceMiddleware $middleware): void
    {
        $collect = $this->callable;
        $this->collect[$name] = function ($request) use ($middleware, $collect) {
            return $middleware->handle($request, $collect);
        };
    }

    /**
     * @param string $middleware
     * @return void
     */
    final public function call(string $middleware): void
    {
        if (!empty($this->collect[$middleware]) && is_callable($this->collect[$middleware])) {
            $callable = call_user_func($this->collect[$middleware], $this->request);
            if($callable !== $this->request) {
                exit();
            }
        }
    }

    /**
     * @param array $middleware
     * @return void
     */
    final public function register(array $middleware = array()): void
    {
        if (!empty($middleware)) {
            foreach ($middleware as $name => $class) {
                $this->add($name, new $class);
            }
        }
    }
}
