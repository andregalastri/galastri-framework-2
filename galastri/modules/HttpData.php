<?php
/**
 * Documentação:
 * https://github.com/andregalastri/galastri-framework-2/wiki/Classe-HttpData
 */

namespace galastri\modules;

use galastri\extensions\Exception;
use galastri\language\Message;

final class HttpData
{
    public static function getValue(string $key): mixed
    {
        $data = self::getRequestData();
        return $data[$key] ?? null;
    }

    public static function getAll(): array
    {
        return self::getRequestData();
    }

    public static function has(string $key): bool
    {
        return array_key_exists($key, self::getRequestData());
    }

    public static function getKeys(): array
    {
        return array_keys(self::getRequestData());
    }

    private static function getRequestData(): array
    {
        if (!empty($_POST)) {
            return array_replace($_GET, $_POST);
        }

        $input = file_get_contents('php://input');

        if ($input === false) {
            throw new Exception(Message::get('HTTP_DATA_READ_FAILED'));
        }

        $contentType = strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '', 2)[0]));
        $isJson = $contentType === 'application/json' || (str_starts_with($contentType, 'application/') && str_ends_with($contentType, '+json'));

        if ($isJson && $input !== '') {
            try {
                $body = json_decode($input, true, 512, JSON_THROW_ON_ERROR | JSON_BIGINT_AS_STRING);
            } catch (\JsonException $e) {
                http_response_code(400);
                throw new Exception(Message::get('HTTP_DATA_INVALID_JSON'));
            }

            if (!is_array($body)) {
                http_response_code(400);
                throw new Exception(Message::get('HTTP_DATA_INVALID_JSON_STRUCTURE'));
            }
        } else {
            $body = json_decode($input, true, 512, JSON_BIGINT_AS_STRING);
        }

        if (is_array($body)) {
            return array_replace($_GET, $body);
        }

        return $_GET;
    }
}
