<?php
/**
 * Documentação:
 * https://github.com/andregalastri/galastri-framework-2/wiki/Classe-Json
 */

namespace galastri\core\output;

use galastri\core\Debug;
use galastri\core\Galastri;

final class Json
{
    private function __construct() {}
    
    public static function run(): void
    {
        $json = json_encode(Galastri::getControllerResponse(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR);

        header('Content-Type: application/json');
        echo $json;
    }

    public static function requiresController(): bool
    {
        return true;
    }
}
