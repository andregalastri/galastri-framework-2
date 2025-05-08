<?php
/**
 * Documentação:
 * https://github.com/andregalastri/galastri-framework-2/wiki/Classe-Tools
 */

namespace galastri\modules;

use galastri\extensions\Exception;
use galastri\language\Message;

final class Tools
{
    const FLAG_REPLACER_REGEX_SEQUENTIAL = '/(?<!%)%s(?!\d)/';
    const FLAG_REPLACER_REGEX_POSITIONAL = '/(?<!%)%(\d+)/';
    const FLAG_REPLACER_ESCAPE = '%%';
    const FLAG_REPLACER_ESCAPE_REPLACE_TAG = '%';

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

    public static function typeOf(mixed $value, bool $getClassForObjects = false): string
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
    
        if ($getClassForObjects && $type === 'object') {
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
        preg_match_all(self::FLAG_REPLACER_REGEX_SEQUENTIAL, $message, $matchSequential);
        preg_match_all(self::FLAG_REPLACER_REGEX_POSITIONAL, $message, $matchPositional);
    
        if (count($matchSequential[0]) + count($matchPositional[0]) > count($args)) {
            throw new Exception(
                Message::get("TOOLS_NUM_OF_FLAGS_UNMATCH_STRING_FLAGS"),
                [
                    count($matchSequential[0]) + count($matchPositional[0]),
                    count($args),
                ],
            );
        }

        foreach($matchPositional[0] as $key => $flag) {
            $index = $matchPositional[1][$key] - 1;
            
            if (isset($args[$index])) {
                $message = str_replace($flag, $args[$index], $message);
                unset($args[$index]);
            }
        }
    
        foreach($args as $arg) {
            $message = preg_replace(self::FLAG_REPLACER_REGEX_SEQUENTIAL, $arg, $message, 1);
        }
    
        return str_replace(self::FLAG_REPLACER_ESCAPE, self::FLAG_REPLACER_ESCAPE_REPLACE_TAG, $message);
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
        if ($quantity >= count($array)) {
            return [];
        }

        return array_slice($array, 0, -$quantity);
    }

    public static function deleteFirstKey(array $array, int $quantity = 1): array
    {
        if ($quantity >= count($array)) {
            return [];
        }

        return array_slice($array, $quantity);
    }

    public static function arrayAt(array &$array, int $index, bool $remove = false): mixed
    {
        if ($index < 0) {
            $index = count($array) + $index;
        }
    
        $keys = array_keys($array);
    
        if (isset($keys[$index])) {
            $key = $keys[$index];
            $value = $array[$key];
    
            if ($remove) {
                unset($array[$key]);
            }
    
            return $value;
        }
    
        return null;
    }

    public static function arrayFlatten(array $list): array
    {
        $result = [];

        array_walk_recursive($list, function ($item) use (&$result) {
            $result[] = $item;
        });

        return $result;
    }

    public static function import(string $location, mixed $defaultValue): mixed
    {
        return file_exists($location) ? require $location : $defaultValue;
    }
}
