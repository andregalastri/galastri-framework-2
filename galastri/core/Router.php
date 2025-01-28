<?php
/**
 * Documentação da classe:
 * https://github.com/andregalastri/galastri-framework-2/wiki/Classe-Router
 */

namespace galastri\core;

use galastri\core\config\Config;
use galastri\extensions\Exception;
use galastri\language\Message;
use galastri\modules\Tools;

final class Router
{
    private const APP_CONFIG_FILE_ROUTES = PROJECT_DIR.'/app/config/routes.php';

    private static string $rootDir;
    private static array $routeData;
    private static array $urlParts;
    private static string $nodeName = '';
    private static string $controllerName = '';
    private static array $controllerNamespace = ['app', 'controllers'];
    private static array $dynamicNodes = [];
    private static string $methodName = 'main';
    private static bool $isValidRoute = true;
    private static bool $removedIndexFromNamespace = false;
    private static array $parameters = [];

    private function __construct() {}

    public static function run(): void
    {
        self::$routeData = Config::importConfig(self::APP_CONFIG_FILE_ROUTES);

        self::configureRootDir();
        self::setUrlParts();
        self::resolveNodes();
        self::configureNodeProperties();
        self::resolveEndpoints();
        self::resolveParameters();

        // self::setUrlParts();
    }

    private static function configureRootDir(): void
    {
        $rootDir = array_key_first(self::$routeData);
        
        if (preg_match('/^\/(?:[\p{L}\p{N}_-]+\/?)*[\p{L}\p{N}_-]+$|^\/$/u', $rootDir)) {
            self::$rootDir = $rootDir;
        } else {
            throw new Exception(
                Message::get("ROUTER_INVALID_URL_ROOT"),
                [
                    str_replace(PROJECT_DIR.'/', '', self::APP_CONFIG_FILE_ROUTES),
                    $rootDir,
                ]
            );
        }
    }

    private static function setUrlParts(): void
    {
        $requestUri = self::removePrefixOnce($_SERVER['REQUEST_URI'], self::$rootDir);
        $urlSplit = explode('?', $requestUri);

        $querystring = $urlSplit[1] ?? [];
        $parts = $urlSplit[0] ?? [];

        $urlParts = [''];

        foreach (explode('/', $parts) as $partValue) {
            if (!is_null($partValue) && $partValue !== '') {
                $urlParts[] = $partValue;
            }
        }

        self::$urlParts = $urlParts;
    }

    private static function resolveNodes(): void
    {
        $found = false;

        foreach (self::$urlParts as $partValue) {

            if (array_key_exists('/'.$partValue, self::$routeData)) {
                $found = true;

                self::$routeData = self::$routeData['/'.$partValue];
                self::$nodeName = '/'.$partValue;

                self::processNodeData($partValue);
                break;
            }

            if (!$found) {
                foreach (self::$routeData as $key => $routeData) {
                    if ('@'.$partValue == $key) {
                        $found = true;
                        break 2;
                    }
                }
            }

            if (!$found) {
                foreach (self::$routeData as $key => $routeData) {
                    if (strpos($key, '/?') === 0) {
                        $found = true;

                        self::$routeData = $routeData;
                        self::$nodeName = $key;

                        self::$dynamicNodes = [ltrim($key, '/?') => $partValue];

                        self::processNodeData($key);
                        break 2;
                    }
                }
            }
        }
    }

    private static function processNodeData(string $className): void
    {
        array_shift(self::$urlParts);

        self::configureRouteProperties();
        self::resolveNamespace($className);
        self::resolveNodes();
    }

    private static function configureRouteProperties(): void
    {
        self::setConfigIfExists('projectName');
        self::setConfigIfExists('title');
        self::setConfigIfExists('output');
        self::setConfigIfExists('timezone');
        self::setConfigIfExists('offline');
        self::setConfigIfExists('offlineRedirect');
        self::setConfigIfExists('offlineMessage');
        self::setConfigIfExists('authTag');
        self::setConfigIfExists('authFailRedirect');
        self::setConfigIfExists('authFailMessage');
        self::setConfigIfExists('notFoundRedirect');
        self::setConfigIfExists('notFoundMessage');
        self::setConfigIfExists('forceRedirect');

        try {
            self::setConfigIfExists('namespace');
        } catch (Exception $e) {
            throw new Exception(
                Message::get("ROUTER_INVALID_NAMESPACE"),
                [
                    str_replace(PROJECT_DIR.'/', '', self::APP_CONFIG_FILE_ROUTES),
                    $e->getData('testedValue'),
                ]
            );
        }

        self::setConfigIfExists('browserCache');

        try {
            self::setConfigIfExists('templateFile');
        } catch (Exception $e) {
            throw new Exception(
                Message::get("ROUTER_INVALID_TEMPLATE_FILE"),
                [
                    str_replace(PROJECT_DIR.'/', '', self::APP_CONFIG_FILE_ROUTES),
                    $e->getData('testedValue'),
                ]
            );
        }

        try {
            self::setConfigIfExists('viewFolder');
        } catch (Exception $e) {
            throw new Exception(
                Message::get("ROUTER_INVALID_VIEW_FOLDER"),
                [
                    str_replace(PROJECT_DIR.'/', '', self::APP_CONFIG_FILE_ROUTES),
                    $e->getData('testedValue'),
                ]
            );
        }

        try {
            self::setConfigIfExists('fileFolder');
        } catch (Exception $e) {
            throw new Exception(
                Message::get("ROUTER_INVALID_VIEW_FILE"),
                [
                    str_replace(PROJECT_DIR.'/', '', self::APP_CONFIG_FILE_ROUTES),
                    $e->getData('testedValue'),
                ]
            );
        }

        self::setConfigIfExists('isDownloadableFile');
        self::setConfigIfExists('allowedFileExtensions');
        self::setConfigIfExists('permissionFailMessage');
        self::setConfigIfExists('validateMimeType');
    }

