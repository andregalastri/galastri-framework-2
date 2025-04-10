<?php
/**
 * Documentação:
 * https://github.com/andregalastri/galastri-framework-2/wiki/Classe-PhpEngine
 */

namespace galastri\core\output\engines;

use galastri\core\Galastri;
use galastri\core\output\View;

final class PhpEngine
{

    private array $controllerData;

    public function __construct() {
        $this->controllerData = Galastri::getControllerResponse();
    }

    public function get(int|string $key): mixed
    {
        return $this->controllerData[$key] ?? null;
    }

    public function print(int|string $key) : void
    {
        $data = $this->get($key);

        switch (gettype($data)) {
            case 'array':
            case 'object':
            case 'boolean':
            case 'null':
                throw new Exception(self::VIEW_INVALID_PRINT_DATA);
        }

        echo htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }

    public function include(string $path): ?bool
    {
        $g = $this;

        if ($path === 'view' and View::$viewPath != '') {
            if (file_exists(View::$viewPath)) {
                return require(View::$viewPath);
            }
        } else {
            $path = PROJECT_DIR.'/'.ltrim($path, '/');

            if (file_exists($path)) {
                return include($path);
            }
        }

        return null;
    }
}
