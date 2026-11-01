<?php
/**
 * -----------------------------------------------------------------------------
 * 📂
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                     ARQUIVO /galastri/core/Router.php                     ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 * xxx
 * ─────────────────────────────────────────────────────────────────────────────
 */



/**
 * 🟢 NAMESPACE DA CLASSE
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Router pertence ao núcleo do framework.
 */

namespace galastri\core;



/**
 * 🟢 CLASSES UTILIZADAS
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * xxx
 *
 * ┌─────────────┬─────────────────────────────────────────────────────────────┐
 * │ Classe      │ Uso                                                         │
 * ├─────────────┼─────────────────────────────────────────────────────────────┤
 * │ `Config`    │ xxx                                                         │
 * ├─────────────┼─────────────────────────────────────────────────────────────┤
 * │ `Exception` │ xxx                                                         │
 * ├─────────────┼─────────────────────────────────────────────────────────────┤
 * │ `Message`   │ xxx                                                         │
 * ├─────────────┼─────────────────────────────────────────────────────────────┤
 * │ `Tools`     │ xxx                                                         │
 * └─────────────┴─────────────────────────────────────────────────────────────┘
 */

use galastri\core\config\Config;
use galastri\extensions\Exception;
use galastri\language\Message;
use galastri\modules\Tools;



/**
 * 🟢 CLASSE Router
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * xxx
 */

final class Router
{
    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                              CONSTANTES                               ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     */
    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * _________________________________
     * @var xxx
     */
    
    private const APP_CONFIG_FILE_ROUTES = PROJECT_DIR.'/app/config/routes.php';

    
    
    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                             PROPRIEDADES                              ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     */

    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * _________________________________
     * @var xxx
     */
    
    private static string $rootDir;
    
    
    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * _________________________________
     * @var xxx
     */
    
    private static array $routeData;
    
    
    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * _________________________________
     * @var xxx
     */
    
    private static array $urlParts;
    
    
    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * _________________________________
     * @var xxx
     */
    
    private static string $nodeName = '';
    
    
    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * _________________________________
     * @var xxx
     */
    
    private static string $controllerName = '';
    
    
    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * _________________________________
     * @var xxx
     */
    private static array $controllerNamespace = ['app', 'controllers'];
    
    
    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * _________________________________
     * @var xxx
     */
    private static array $dynamicNodes = [];
    
    
    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * _________________________________
     * @var xxx
     */
    private static string $methodName = 'main';
    
    
    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * _________________________________
     * @var xxx
     */
    private static bool $isValidRoute = true;
    
    
    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * _________________________________
     * @var xxx
     */
    private static bool $removedIndexFromNamespace = false;
    
    
    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * _________________________________
     * @var xxx
     */
    private static array $parameters = [];
    
    
    
    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                                MÉTODOS                                ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     */
    
    /**
     * 🟢 CLASSE ESTÁTICA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Os recursos desta classe são acessados diretamente por chamadas como
     * `Router::run()`, sem precisar criar um objeto.
     *
     * Por isso, deixamos o construtor privado para impedir que a classe seja
     * instanciada com `new Router()`.
     */

    private function __construct() {}

    
    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * _________________________________
     * @return void
     */
    
    public static function run(): void
    {
        self::$routeData = Config::importConfig(self::APP_CONFIG_FILE_ROUTES);

        self::configureRootDir();
        self::setUrlParts();
        self::resolveNodes();
        self::configureNodeProperties();
        self::resolveEndpoints();
        self::resolveParameters();
    }

    
    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * _________________________________
     * @return void
     */
    
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
   
   
   
    /**
    * 🟢 xxx
    * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    * xxx
    *
    *
    * _________________________________
    * @return void
    */
    
    private static function setUrlParts(): void
    {
        $requestPath = explode('?', $_SERVER['REQUEST_URI'], 2)[0];
        $parts = self::removePrefixOnce($requestPath, self::$rootDir);

        $urlParts = [''];

        foreach (explode('/', $parts) as $partValue) {
            if (!is_null($partValue) && $partValue !== '') {
                $urlParts[] = $partValue;
            }
        }

        self::$urlParts = $urlParts;
    }
    
    
    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * _________________________________
     * @return void
     */
    