    private static function configureNodeProperties(): void
    {
        if(self::setConfigIfExists('controller')) {
            self::$controllerName = self::$routeData['controller'];
            
            array_pop(self::$controllerNamespace);

            self::$controllerNamespace[] = self::$routeData['controller'];
        }
    }

    private static function configureEndpointProperties(): void
    {
        self::setConfigIfExists('method');

        if (isset(self::$routeData['httpMethod'])) {
            $httpMethod = [];

            foreach(self::$routeData['httpMethod'] as $type => $method) {
                $httpMethod[mb_strtolower($type)] = ltrim($method, '@');
            }

            Config::set('httpMethod', $httpMethod);
        }

        try {
            self::setConfigIfExists('parameters');
        } catch (Exception $e) {
            throw new Exception(
                Message::get("ROUTER_INVALID_ENDPOINT_PARAMETERS"),
                [
                    str_replace(PROJECT_DIR.'/', '', self::APP_CONFIG_FILE_ROUTES),
                    $e->getData('testedValue'),
                ]
            );
        }

        try {
            self::setConfigIfExists('viewFile');
        } catch (Exception $e) {
            throw new Exception(
                Message::get("ROUTER_INVALID_VIEW_FILE"),
                [
                    str_replace(PROJECT_DIR.'/', '', self::APP_CONFIG_FILE_ROUTES),
                    $e->getData('testedValue'),
                ]
            );
        }
    }

    private static function resolveEndpoints(): void
    {
        if (Config::get('output') !== 'file' and count(preg_grep('/^@main(:|$)/', array_keys(self::$routeData))) == 0) {
            throw new Exception(
                Message::get("ROUTER_MAIN_METHOD_NOT_FOUND"),
                [
                    str_replace(PROJECT_DIR.'/', '', self::APP_CONFIG_FILE_ROUTES),
                    self::$nodeName,
                ]
            );
        }

        $methodName = 'main';

        foreach (self::$routeData as $key => $routeData) {
            if (strpos($key, '@') === 0) {
                $endpointMethodName = ltrim($key, '@');
                $urlPartAsMethod = self::$urlParts[0] ?? 'main';

                if ($urlPartAsMethod == $endpointMethodName) {
                    array_shift(self::$urlParts);
                    
                    $methodName = $endpointMethodName;
                    break;
                }
            }
        }

        self::$routeData = self::$routeData['@'.$methodName];

        self::$methodName = $methodName;
        self::configureEndpointProperties();
    }

    private static function resolveParameters(): void
    {
        $parametersValues = array_values(array_filter(explode('/', Config::get('parameters'))));

        if (count(self::$urlParts) > 0 and count($parametersValues) <= 0) {
            self::$isValidRoute = false;
        }

        foreach ($parametersValues as $parameterId) {
            if (strpos($parameterId, '?') !== 0 and count(self::$urlParts) <= 0) {
                self::$isValidRoute = false;
            }

            self::$parameters[ltrim($parameterId, '?')] = array_shift(self::$urlParts);
        }
    }

    private static function resolveNamespace(string $partValue): void
    {
        if (isset(self::$routeData['namespace'])) {
            self::$controllerNamespace = array_filter(explode('\\', self::$routeData['namespace']));
        }

        if (!self::$removedIndexFromNamespace and count(self::$controllerNamespace) > 2) {
            self::$removedIndexFromNamespace = true;

            self::$controllerNamespace = array_filter(self::$controllerNamespace, function($value) {
                return $value !== 'Index';
            });
        }

        self::$controllerName = $partValue === '' ? 'Index' : Tools::toPascalCase($partValue);

        self::$controllerNamespace[] = self::$controllerName;
    }

    /************************ */

    private static function removePrefixOnce(string $string, string $prefix): string
    {
        if (str_starts_with($string, $prefix) && (strlen($string) === strlen($prefix) || $string[strlen($prefix)] === '/')) {
            return substr($string, strlen($prefix));
        }

        return $string;
    }

    private static function setConfigIfExists(string $configName): bool
    {
        if (isset(self::$routeData[$configName])) {
            Config::set($configName, self::$routeData[$configName]);
            return true;
        }

        return false;
    }

    /************************ */

    public static function getUrlParts(): array
    {
        return self::$urlParts;
    }

    public static function getControllerName(): string
    {
        return self::$controllerName;
    }
    
    public static function getControllerNamespace(): array
    {
        return self::$controllerNamespace;
    }
    
    public static function getDynamicNodeValues(): array
    {
        return self::$dynamicNodes;
    }
    
    public static function getDynamicNode(string $key): string
    {
        return self::$dynamicNodes[$key];
    }
    
    public static function getMethodName(): string
    {
        return self::$methodName;
    }
    
    public static function isValidRoute(): bool
    {
        return self::$isValidRoute;
    }
    
    public static function getParameterValues(): array
    {
        return self::$parameters;
    }
    
    public static function getParameter(string $key): string
    {
        return self::$parameters[$key];
    }
    
    public static function getRootDir(): string
    {
        return self::$rootDir;
    }
}
