<?php

namespace galastri\modules;

use galastri\extensions\Exception;
use galastri\language\Message;

final class Tools
{
    private function __construct() {}

    public static function createDir(string $path, int $chmod = 0777): string
    {
        if (!is_dir($path)){
            mkdir($path, $chmod, true);
        }
    
        return $path;
    }

    public static function writeFile(string $path, string $content, string $mode = 'w+', int $chmod = 0777): void
    {
        self::createDir(pathinfo($path)['dirname'], $chmod);
    
        $fopen = fopen($path, $mode);
        fwrite($fopen,$content);
        fclose($fopen);
    }

    public static function typeOf(mixed $value): string
    {
        $types = [
            'boolean' => 'bool',
            'integer' => 'int',
            'double' => 'float',
            'string' => 'string',
            'array' => 'array',
            'object' => 'object',
            'resource' => 'resource',
            'NULL' => 'null',
            'unknown type' => 'unknown',
        ];
    
        $type = gettype($value);
    
        if ($type === 'object') {
            return get_class($value);
        }
    
        return $types[$type];
    }

    public static function arrayToString(string $separator, string $lastSeparator, array $array): string
    {
        $array = array_map(function($value) {
            if (in_array(self::typeOf($value), ['null', 'bool'])) {
                return '['.var_export($value, true).']';
            }
            return $value;
        }, $array);
    
        $lastKey = array_pop($array);
    
        if (empty($array)) {
            return $lastKey;
        }
    
        return implode($separator, $array).$lastSeparator.$lastKey;
    }

    public static function arrayMapRecursive(Closure $callback, array $array)
    {
        $recursive = function ($callback, $array, $recursive)
        {
            foreach ($array as $key => $value) {
                if (self::typeOf($value) === 'array') {
                    $result[$key] = $recursive($callback, $value, $array[$key], $recursive);
                } else {
                    $result[$key] = $callback($value);
                }
            }
    
            return $result;
        };
    
        return $recursive($callback, $array, $recursive);
    }
}
