<?php
/**
 * Documentação da classe:
 * https://github.com/andregalastri/galastri-framework-2/wiki/Classe-Router
 */

namespace galastri\core;

use galastri\core\config\Config;
use galastri\extensions\Exception;
use galastri\language\Message;

final class Router
{
    private const APP_CONFIG_FILE_ROUTES = PROJECT_DIR.'/app/config/routes.php';

    private function __construct() {}

    public static function run(): void
    {
        self::configureUrlRoot();
    }

    private static function configureUrlRoot(): void
    {
        $routes = Config::importConfig(self::APP_CONFIG_FILE_ROUTES);

        try {
            Config::set('urlRoot', array_key_first($routes));
        } catch (Exception $e) {
            throw new Exception(
                Message::get("ROUTER_INVALID_URL_ROOT"),
                [
                    str_replace(PROJECT_DIR.'/', '', self::APP_CONFIG_FILE_ROUTES),
                    array_key_first($routes),
                ]
            );
        }
    }

    private static function prepareUrlArray(): void
    {
        // Parameters::setUrlRoot(key($GLOBALS['GALASTRI_ROUTES']) ?? null);
        
        // $requestUri = (new TypeString($_SERVER['REQUEST_URI']))->replaceOnce(ltrim(Parameters::getUrlRoot(), '/'), '')
        //                                                        ->get();

        // $urlArray = explode('?', $requestUri);
        // $urlArray = explode('/', $urlArray[0]);

        // if (empty($urlArray[1])) {
        //     array_shift($urlArray);
        // }

        // foreach ($urlArray as &$urlNode) {
        //     $urlNode = '/' . $urlNode;
        // }
        // unset($urlNode);

        // self::$urlArray = $urlArray;

        // PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
    }
}