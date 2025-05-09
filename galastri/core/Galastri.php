<?php
/**
 * Documentação:
 * https://github.com/andregalastri/galastri-framework-2/wiki/Classe-Galastri
 */

namespace galastri\core;

use galastri\core\Router;
use galastri\core\config\Config;
use galastri\extensions\Exception;
use galastri\language\Message;
use galastri\modules\Tools;
use galastri\modules\VarDump;
use galastri\modules\Redirect;
use galastri\modules\Authentication;
// use galastri\modules\PerformanceAnalysis;

final class Galastri
{
    private const MODULE_CONTROLLER = 'galastri\modules\Controller';
    private const OUTPUT_NAMESPACE = 'galastri\core\output';

    private static string $controllerNamespace = '';
    private static string $controllerMethodName = '';
    private static string $controllerHttpMethodName = '';

    private static bool $controllerExists = true;
    private static bool $controllerMethodExists = true;
    private static bool $controllerHttpMethodExists = false;

    private static string $output;
    private static ?object $controller = null;

    private function __construct() {}


    public static function run(): void
    {
        Config::run();
        Router::run();

        self::setOutputClass();
        self::checkForceRedirectTo();
        self::checkOffline();
        self::isValidRoute();
        self::checkAuthentication();
        self::setControllerNamespace();
        self::setMethodName();
        self::setHttpMethodName();

        if (self::controllerExists()) {
            self::executeController();
        }

        self::executeOutput();
    }

    private static function checkForceRedirectTo(): void
    {
        $forceRedirectTo = Config::get('forceRedirectTo');

        if ($forceRedirectTo !== false && $forceRedirectTo !== '') {
            Redirect::to($forceRedirectTo);
        }
    }

    private static function checkOffline(): void
    {
        $offline = Config::get('offline');
        $offlineRedirectTo = Config::get('offlineRedirectTo');
        $offlineMessage = Config::get('offlineMessage');

        if ($offline) {

            if ($offlineRedirectTo !== '') {
                Redirect::to($offlineRedirectTo);
            }

            throw new Exception(
                $offlineMessage
            );
        }
    }

    private static function isValidRoute(): void
    {
        if (!Router::isValidRoute() && self::$output::requiresController()) {
            self::notFound();
        }
    }

    public static function notFound(): void
    {
        $output = Config::get('output');
        $notFoundRedirectTo = Config::get('notFoundRedirectTo');
        $notFoundMessage = Config::get('notFoundMessage');

        if ($notFoundRedirectTo !== '' && $output != 'json') {
            Redirect::to($notFoundRedirectTo);
        }

        header("HTTP/1.0 404 Not Found");
        throw new Exception(
            $notFoundMessage
        );
    }

    private static function checkAuthentication(): void
    {
        $authTag = Config::get('authTag');
        $authFailRedirectTo = Config::get('authFailRedirectTo');
        $authFailMessage = Config::get('authFailMessage');
        $output = Config::get('output');

        if (!empty($authTag) && !Authentication::validate($authTag)) {
            if ($authFailRedirectTo !== '' && $output != 'json') {
                Redirect::to($authFailRedirectTo);
            }

            throw new Exception(
                $authFailMessage
            );
        }
    }

    private static function setOutputClass(): void
    {
        $output = Tools::toPascalCase(Config::get('output'));
        $outputClass = self::OUTPUT_NAMESPACE.'\\'.$output;
        
        self::$output = $outputClass;
    }

    private static function setControllerNamespace(): void
    {
        self::$controllerNamespace = implode('\\', Router::getControllerNamespace());

        if (!class_exists(self::$controllerNamespace)) {
            $controllerLocation = implode('/', Tools::deleteLastKey(Router::getControllerNamespace()));

            if (self::$output::requiresController()) {
                throw new Exception(
                    Message::get("CONTROLLER_NOT_FOUND"),
                    [
                        self::$controllerNamespace,
                        $controllerLocation,
                    ]
                );
            }

            self::$controllerExists = false;

        } elseif (!is_subclass_of(self::$controllerNamespace, self::MODULE_CONTROLLER)) {

            throw new Exception(
                Message::get("CONTROLLER_MUST_EXTEND_MODULE"),
                [
                    self::$controllerNamespace,
                ]
            );
        }
    }

    private static function setMethodName(): void
    {
        if (!self::controllerExists()) {
            self::$controllerMethodExists = false;
            return;
        }

        self::$controllerMethodName = Router::getMethodName();
        
        if(!method_exists(self::$controllerNamespace, self::$controllerMethodName)) {
        
            throw new Exception(
                Message::get("CONTROLLER_METHOD_NOT_FOUND"),
                [
                    self::$controllerNamespace,
                    self::$controllerMethodName.'()',
                ]
            );
        }
    }

    private static function setHttpMethodName(): void
    {
        if (!self::controllerExists()) {
            return;
        }

        $httpMethodList = Config::get('httpMethod');
        $serverHttpMethod = mb_strtolower($_SERVER['REQUEST_METHOD']);

        if (!empty($httpMethodList) && array_key_exists($serverHttpMethod, $httpMethodList)) {

            self::$controllerHttpMethodName = $httpMethodList[$serverHttpMethod];
            self::$controllerHttpMethodExists = true;

            if(!method_exists(self::$controllerNamespace, self::$controllerHttpMethodName)) {
                throw new Exception(
                    Message::get("CONTROLLER_METHOD_NOT_FOUND"),
                    [
                        self::$controllerNamespace,
                        self::$controllerHttpMethodName.'()',
                    ]
                );
            }
        }
    }

    private static function executeController(): void
    {
        self::$controller = new self::$controllerNamespace();
    }

    private static function executeOutput(): void
    {
        self::$output::run();
    }

    public static function controllerExists(): bool
    {
        return self::$controllerExists;
    }

    public static function methodExists(): bool
    {
        return self::$controllerMethodExists;
    }

    public static function httpMethodExists(): bool
    {
        return self::$controllerHttpMethodExists;
    }

    public static function getControllerResponse(): array
    {
        return self::$controller->getResponse();
    }

    public static function getControllerFileContents(): array
    {
        return self::$controller->getFileContents();
    }

    public static function getControllerNamespace(): string
    {
       return self::$controllerNamespace;
    }

    public static function getControllerMethodName(): string
    {
       return self::$controllerMethodName;
    }

    public static function getControllerHttpMethodName(): string
    {
       return self::$controllerHttpMethodName;
    }
}
