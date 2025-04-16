<?php
namespace galastri\modules;

use galastri\extensions\Exception;
use galastri\language\Message;

final class Authentication
{
    private static ?string $currentAuthTag = null;
    private static array $tagList = [];

    public static function setAuthTag(string $authTag): void
    {
        self::$currentAuthTag = $authTag;

        if (!self::validate($authTag)) {
            self::$tagList[$authTag] = [
                'token' => base64_encode(random_bytes(48)),
                'ip' => $_SERVER['REMOTE_ADDR'],
                'cookieExpiration' => 86400,
                'fields' => [],
            ];
        }
    }

    public static function create(?string $authTag = null, bool $regenerateIdSession = true): void
    {
        $authTag = $authTag ?? self::$currentAuthTag;

        self::startSession();

        $_SESSION[$authTag]['token'] = self::$tagList[$authTag]['token'];
        $_SESSION[$authTag]['ip'] = self::$tagList[$authTag]['ip'];
        $_SESSION[$authTag]['cookieExpiration'] = self::$tagList[$authTag]['cookieExpiration'];
        $_SESSION[$authTag]['fields'] = self::$tagList[$authTag]['fields'];

        setcookie(
            $authTag,
            json_encode(self::$tagList[$authTag]),
            [
                'expires' => time() + self::$tagList[$authTag]['cookieExpiration'],
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict',
                'domain' => $_SERVER['HTTP_HOST'],
            ]
        );

        if ($regenerateIdSession) {
            session_regenerate_id();
        }

        session_write_close();
    }

    public static function validate(string $authTag, bool $ipCheck = false): bool
    {
        self::startSession();

        if (!isset($_SESSION[$authTag]) && !self::createSessionFromCookie($authTag)) {
            session_write_close();
            return false;
        }

        $cookie = json_decode($_COOKIE[$authTag], true);

        if (!is_array($cookie) || !isset($cookie['token'], $_SESSION[$authTag]['token'])) {
            session_write_close();
            return false;
        }

        if ($_SESSION[$authTag]['token'] !== $cookie['token']) {
            session_write_close();
            return false;
        }

        if ($ipCheck && $_SESSION[$authTag]['ip'] !== $_SERVER['REMOTE_ADDR']) {
            session_write_close();
            return false;
        }

        session_write_close();
        return true;
    }

    public static function setField(string $fieldName, bool|int|null|string $fieldValue, ?string $authTag = null): void
    {
        $authTag = $authTag ?? self::$currentAuthTag;

        self::$tagList[$authTag]['fields'][$fieldName] = $fieldValue;
    }

    public static function getField(string $field, ?string $authTag = null): bool|int|null|string
    {
        self::startSession();

        $authTag = $authTag ?? self::$currentAuthTag;

        $value = $_SESSION[$authTag]['fields'][$field] ?? null;

        session_write_close();

        return $value;
    }

    public static function setCookieExpiration(int $seconds, ?string $authTag = null): void
    {
        $authTag = $authTag ?? self::$currentAuthTag;

        self::$tagList[$authTag]['cookieExpiration'] = $seconds;
    }

    public static function remove(string $authTag): void
    {
        self::startSession();

        unset($_SESSION[$authTag]);

        setcookie(
            $authTag,
            '',
            [
                'expires' => time() - 86400,
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict',
                'domain' => $_SERVER['HTTP_HOST'],
            ]
        );

        unset($_COOKIE[$authTag]);

        session_write_close();
    }

    public static function destroy(): void
    {
        self::startSession();

        foreach(array_keys($_COOKIE) as $authTag){
            setcookie(
                $authTag,
                '',
                [
                    'expires' => time() - 86400,
                    'path' => '/',
                    'secure' => true,
                    'httponly' => true,
                    'samesite' => 'Strict',
                    'domain' => $_SERVER['HTTP_HOST'],
                ]
            );

            unset($_COOKIE[$authTag]);
        }

        session_unset();
        session_destroy();
    }

    private static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private static function createSessionFromCookie(string $authTag): bool
    {
        if (isset($_COOKIE[$authTag])) {
            $cookie = json_decode($_COOKIE[$authTag], true);

            if (is_array($cookie) && isset($cookie['token'])) {
                self::$tagList[$authTag]['token'] = $cookie['token'];
                self::$tagList[$authTag]['ip'] = $_SERVER['REMOTE_ADDR'];
                self::$tagList[$authTag]['cookieExpiration'] = $cookie['cookieExpiration'];
                self::$tagList[$authTag]['fields'] = $cookie['fields'];

                self::create($authTag);
                return true;
            }
        }

        return false;
    }
}
