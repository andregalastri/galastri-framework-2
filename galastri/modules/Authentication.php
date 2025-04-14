<?php

namespace galastri\modules;

use galastri\extensions\Exception;
use galastri\language\Message;

final class Authentication
{
    
    private static array $fields;
    private static array $token;
    private static array $ip;
    private static array $cookieExpiration;
    private static ?string $authTag = null;

    private static string $secretKeyLocation = PROJECT_DIR.'/app/config/secrets.php';
    private static string $secretKey = '';
    private static string $cipher = 'aes-256-cbc';

    private function __construct() {}

    public static function configure(string $authTag, ?int $cookieExpiration = null): void
    {
        self::$token[$authTag] = base64_encode(random_bytes(48));
        self::$ip[$authTag] = $_SERVER['REMOTE_ADDR'];
        self::$cookieExpiration[$authTag] = $cookieExpiration ?? 86400;


        self::$authTag = $authTag;
    }

    public static function setField(string $field, bool|int|null|string $value, ?string $authTag = null)// : self
    {
        $authTag = $authTag ?? self::$authTag;

        if (empty($authTag)) {
            throw new Exception(
                Message::get('AUTHENTICATION_UNDEFINED_AUTH_TAG')
            );
        }

        self::$fields[$authTag][$field] = $value;
    }

    public static function create(?string $authTag = null): void
    {
        $authTag = $authTag ?? self::$authTag;

        if (empty($authTag)) {
            throw new Exception(
                Message::get('AUTHENTICATION_UNDEFINED_AUTH_TAG')
            );
        }

        if (!isset(self::$token[$authTag])) {
            throw new Exception(
                Message::get('AUTHENTICATION_UNCONFIGURED_AUTH_TAG')
            );
        }

        session_start();

        $_SESSION[$authTag]['token'] = self::$token[$authTag];
        $_SESSION[$authTag]['ip'] = self::$ip[$authTag];
        $_SESSION[$authTag]['cookieExpiration'] = self::$cookieExpiration[$authTag];

        setcookie(
            $authTag,
            self::$token[$authTag],
            [
                'expires' => time() + self::$cookieExpiration[$authTag],
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict',
                'domain' => $_SERVER['HTTP_HOST'],
            ]
        );

        foreach (self::$fields[$authTag] as $field => $value) {
            $_SESSION[$authTag]['fields'][$field] = $value;
        }

        setcookie(
            $authTag.'Fields',
            self::encryptCookie($_SESSION[$authTag]['fields']),
            [
                'expires' => time() + self::$cookieExpiration[$authTag],
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict',
                'domain' => $_SERVER['HTTP_HOST'],
            ]
        );

        session_regenerate_id();
        session_write_close();
    }

    public static function update(string $authTag): void
    {
        if (self::check($authTag)){
            self::create($authTag);
        }
    }

    public static function remove(string $authTag): bool
    {
        session_start();

        if (self::check($authTag)){
            unset($_SESSION[$authTag]);

            setcookie($authTag, null, time() - 3600, '/');
            unset($_COOKIE[$authTag]);

            setcookie($authTag.'Fields', null, time() - 3600, '/');
            unset($_COOKIE[$authTag.'Fields']);

            return true;
        }

        session_write_close();

        return false;
    }

    public static function destroy(): void
    {
        session_start();

        foreach($_SESSION as $key => $value){
            setcookie($key, null, time() - 3600, '/');
            unset($_COOKIE[$key]);
            unset($_COOKIE[$key.'Fields']);
        }

        session_unset();
        session_destroy();
    }

    public static function getSession(string $authTag): ?array
    {
        session_start();

        if (self::check($authTag)){
            $sessionFields = $_SESSION[$authTag]['fields'];

            session_write_close();

            return $sessionFields;
        }

        session_write_close();

        return null;
    }

    public static function getCookies(string $authTag): ?array
    {
        session_start();

        if (self::check($authTag)){
            $cookie = self::decryptCookie($_COOKIE[$authTag.'Fields'], true);

            session_write_close();

            return $cookie;
        }

        session_write_close();

        return null;
    }

    public static function validate(string $authTag, bool $ipCheck = false): bool
    {
        session_start();

        if (self::check($authTag)){
            if ($_SESSION[$authTag]['token'] === $_COOKIE[$authTag]){
                if ($ipCheck){
                    if ($_SESSION[$authTag]['ip'] === $_SERVER['REMOTE_ADDR']){
                        session_write_close();

                        return true;
                    } else {
                        session_write_close();

                        return false;
                    }
                } else {
                    session_write_close();

                    return true;
                }
            }
        }

        session_write_close();
        
        return false;
    }

    private static function check(string $authTag): bool
    {
        return session_status() !== PHP_SESSION_NONE && isset($_SESSION[$authTag]['token']);
    }

    private static function encryptCookie(array $data): string
    {
        self::getAuthKey();

        $iv = random_bytes(openssl_cipher_iv_length(self::$cipher));
        $encryptedData = openssl_encrypt(json_encode($data), self::$cipher, self::$secretKey, 0, $iv);
        
        return base64_encode($iv.$encryptedData);
    }

    private static function decryptCookie(string $data): ?array
    {
        self::getAuthKey();

        $raw = base64_decode($data);
        $ivLength = openssl_cipher_iv_length(self::$cipher);
        $iv = substr($raw, 0, $ivLength);
        $encryptedData = substr($raw, $ivLength);
        $decryptedData = openssl_decrypt($encryptedData, self::$cipher, self::$secretKey, 0, $iv);
        
        return $decryptedData !== false ? json_decode($decryptedData, true) : null;
    }

    private static function getAuthKey(): void
    {
        if (self::$secretKey !== '') {
            return;
        }

        if (!file_exists(self::$secretKeyLocation)){
            throw new Exception(
                Message::get('AUTHENTICATION_UNCONFIGURED_SECRET_KEY')
            );
        }
        
        $secretKey = require(self::$secretKeyLocation);

        if (!is_array($secretKey) || empty($secretKey['AUTH_SECRET_KEY'])) {
            throw new Exception(
                Message::get('AUTHENTICATION_UNCONFIGURED_SECRET_KEY')
            );
        }

        self::$secretKey = $secretKey['AUTH_SECRET_KEY'];
    }
}
