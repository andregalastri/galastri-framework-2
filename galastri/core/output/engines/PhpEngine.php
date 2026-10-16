<?php
/**
 * Documentação:
 * https://github.com/andregalastri/galastri-framework-2/wiki/Classe-PhpEngine
 */

namespace galastri\core\output\engines;

use galastri\core\Galastri;
use galastri\core\output\View;
use galastri\extensions\Exception;
use galastri\language\Message;

final class PhpEngine
{

    private array $controllerData;

    public function __construct() {
        $this->controllerData = Galastri::getControllerResponse();
    }

    public function get(...$keyList): mixed
    {
        $currentValue = $this->controllerData;
        
        foreach ($keyList[0] as $key) {
            if (array_key_exists($key, $currentValue)) {
                $currentValue = $currentValue[$key];
            }
        }
        
        return $currentValue;
    }

    public function print(...$keyList) : void
    {
        $data = $this->get($keyList);
        
        if ($data === []) {
            $data = null;
        }
        
        switch (gettype($data)) {
            case 'boolean':
            case 'array':
            case 'object':
            case 'resource':
            case 'unknown type':
                throw new Exception(Message::get('VIEW_INVALID_PRINT_DATA'));
            case 'NULL':
                $data = '';
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
