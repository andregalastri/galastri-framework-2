<?php
/**
 * Documentação:
 * https://github.com/andregalastri/galastri-framework-2/wiki/Classe-Authentication
 */

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

        if (self::validate($authTag)) {
            self::startSession();
            self::$tagList[$authTag] = $_SESSION[$authTag];
            session_write_close();
            return;
        }

        self::$tagList[$authTag] = [
            'token' => base64_encode(random_bytes(48)),
            'ip' => $_SERVER['REMOTE_ADDR'],
            'cookieExpiration' => 86400,
            'fields' => [],
        ];
    }

    public static function create(?string $authTag = null, bool $regenerateIdSession = true): void
    {
        $authTag = $authTag ?? self::$currentAuthTag;

        if ($authTag === null) {
            throw new Exception(Message::get('AUTHENTICATION_UNDEFINED_AUTH_TAG'));
        }

        if (!isset(self::$tagList[$authTag]['token'])) {
            throw new Exception(Message::get('AUTHENTICATION_UNCONFIGURED_AUTH_TAG'), [$authTag]);
        }

        self::startSession();

        if ($regenerateIdSession && !session_regenerate_id(true)) {
            session_write_close();
            throw new Exception(Message::get('AUTHENTICATION_SESSION_REGENERATION_FAILED'));
        }

        $_SESSION[$authTag]['token'] = self::$tagList[$authTag]['token'];
        $_SESSION[$authTag]['ip'] = self::$tagList[$authTag]['ip'];
        $_SESSION[$authTag]['cookieExpiration'] = self::$tagList[$authTag]['cookieExpiration'];
        $_SESSION[$authTag]['fields'] = self::$tagList[$authTag]['fields'];
        $_SESSION[$authTag]['expiresAt'] = time() + self::$tagList[$authTag]['cookieExpiration'];

        setcookie(
            $authTag,
            json_encode(['token' => self::$tagList[$authTag]['token']], JSON_THROW_ON_ERROR),
            [
                'expires' => $_SESSION[$authTag]['expiresAt'],
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict',
                'domain' => parse_url('http://'.$_SERVER['HTTP_HOST'], PHP_URL_HOST),
            ]
        );

        session_write_close();
    }

    public static function validate(string $authTag, bool $ipCheck = false): bool
    {
        self::startSession();

        if (!isset($_SESSION[$authTag], $_COOKIE[$authTag]) || !is_string($_COOKIE[$authTag])) {
            session_write_close();
            return false;
        }

        $expiresAt = $_SESSION[$authTag]['expiresAt'] ?? null;

        if (!is_int($expiresAt) || $expiresAt <= time()) {
            unset($_SESSION[$authTag]);
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

        unset($_SESSION[$authTag], self::$tagList[$authTag]);

        if (self::$currentAuthTag === $authTag) {
            self::$currentAuthTag = null;
        }

        setcookie(
            $authTag,
            '',
            [
                'expires' => time() - 86400,
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict',
                'domain' => parse_url('http://'.$_SERVER['HTTP_HOST'], PHP_URL_HOST),
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
                    'domain' => parse_url('http://'.$_SERVER['HTTP_HOST'], PHP_URL_HOST),
                ]
            );

            unset($_COOKIE[$authTag]);
        }

        session_unset();
        session_destroy();
        self::$tagList = [];
        self::$currentAuthTag = null;
    }

    private static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}
