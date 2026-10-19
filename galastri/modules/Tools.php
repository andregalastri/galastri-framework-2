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
    private const PLACEHOLDER_REGEX = '~(\\\\)?\{\{([^{}]*)\}\}~';

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
            throw new Exception('Não foi possível abrir o arquivo para escrita.');
        }

        try {
            if (!flock($handle, LOCK_EX)) {
                throw new Exception('Não foi possível bloquear o arquivo.');
            }

            if ($isNewFile) {
                self::chmod($path, $chmod);
            }

            if (in_array($mode, [INSERT_CONTENT_AT_START, INSERT_CONTENT_AT_END], true)) {
                $existingContent = stream_get_contents($handle);

                if ($existingContent === false) {
                    throw new Exception('Não foi possível ler o arquivo.');
                }

                $content = $mode === INSERT_CONTENT_AT_START ? $content.$existingContent : $existingContent.$content;
            }

            if (!rewind($handle)) {
                throw new Exception('Não foi possível reposicionar o arquivo.');
            }

            $length = strlen($content);
            $offset = 0;

            while ($offset < $length) {
                $written = fwrite($handle, substr($content, $offset));

                if ($written === false || $written === 0) {
                    throw new Exception('Não foi possível concluir a escrita.');
                }

                $offset += $written;
            }

            if (!fflush($handle)) {
                throw new Exception('Não foi possível descarregar a escrita.');
            }

            if (!ftruncate($handle, $length)) {
                throw new Exception('Não foi possível ajustar o tamanho do arquivo.');
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

    public static function hasPlaceholders(string $message): bool
    {
        $result = preg_match_all(
            self::PLACEHOLDER_REGEX,
            $message,
            $matches,
            PREG_SET_ORDER
        );

        if ($result === false) {
            throw new Exception(Message::get('TOOLS_PLACEHOLDER_ANALYSIS_FAILED'));
        }

        foreach ($matches as $match) {
            if ($match[1] === '') {
                return true;
            }
        }

        return false;
    }

    public static function replacePlaceholders(string $message, array $replacements = []): string
    {
        $values = array_values($replacements);
        $mode = null;
        $sequentialIndex = 0;

        $result = preg_replace_callback(
            self::PLACEHOLDER_REGEX,
            function (array $match) use ($replacements, $values, &$mode, &$sequentialIndex): string {
                if ($match[1] !== '') {
                    return substr($match[0], 1);
                }

                $placeholder = $match[2];

                if ($placeholder === '') {
                    $currentMode = 'sequential';
                } elseif (preg_match('/^[1-9][0-9]*$/D', $placeholder)) {
                    $currentMode = 'positional';
                } elseif (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $placeholder)) {
                    $currentMode = 'named';
                } else {
                    throw new Exception(Message::get('TOOLS_INVALID_PLACEHOLDER'), [$match[0]]);
                }

                if ($mode !== null && $mode !== $currentMode) {
                    throw new Exception(Message::get('TOOLS_MIXED_PLACEHOLDER_MODES'));
                }

                $mode = $currentMode;

                if ($mode === 'named') {
                    if (!array_key_exists($placeholder, $replacements)) {
                        throw new Exception(Message::get('TOOLS_MISSING_PLACEHOLDER_VALUE'), [$match[0]]);
                    }

                    $value = $replacements[$placeholder];
                } else {
                    if ($mode === 'sequential') {
                        $index = $sequentialIndex++;
                    } else {
                        $position = filter_var($placeholder, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

                        if ($position === false) {
                            throw new Exception(Message::get('TOOLS_INVALID_PLACEHOLDER_POSITION'), [$match[0]]);
                        }

                        $index = $position - 1;
                    }

                    if (!array_key_exists($index, $values)) {
                        throw new Exception(Message::get('TOOLS_MISSING_PLACEHOLDER_VALUE'), [$match[0]]);
                    }

                    $value = $values[$index];
                }

                if (
                    $value !== null
                    && !is_scalar($value)
                    && !$value instanceof \Stringable
                ) {
                    throw new Exception(Message::get('TOOLS_INVALID_PLACEHOLDER_VALUE'));
                }

                return (string) $value;
            },
            $message
        );

        if ($result === null) {
            throw new Exception(Message::get('TOOLS_PLACEHOLDER_REPLACEMENT_FAILED'));
        }

        return $result;
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
            throw new Exception(Message::get("TOOLS_INVALID_CHMOD_CODE"), [$chmod]);
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
        $path = PROJECT_DIR.'/'.ltrim($location, '/');

        if (file_exists($path)) {
            return require $path;
        }

        return $defaultValue;
    }

    public static function trimLeft(string $string, ...$replacementList): string
    {
        $quoteReplacement = function ($value) {
            return preg_quote($value, '/');
        };

        $replacementList = array_map($quoteReplacement, $replacementList);

        return preg_replace('/^('.implode('|', $replacementList).')\b\s*/i', '', $string);
    }

    public static function trimRight(string $string, ...$replacementList): string
    {
        $quoteReplacement = function ($value) {
            return preg_quote($value, '/');
        };

        $replacementList = array_map($quoteReplacement, $replacementList);

        return preg_replace('/('.implode('|', $replacementList).')\b\s*$/i', '', $string);
    }

    public static function trimBoth(string $string, ...$replacementList): string
    {
        $quoteReplacement = function ($value) {
            return preg_quote($value, '/');
        };

        $replacementList = array_map($quoteReplacement, $replacementList);

        $string = preg_replace('/^('.implode('|', $replacementList).')\b\s*/i', '', $string);
        $string = preg_replace('/('.implode('|', $replacementList).')\b\s*$/i', '', $string);

        return $string;
    }
}