    private static function resolveNodes(): void
    {
        $partValue = self::$urlParts[0] ?? null;

        if ($partValue === null) {
            return;
        }

        $routeName = $partValue === '' ? self::$rootDir : '/'.$partValue;

        if (array_key_exists($routeName, self::$routeData)) {
            self::$routeData = self::$routeData[$routeName];
            self::$nodeName = $routeName;
            self::processNodeData($partValue);
            return;
        }

        if (array_key_exists('@'.$partValue, self::$routeData)) {
            return;
        }

        foreach (self::$routeData as $key => $routeData) {
            if (strpos($key, '/?') === 0) {
                self::$routeData = $routeData;
                self::$nodeName = $key;
                self::$dynamicNodes[ltrim($key, '/?')] = $partValue;
                self::processNodeData($key);
                return;
            }
        }
    }
    
    
    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * _________________________________
     * @return void
     */
    
    private static function processNodeData(string $className): void
    {
        array_shift(self::$urlParts);

        self::configureRouteProperties();
        self::resolveNamespace($className);
        self::resolveNodes();
    }
    
    
    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * _________________________________
     * @return void
     */
    
    private static function configureRouteProperties(): void
    {
        self::setConfigIfExists('displayErrors', false);
        self::setConfigIfExists('stopOnNonFatalErrors', true);
        // self::setConfigIfExists('useErrorHandler', true);
        self::setConfigIfExists('showTrace', false);
        // self::setConfigIfExists('performanceAnalysis', false);
        self::setConfigIfExists('createLogsOnError', true);

        self::setConfigIfExists('projectName');
        self::setConfigIfExists('title');
        self::setConfigIfExists('output');
        self::setConfigIfExists('timezone');
        self::setConfigIfExists('offline');
        self::setConfigIfExists('offlineRedirectTo');
        self::setConfigIfExists('offlineMessage');
        self::setConfigIfExists('authTag');
        self::setConfigIfExists('authFailRedirectTo');
        self::setConfigIfExists('authFailMessage');
        self::setConfigIfExists('notFoundRedirectTo');
        self::setConfigIfExists('notFoundMessage');
        self::setConfigIfExists('forceRedirectTo');

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
        self::setConfigIfExists('forceMimeType');
        self::setConfigIfExists('includeProjectMetadata');
    }
    
    
    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * _________________________________
     * @return void
     */
    
    private static function configureNodeProperties(): void
    {
        if(self::setConfigIfExists('controller')) {
            self::$controllerName = self::$routeData['controller'];
            
            array_pop(self::$controllerNamespace);

            self::$controllerNamespace[] = self::$routeData['controller'];
        }
    }
    
    
    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * _________________________________
     * @return void
     */
    
