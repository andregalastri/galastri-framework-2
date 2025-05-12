<?php
/**
 * Documentação:
 * https://github.com/andregalastri/galastri-framework-2/wiki/Classe-View
 */

namespace galastri\core\output;

use galastri\core\Router;
use galastri\core\Galastri;
use galastri\core\config\Config;
use galastri\extensions\Exception;
use galastri\language\Message;
use galastri\modules\Tools;

final class View
{
    use traits\BrowserCache;

    public static string $viewPath;
    private static string $templatePath = '';

    private function __construct() {}

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
                    $viewPath,
                ]
            );
        }

        self::$viewPath = $viewPath;
    }

    private static function checkTemplateFile(): void
    {
        $templateFile = Config::get('templateFile');
        $templatePath = PROJECT_DIR.$templateFile;

        if ($templateFile != '') {
            if (!file_exists($templatePath)) {
                throw new Exception(
                    Message::get("OUTPUT_TEMPLATE_FILE_NOT_FOUND"),
                    [
                        $templatePath,
                    ]
                );
            }

            self::$templatePath = $templatePath;
        }
    }

    private static function render(): void
    {
        ob_start();

        /**
         * Está assim pois futuramente pretendo inserir a possibilidade de usar
         * template engines alternativas.
         */
        $phpEngine = 'galastri\core\output\engines\PhpEngine';
        $g = new $phpEngine();

        require(self::$templatePath === '' ? self::$viewPath : self::$templatePath);

        $renderedContent = ob_get_clean();

        if (self::checkBrowserCache($renderedContent)) {
            return;
        }

        @print($renderedContent);
    }


    public static function requiresController(): bool
    {
        return true;
    }
}
