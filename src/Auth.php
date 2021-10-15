<?php

/**
 * @category Class
 * @package  Roma/Auth
 * @author   ZheeknoDev <million8.me@gmail.com>
 * @license  https://opensource.org/licenses/MIT - MIT License 
 * @link     https://github.com/ZheeknoDev/Roma
 */

namespace Zheeknodev\Roma;

use Exception;
use Zheeknodev\Roma\Router\Request;
use Zheeknodev\Sipher\Sipher;

abstract class Auth
{
    private static $_groups;
    private static $_origin_key;
    private static $_request;
    private static $_sipher_package;
    private static $_split_key;

    public function __construct()
    {
        # validate orgin_key & groups of auth
        if (empty(self::$_origin_key) || empty(self::$_groups)) {
            # self::$_origin_key & self::$_groups must be setup in initial.
            throw new Exception('Unable to load ' . __CLASS__ . ' ,bacause ' . __CLASS__ . '::setup() is not set.');
            exit();
        }

        # set the Sipher package
        self::$_sipher_package = new Sipher(self::$_origin_key);
        if (empty(self::$_sipher_package) || !is_object(self::$_sipher_package)) {
            throw new Exception('Something went wrong with the Sipher pagkage class.');
            exit();
        }
        # set the request class
        self::$_request = new Request;
        # set split word
        self::$_split_key = hex2bin(base64_decode('MjQ3OTI0Nzg='));
    }

    final public static function setup(array $setup)
    {
        if (array_keys($setup) === ['origin_key', 'groups']) {
            self::$_groups = (is_array($setup['groups']) ? $setup['groups'] : array());
            self::$_origin_key = (is_string($setup['origin_key']) ? $setup['origin_key'] : null);
        }
    }

    final public static function getApiToken(string $groupName)
    {
        $sipher = self::$_sipher_package;
        $result = $sipher->get_string_encrypt(self::$_groups[$groupName]);
        return (!empty($result) && is_object($result)) ? $result : null;
    }

    final public static function verifyApiToken(array $data)
    {
        if (array_keys($data) == ['authorized', 'group', 'check_hash']) {
            $hasValue = array_filter($data, function ($value) {
                return ($value !== null);
            });
            if($hasValue) {
                $request = self::$_request;
                $sipher = self::$_sipher_package;
                $hasAuthorized = $request->hasAuthorized($data['authorized']);
                $getAuthorizedToken = $request->getAuthorizedToken();
                if($hasAuthorized && !empty($getAuthorizedToken) && !empty(self::$_sipher_package)) {
                    $token = base64_decode($getAuthorizedToken);
                    $explode = explode(self::$_split_key, $token);
                    if(count($explode) == 2 && !empty($data['check_hash'])) {
                        return $sipher->get_verify_encrypt($explode[0], $data['check_hash'], $explode[1]);
                    }
                }
            }
        }
        return false;
    }
}