    private static function configureEndpointProperties(): void
    {
        if (self::setConfigIfExists('method') && self::$routeData['method'] !== '') {
            self::$methodName = ltrim(self::$routeData['method'], '@');
        }

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
    
    
    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * _________________________________
     * @return void
     */
    
    private static function resolveEndpoints(): void
    {
        if (Config::get('output') !== 'file' && count(preg_grep('/^@main(:|$)/', array_keys(self::$routeData))) == 0) {
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

        if (Config::get('output') === 'file' && !isset(self::$routeData['@'.$methodName])) {
            return;
        }
        
        self::$routeData = self::$routeData['@'.$methodName];
        self::configureRouteProperties();
        
        self::$methodName = Tools::toCamelCase($methodName);
        self::configureEndpointProperties();
    }
    
    
    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * _________________________________
     * @return void
     */
    
    private static function resolveParameters(): void
    {
        $parametersValues = array_values(array_filter(explode('/', Config::get('parameters'))));

        if (count(self::$urlParts) > 0 && count($parametersValues) <= 0) {
            self::$isValidRoute = false;
        }

        foreach ($parametersValues as $parameterId) {
            if (strpos($parameterId, '?') !== 0 && count(self::$urlParts) <= 0) {
                self::$isValidRoute = false;
            }

            self::$parameters[ltrim($parameterId, '?')] = array_shift(self::$urlParts);
        }

        if (self::$urlParts !== []) {
            self::$isValidRoute = false;
        }
    }
    
    
    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     * 
     * _________________________________
     * @param xxx $xxx  
     *    xxx
     * _________________________________
     * @return void
     */
    
    private static function resolveNamespace(string $partValue): void
    {
        if (isset(self::$routeData['namespace'])) {
            self::$controllerNamespace = array_filter(explode('\\', self::$routeData['namespace']));
        }

        if (!self::$removedIndexFromNamespace && count(self::$controllerNamespace) > 2) {
            self::$removedIndexFromNamespace = true;

            self::$controllerNamespace = array_filter(self::$controllerNamespace, function($value) {
                return $value !== 'Index';
            });
        }

        self::$controllerName = $partValue === '' ? 'Index' : Tools::toPascalCase($partValue);

        self::$controllerNamespace[] = self::$controllerName;
    }

    
    
    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * _________________________________
     * @param xxx $xxx  
     *    xxx
     * 
     * @param xxx $xxx  
     *    xxx
     * _________________________________
     * @return string
     *    xxx
     */

    private static function removePrefixOnce(string $string, string $prefix): string
    {
        if (str_starts_with($string, $prefix) && (strlen($string) === strlen($prefix) || $string[strlen($prefix)] === '/')) {
            return substr($string, strlen($prefix));
        }

        return $string;
    }

    
    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * _________________________________
     * @param xxx $xxx  
     *    xxx
     * 
     * @param xxx $xxx  
     *    xxx
     * _________________________________
     * @return bool
     *    xxx
     */
    
    private static function setConfigIfExists(string $configName /*, [1]*/): bool
    {
        if (isset(self::$routeData[$configName])) {
            Config::set($configName, self::$routeData[$configName]);
            return true;
        }
        
        if (isset(func_get_args()[1]) && Config::notExists($configName)) {
            Config::set($configName, func_get_args()[1]);
            return true;
        }

        return false;
    }

    
    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * _________________________________
     * @return array
     *    xxx
     */

    public static function getUrlParts(): array
    {
        return self::$urlParts;
    }
    
    
    
    /**
    * 🟢 xxx
    * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    * xxx
    *
    *
    * _________________________________
    * @return string
    *    xxx
    */
    
    public static function getControllerName(): string
    {
        return self::$controllerName;
    }
    
    
    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * _________________________________
     * @return array
     *    xxx
     */
    
    public static function getControllerNamespace(): array
    {
        return self::$controllerNamespace;
    }
    
    
    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * _________________________________
     * @return array
     *    xxx
     */
    
    public static function getDynamicNodeValues(): array
    {
        return self::$dynamicNodes;
    }
    
    
    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * _________________________________
     * @param xxx $xxx  
     *    xxx
     * _________________________________
     * @return string
     *    xxx
     */
    
    public static function getDynamicNode(string $key): string
    {
        return self::$dynamicNodes[$key];
    }
    
    
    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * _________________________________
     * @return string
     *    xxx
     */
    
    public static function getMethodName(): string
    {
        return self::$methodName;
    }
    
    
    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * _________________________________
     * @return bool
     *    xxx
     */
    
    public static function isValidRoute(): bool
    {
        return self::$isValidRoute;
    }
    
    
    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * _________________________________
     * @return array
     *    xxx
     */
    
    public static function getParameterValues(): array
    {
        return self::$parameters;
    }
    
    
    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * _________________________________
     * @param xxx $xxx  
     *    xxx
     * _________________________________
     * @return null|string
     *    xxx
     */
    
    public static function getParameter(string $key): ?string
    {
        return self::$parameters[$key] ?? null;
    }
    
    
    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * _________________________________
     * @return string
     *    xxx
     */
    
    public static function getRootDir(): string
    {
        return self::$rootDir;
    }
    
    
    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * _________________________________
     * @return string
     *    xxx
     */
    
    public static function getBaseHref(): string
    {
        return rtrim(self::$rootDir, '/').'/';
    }
}
