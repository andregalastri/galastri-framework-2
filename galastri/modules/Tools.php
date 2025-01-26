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
            mkdir($path, self::validateChmodCode($chmod), true);
        }
    
        return $path;
    }

    public static function writeFile(string $path, string $content, int $mode = INSERT_CONTENT_AT_END, int $chmod = 0777): void
    {
        if (!file_exists($path)) {
            self::createDir(pathinfo($path)['dirname'], $chmod);

            file_put_contents($path, '');
            self::chmod($path, $chmod);
        }

        switch ($mode) {
            case INSERT_CONTENT_AT_END:
                $content = file_get_contents($path).$content;
                break;
                
            case INSERT_CONTENT_AT_START:
                $content = $content.file_get_contents($path);
                break;

            case OVERWRITE_CONTENT:
                break;
        }

        file_put_contents($path, $content);
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
                Message::get("TOOLS_NUM_OF_FLAGS_UNMATCH_STRING_FLAGS"),
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


    public static function toCamelCase(string $string): string
    {
        $string = preg_replace('/[^a-zA-Z0-9À-ÿ]+/', ' ', $string);

        $string = mb_convert_case($string, MB_CASE_TITLE, "UTF-8");
        
        $string = lcfirst(str_replace(' ', '', $string));

        return $string;
    }

    public static function toPascalCase(string $string): string
    {
        return ucfirst(self::toCamelCase($string));
    }
    
    public static function chmod(string $path, int|string $chmod): void
    {
        chmod($path, self::validateChmodCode($chmod));
    }
    
    private static function validateChmodCode(int|string $chmod): int
    {
        if (!preg_match('/^0[0-7]{3}$/', '0'.decoct($chmod))) {
            throw new Exception(
                Message::get("TOOLS_INVALID_CHMOD_CODE"),
                [$chmod]
            );
        }

        return $chmod;
    }

    public static function deleteLastKey(array $array, int $quantity = 1): array
    {
        if ($quantity >= count($quantity)) {
            return [];
        }

        return array_slice($array, 0, -$quantity);
    }

    public static function deleteFirstKey(array $array, int $quantity = 1): array
    {
        if ($quantity >= count($array)) {
            return [];
        }

        // Remove os primeiros $times elementos de uma vez
        return array_slice($array, $quantity);
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
