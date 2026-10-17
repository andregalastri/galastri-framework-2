<?php
/**
 * Documentação:
 * Em breve
 */

namespace galastri\modules;

use galastri\core\output\Json;

final class ResponseStream
{
    private function __construct() {}

    public static function send(array $data): void
    {
        Json::stream($data);
    }
}
