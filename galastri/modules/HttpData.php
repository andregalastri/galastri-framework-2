<?php
/**
 * Documentação:
 * https://github.com/andregalastri/galastri-framework-2/wiki/Classe-HttpData
 */

namespace galastri\modules;

final class HttpData
{
    public static function getValue(string $key, ?string $requestMethod = null): mixed
    {
        $data = self::getRequestData($requestMethod);
        return $data[$key] ?? null;
    }

    public static function getAll(?string $requestMethod = null): array
    {
        return self::getRequestData($requestMethod);
    }

    public static function has(string $key, ?string $requestMethod = null): bool
    {
        return array_key_exists($key, self::getRequestData($requestMethod));
    }

    public static function getKeys(?string $requestMethod = null): array
    {
        return array_keys(self::getRequestData($requestMethod));
    }

    private static function getRequestData(?string $requestMethod = null): array
    {
        if (empty($requestMethod)) {
            $requestMethod = $_SERVER['REQUEST_METHOD'];
        }

        $requestMethod = mb_strtolower($requestMethod);

        switch ($requestMethod) {
            case 'post':
                return $_POST;

            case 'get':
                return $_GET;

            default:
                return json_decode(file_get_contents('php://input'), true) ?? [];
        }
    }
}
