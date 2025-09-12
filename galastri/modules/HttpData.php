<?php
/**
 * Documentação:
 * https://github.com/andregalastri/galastri-framework-2/wiki/Classe-HttpData
 */

namespace galastri\modules;

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
        if(!empty($_GET)) {
            return $_GET;
        } elseif (!empty($_POST)) {
            return $_POST;
        } else {
            return json_decode(file_get_contents('php://input'), true) ?? [];
        }
    }
}
