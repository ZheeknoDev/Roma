<?php

/**
 * @category Class
 * @package  Roma/Router
 * @author   ZheeknoDev <million8.me@gmail.com>
 * @license  https://opensource.org/licenses/MIT - MIT License 
 * @link     https://github.com/ZheeknoDev/Roma
 */

namespace Zheeknodev\Roma\Router;

final class Request
{
    private const HASH_KEY = 'a2nnGI0yM0S5NG9N9riw5U4reiLFTuJae8R7oL478Cp8SNT01nqQK1oc6McGW05P';
    private $requestAuthToken;

    public static $_hash_key;

    public function __construct()
    {
        foreach ($_SERVER as $key => $value) {
            $this->{$this->strCamelCase($key, '_[a-z]')} = $value;
        }
    }

    final public function __debugInfo()
    {
        return;
    }

    /**
     * Return list_params of body to be the object
     * For method: GET, POST, PUT, PATH, DELETE
     * @return object
     */
    final public function body()
    {
        return (json_decode(file_get_contents('php://input')));
    }

    /**
     * Generate the csrf token
     * Return the hidden input
     * @return string
     */
    final public static function csrf()
    {
        $hash_hmac_key = (!empty(self::$_hash_key) ? self::$_hash_key : self::HASH_KEY);
        $csrfToken = base64_encode(hash_hmac('sha3-384', session_id(), self::HASH_KEY));
        $inputHidden = "<input type=\"hidden\" name=\"_csrf_token\" value=\"{$csrfToken}\" />";
        return $inputHidden;
    }

    /**
     * Get the request from apache_request_headers()
     * @return object
     */
    private function getApacheRequest(string $requestName = null)
    {
        $apacheRequest = apache_request_headers();
        $requestApache = [];
        foreach ($apacheRequest as $key => $value) {
            $key = $this->strCamelCase($key, '-[a-z]');
            $requestApache[$key] = $value;
        }
        return (!empty($requestName) ? $requestApache[$requestName] : (object) $requestApache);
    }

    /**
     * Return the Api's token
     * @return string
     */
    final public function getAuthorizedToken()
    {
        return $this->requestAuthToken;
    }

    /**
     * Validate the type of header accept
     * @param string $accept
     * @return bool
     */
    final public function hasAccept(string $accept)
    {
        return ($this->httpAccept == strtolower($accept));
    }

    /**
     * Validate the header authorization
     * @param string $typeOfAuth
     * @return bool
     * 
     */
    final public function hasAuthorized(string $typeOfAuth)
    {
        $typeOfAuth = ucwords($typeOfAuth);
        $httpAuthorization = null;
        if (!empty($this->httpAuthorization)) {
            $httpAuthorization = $this->httpAuthorization;
        } elseif (!empty($this->authorization)) {
            $httpAuthorization = $this->authorization;
        } elseif (!empty($this->getApacheRequest('authorization'))) {
            $httpAuthorization = $this->getApacheRequest('authorization');
        }

        if (!empty($httpAuthorization) && (strpos($httpAuthorization, $typeOfAuth) !== false)) {
            $authToken = trim(str_replace($typeOfAuth, '', $httpAuthorization));
            if (!empty($authToken)) {
                $this->requestAuthToken = $authToken;
                return true;
            }
        }
        return false;
    }

    /**
     * Validate the header content-type
     * @param string $content
     * @return bool
     */
    final public function hasContentType(string $content)
    {
        return (!empty($this->contentType) && ($this->contentType == strtolower($content)));
    }

    /**
     * Validate the HTTP protocol is secure or not ?
     * @return bool
     */
    public function hasSecure()
    {
        $cond_1 = (!empty($this->https) && $this->https === 'on');
        $cond_2 = (!empty($this->serverPort) && $this->serverPort === 443);
        $cond_3 = (!empty($this->httpXForwardedSsl) && $this->httpXForwardedSsl === 'on');
        $cond_4 = (!empty($this->httpXForwardedProto) && $this->httpXForwardedProto === 'https');
        return ($cond_1 || $cond_2 || $cond_3 || $cond_4);
    }

    /**
     * Check if the requestName is an AJAX requestName
     * @return bool
     */
    final public function is_ajax()
    {
        return (!empty($this->httpXRequestedWith) && ('xmlhttprequest' == strtolower($this->httpXRequestedWith) ?? false));
    }

    /**
     * Return all parameter of the requestName to be objects
     * For method: GET, POST
     * @return object
     */
    final public function param(string $requestName = null)
    {
        if ($this->requestMethod == 'GET') { # when current method is GET
            $paramType = $_GET;
            $inputType = INPUT_GET;
        } else if ($this->requestMethod == 'POST') { # when current method is POST
            $paramType = $_POST;
            $inputType = INPUT_POST;
        }
        if (!empty($paramType)) {
            $list_params = [];
            foreach ($paramType as $key => $value) {
                $list_params[$key] = (filter_input($inputType, $key, FILTER_SANITIZE_SPECIAL_CHARS));
            }
            # return parameter as objects
            if (!empty($list_params)) {
                if (!empty($requestName)) {
                    $requestName = strtolower($requestName);
                    return $list_params[$requestName];
                }
                return (object) $list_params;
            }
            return null;
        }
        return null;
    }

    /**
     * Return a full path of the requestName
     * @return string
     */
    final public function pathinfo(string $requestUri = null): string
    {
        return (string) implode('', [($this->hasSecure()) ? 'https://' : 'http://', $this->httpHost, (!empty($requestUri)) ? $requestUri : $this->requestUri]);
    }

    /**
     * Set the default of hashing
     * for generate csrf token
     * @return void
     */
    final public function set_default_hashing(string $hashing)
    {
        self::$_hash_key = $hashing;
    }

    /**
     * reformat string to camel case 
     * @param string $string
     * @return string
     */
    private function strCamelCase(string $string, string $pattern)
    {
        $string = strtolower($string);
        preg_match_all("/$pattern/", $string, $matches);
        foreach ($matches[0] as $match) {
            $str_replace = str_replace('_', '', strtoupper($match));
            $string = str_replace($match, $str_replace, $string);
        }
        return $string;
    }

    /**
     * Verify the csrf token
     * @return Response
     */
    final public function verifyCsrf()
    {
        if ($this->requestMethod == 'POST') {
            $resultVerify = false;
            $csrfToken = (string) $this->param('_csrf_token');
            $viaRequestWeb = $this->viaRequest('/', '*/*');
            if (!empty($csrfToken) && $viaRequestWeb) {
                $hash_hmac_key = (!empty(self::$_hash_key) ? self::$_hash_key : self::HASH_KEY);
                $csrfServer = hash_hmac('sha3-384', session_id(), $hash_hmac_key);
                $csrfClient = base64_decode($csrfToken);
                $resultVerify = (hash_equals($csrfServer, $csrfClient)) ? true : false;

                if(!$resultVerify) {
                    return Response::instance()->redirect('/400');
                }
            }
        }
    }

    /**
     * Validate the requestName are from API , Ajax and JSON or not ?
     * @param string $requestUri
     * @param string $accept
     * @return bool
     */
    final public function viaRequest(string $requestUri, string $accept)
    {
        if (strpos($this->requestUri, $requestUri) !== false) {
            return $this->hasAccept($accept);
        }
        return false;
    }
}
