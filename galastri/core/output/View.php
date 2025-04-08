<?php

namespace galastri\core\output;

use galastri\core\Router;
use galastri\core\Galastri;
use galastri\core\config\Config;
use galastri\extensions\Exception;
use galastri\language\Message;
use galastri\modules\Tools;

final class View
{
    public static string $viewPath;
    private static string $templateFile = '';

    public static function run(): void
    {
        self::checkViewFile();
        self::checkTemplateFile();
        
        self::render();
    }

    private static function checkViewFile(): void
    {
        $viewFile = Config::get('viewFile');

        if (Config::get('viewFile') == '') {
            $controllerName = implode('/', Tools::deleteFirstKey(Router::getControllerNamespace(), 2));
            $viewFile =  $controllerName.'/'.Galastri::getControllerMethodName().'.php';
        }

        $viewFile = '/'.ltrim($viewFile, '/');

        $viewPath = PROJECT_DIR.Config::get('viewFolder').$viewFile;

        if (!file_exists($viewPath)) {
            throw new Exception(
                Message::get("OUTPUT_VIEW_FILE_NOT_FOUND"),
                [
                    Config::get('viewFolder').$viewFile,
                ]
            );
        }

        self::$viewPath = $viewPath;
    }

    private static function checkTemplateFile(): void
    {
        $templateFile = Config::get('templateFile');
        $templateFileFullPath = PROJECT_DIR.$templateFile;

        if ($templateFile != '') {
            if (!file_exists($templateFileFullPath)) {
                throw new Exception(
                    Message::get("OUTPUT_TEMPLATE_FILE_NOT_FOUND"),
                    [
                        $templateFile,
                    ]
                );
            }

            self::$templateFile = $templateFileFullPath;
        }
    }

    private static function render()
    {
        /**
         * Está assim pois futuramente pretendo inserir a possibilidade de usar
         * template engines alternativas.
         */
        $phpEngine = 'galastri\core\output\engines\PhpEngine';
        $g = new $phpEngine();
        
        require(self::$templateFile == '' ? self::$viewPath : self::$templateFile);
    }

    public static function requiresController(): bool
    {
        return true;
    }
}
