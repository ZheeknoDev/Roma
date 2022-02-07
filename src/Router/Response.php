<?php

/**
 * @category Class
 * @package  Roma/Router
 * @author   ZheeknoDev <million8.me@gmail.com>
 * @license  https://opensource.org/licenses/MIT - MIT License 
 * @link     https://github.com/ZheeknoDev/Roma
 */

namespace Zheeknodev\Roma\Router;

use Zheeknodev\Roma\Router\Request;

final class Response
{
    private const STATUS_CODES = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported'
    ];

    private $request;

    public function __construct(Request $request = null)
    {
        $this->request = $request;
    }

    final public function __debugInfo()
    {
        return;
    }

    /**
     * Return error respond.
     * @param array $object
     * @param integer $code
     * @return void
     */
    final public function fail(array $object = [], int $code)
    {
        return $this->respond([
            'status' => false,
            'response' => (empty($object) ? ['error' => $this->getResponseMessage($code)] : $object),
            'error' => $code,
        ], $code);
    }

    /**
     * Return error bad request.
     * @param array $object
     * @return void
     */
    final public function failBadRequest(array $object = [])
    {
        return $this->fail($object, 400);
    }

    /**
     * Return error unauthorized.
     * @param array $object
     * @return void
     */
    final public function failUnauthorized(array $object = [])
    {
        return $this->fail($object, 401);
    }

    /**
     * Return error forbidden.
     * @param array $object
     * @return void
     */
    final public function failForbidden(array $object = [])
    {
        return $this->fail($object, 403);
    }

    /**
     * Return error not found
     * @param array $object
     * @return void
     */
    final public function failNotFound(array $object = [])
    {
        return $this->fail($object, 404);
    }

    /**
     * Retirn the methode is not allow.
     * @param array $object
     * @return void
     */
    final public function failMethodNotAllow(array $object = [])
    {
        return $this->fail($object, 405);
    }

    public static function instance()
    {
        return new self(new Request);
    }

    /**
     * Return the response message from HTTP response code.
     * @param int $http_response_code
     * @return string
     */
    final public function getResponseMessage(int $http_response_code = null): string
    {
        $code = (!empty($http_response_code)) ? $http_response_code : http_response_code();
        return (!empty(self::STATUS_CODES[$code])) ? (string) self::STATUS_CODES[$code] : null;
    }

    /**
     * Redirect the path
     * @param string $path
     */
    final public function redirect(string $path = null)
    {
        $request_uri = empty($path) ? '/' : $path;
        $redirect_to = $this->request->pathinfo($request_uri);
        header("Location: {$redirect_to}");
        exit();
    }

    /**
     * Response the object to be a json object
     * @param array $object
     * @param int $http_response_code - default is 200 
     * @return void
     */
    final public function respond(array $object = [], int $http_response_code = 200)
    {
        // remove any string that could create an invalid JSON 
        if (ob_get_length() > 0) {
            ob_clean();
        }
        // this will clean up any previously added headers, to start clean
        header_remove();
        // Set the content type to JSON and charset 
        header("Content-type: application/json; charset=utf-8");
        // Set your HTTP response code, 2xx = SUCCESS
        http_response_code($http_response_code);
        // encode your PHP Object or Array into a JSON string.
        echo json_encode($object);

        exit();
    }
}
