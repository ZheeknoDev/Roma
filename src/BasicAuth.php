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

final class BasicAuth
{
    private static $_groups;
    private static $_origin_key;
    private static $_request;
    private static $_sipher_package;
    private static $_saparator;

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
        # set saparator
        self::$_saparator = hex2bin(base64_decode('MjQ3OTI0Nzg='));
    }

    /**
     * to get intance of class
     * @return object
     */
    final public static function instance(): object
    {
        return new self();
    }

    /**
     * Setup the origin key, group
     * @param array $setup ['origin_key', 'groups']
     * @return void
     */
    final public static function setup(array $setup): void
    {
        if (array_keys($setup) === ['origin_key', 'groups']) {
            self::$_groups = (is_array($setup['groups']) ? $setup['groups'] : array());
            self::$_origin_key = (is_string($setup['origin_key']) ? $setup['origin_key'] : null);
        }
    }

    /**
     * to get the API's token from the generator
     * @param string $groupName
     * @return object
     */
    final public function getApiToken(string $groupName): object
    {
        $sipher = self::$_sipher_package;
        $result = $sipher->get_string_encrypt(self::$_groups[$groupName]);
        if (!empty($result) && is_object($result)) {
            return (object) [
                'token' => bin2hex(base64_encode(implode(self::$_saparator, [$result->encrypted, $result->key]))),
                'check_hash' => $result->check_hash
            ];
        }
        return false;
    }

    /**
     * to generate the basic of authorization token
     * @return string
     */
    final public function getBasicAuthToken()
    {
        if (!empty(self::$_sipher_package)) {
            $sipher = self::$_sipher_package;
            $str16h = $sipher::randomString(16);
            $str8t = $sipher::randomString(8);
            $encrypt = $sipher->get_crypt(self::$_origin_key);
            if (!empty($encrypt)) {
                return base64_encode(implode(self::$_saparator, [$str16h, $encrypt, $str8t]));
            }
        }
        return false;
    }

    /**
     * to verify basic of authorization
     * @return bool
     */
    final public static function hasBasicAuthorized($request)
    {
        $basicAuth = self::instance();
        if ($request->hasAuthorized('basic')) {
            $basicToken = $request->getAuthorizedToken();
            if (!empty($basicToken)) {
                $basicToken = base64_decode($basicToken);
                $explode = explode($basicAuth::$_saparator, $basicToken);
                $hasValue = array_filter($explode, function ($value) {
                    return ($value !== null);
                });
                if (count($explode) == 3 && !empty($hasValue)) {
                    $cond_1 = strlen($explode[0]) == 16 ? 1 : 0;
                    $cond_2 = strlen($explode[2]) == 8 ? 1 : 0;
                    if ($cond_1 && $cond_2) {
                        $sipher = $basicAuth::$_sipher_package;
                        return $sipher->get_crypt_verify($basicAuth::$_origin_key, $explode[1]);
                    }
                }
            }
        }
        return false;
    }

    /**
     * to call the Sipher class
     * @return object
     */
    final public static function sipher(): object
    {
        $self = self::instance();
        return $self::$_sipher_package;
    }

    /**
     * to verify the API's token
     * @param array $data ['authorized', 'group', 'token', 'check_hash'];
     * @return bool
     */
    final public function verifyApiToken(array $data): bool
    {
        $array_keys = ['authorized', 'group', 'token', 'check_hash'];
        if (array_keys($data) == $array_keys) {
            $hasValue = array_filter($data, function ($value) {
                return ($value !== null);
            });
            if ($hasValue) {
                $request = self::$_request;
                $sipher = self::$_sipher_package;
                $hasAuthorized = $request->hasAuthorized($data['authorized']);
                if ($hasAuthorized && !empty(self::$_sipher_package)) {
                    $token = base64_decode(hex2bin($data['token']));
                    $explode = explode(self::$_saparator, $token);
                    if (count($explode) == 2 && !empty($data['check_hash'])) {
                        return $sipher->get_verify_encrypt($explode[0], $data['check_hash'], $explode[1]);
                    }
                }
            }
        }
        return false;
    }
}
