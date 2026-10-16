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
        $isNewFile = !file_exists($path);

        if ($isNewFile) {
            self::createDir(pathinfo($path)['dirname'], $chmod);
        }

        $handle = fopen($path, 'c+b');

        if ($handle === false) {
            throw new \RuntimeException('Não foi possível abrir o arquivo para escrita.');
        }

        try {
            if (!flock($handle, LOCK_EX)) {
                throw new \RuntimeException('Não foi possível bloquear o arquivo.');
            }

            if ($isNewFile) {
                self::chmod($path, $chmod);
            }

            if (in_array($mode, [INSERT_CONTENT_AT_START, INSERT_CONTENT_AT_END], true)) {
                $existingContent = stream_get_contents($handle);

                if ($existingContent === false) {
                    throw new \RuntimeException('Não foi possível ler o arquivo.');
                }

                $content = $mode === INSERT_CONTENT_AT_START ? $content.$existingContent : $existingContent.$content;
            }

            if (!rewind($handle)) {
                throw new \RuntimeException('Não foi possível reposicionar o arquivo.');
            }

            $length = strlen($content);
            $offset = 0;

            while ($offset < $length) {
                $written = fwrite($handle, substr($content, $offset));

                if ($written === false || $written === 0) {
                    throw new \RuntimeException('Não foi possível concluir a escrita.');
                }

                $offset += $written;
            }

            if (!fflush($handle)) {
                throw new \RuntimeException('Não foi possível descarregar a escrita.');
            }

            if (!ftruncate($handle, $length)) {
                throw new \RuntimeException('Não foi possível ajustar o tamanho do arquivo.');
            }
        } finally {
            fclose($handle);
        }
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
        if ($array === []) {
            return '';
        }
        
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
        return file_exists(PROJECT_DIR.'/'.$location) ? require $location : $defaultValue;
    }

    public static function trimLeft(string $string, ...$replacementList): string
    {
        $replacementList = array_map('preg_quote', $replacementList);

        return preg_replace('/^('.implode('|', $replacementList).')\b\s*/i', '', $string);
    }

    public static function trimRight(string $string, ...$replacementList): string
    {
        $replacementList = array_map('preg_quote', $replacementList);

        return preg_replace('/('.implode('|', $replacementList).')\b\s*$/i', '', $string);
    }

    public static function trimBoth(string $string, ...$replacementList): string
    {
        $replacementList = array_map('preg_quote', $replacementList);

        $string = preg_replace('/^('.implode('|', $replacementList).')\b\s*/i', '', $string);
        $string = preg_replace('/('.implode('|', $replacementList).')\b\s*$/i', '', $string);

        return $string;
    }
}
