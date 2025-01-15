<?php

namespace galastri\core;

use galastri\extensions\Exception;
use galastri\language\Message;

final class CoreTools
{
    const FLAG_REPLACER_REGEX = '/(?<!%)%s/m';
    const FLAG_REPLACER_ESCAPE = '%%s';
    const FLAG_REPLACER_REPLACE_TAG = '%s';

    private function __construct() {}

    //https://stackoverflow.com/a/23528413
    public static function flagReplace(string $message, array $args): string
    {
        preg_match_all(self::FLAG_REPLACER_REGEX, $message, $match);
    
        if (count($match[0]) > count($args)) {
            throw new Exception(
                Message::TOO_FEW_ARGUMENTS_EXCEPTION_PRINTF,
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
}
