<?php

namespace galastri\core\output;

use galastri\core\Debug;
use galastri\core\Galastri;

final class Json
{
    public static function run(): void
    {
        header('Content-Type: application/json');
        echo json_encode(Galastri::getControllerResponse(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public static function jsonRequiresController(): bool
    {
        return true;
    }
}
