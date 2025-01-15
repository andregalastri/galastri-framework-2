<?php
/**
 * Documentação da classe:
 * https://github.com/andregalastri/galastri-framework-2/wiki/Classe-Tools
 */

namespace galastri\modules;

use galastri\extensions\Exception;
use galastri\language\Message;

final class Tools
{
    const FLAG_REPLACER_REGEX = '/(?<!%)%s/m';
    const FLAG_REPLACER_ESCAPE = '%%s';
    const FLAG_REPLACER_REPLACE_TAG = '%s';

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

    public static function readableImplode(string $separator, string $lastSeparator, array $array): string
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

    /**
     * Source: https://stackoverflow.com/a/23528413
     */
    public static function flagReplace(string $message, array $args): string
    {
        preg_match_all(self::FLAG_REPLACER_REGEX, $message, $match);
    
        if (count($match[0]) > count($args)) {
            throw new Exception(
                Message::UNMATCHED_ARGUMENT_COUNT,
                [
                    (string)substr_count($message, self::FLAG_REPLACER_REPLACE_TAG),
                    (string)count($args),
                ],
            );
        }
    
        foreach( $args as $arg ) {
            $message = preg_replace(self::FLAG_REPLACER_REGEX, $arg, $message, 1);
        }
    
        return str_replace(self::FLAG_REPLACER_ESCAPE, self::FLAG_REPLACER_REPLACE_TAG, $message);
    }

    // public static function arrayMapRecursive(Closure $callback, array $array)
    // {
    //     $recursive = function ($callback, $array, $recursive)
    //     {
    //         foreach ($array as $key => $value) {
    //             if (self::typeOf($value) === 'array') {
    //                 $result[$key] = $recursive($callback, $value, $array[$key], $recursive);
    //             } else {
    //                 $result[$key] = $callback($value);
    //             }
    //         }
    
    //         return $result;
    //     };
    
    //     return $recursive($callback, $array, $recursive);
    // }
}
