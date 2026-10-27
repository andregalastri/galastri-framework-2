<?php
/**
 * -----------------------------------------------------------------------------
 * 📂
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                    ARQUIVO /galastri/core/Galastri.php                    ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 * Este arquivo define a classe que coordena o processamento da requisição.
 * 
 * Depois que o bootstrap prepara os recursos básicos, Galastri assume a
 * condução das próximas etapas.
 * 🔗 LINK: /galastri/bootstrap/bootstrap.php
 *
 * Aqui preparamos as configurações, verificamos a rota e a autenticação,
 * identificamos o controller que será executado e chamamos a classe responsável
 * por apresentar a resposta.
 * ─────────────────────────────────────────────────────────────────────────────
 */



/**
 * 🟢 NAMESPACE DA CLASSE
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Galastri pertence ao núcleo do framework.
 */

namespace galastri\core;



/**
 * 🟢 CLASSES UTILIZADAS
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Para conduzir o processamento, precisamos consultar as configurações,
 * identificar o destino da requisição e verificar se podemos continuar.
 * Estas classes fornecem os recursos usados em cada uma dessas etapas.
 *
 * ┌──────────────────┬───────────────────────────────────────────────────────┐
 * │ Classe           │ Uso                                                   │
 * ├──────────────────┼───────────────────────────────────────────────────────┤
 * │ `Config`         │ Prepara as definições das configurações e permite     │
 * │                  │ consultar os valores durante o processamento.         │
 * ├──────────────────┼───────────────────────────────────────────────────────┤
 * │ `Router`         │ Interpreta a URL, identifica a rota e fornece os      │
 * │                  │ nomes do controller e do método que serão usados.     │
 * ├──────────────────┼───────────────────────────────────────────────────────┤
 * │ `Exception`      │ Permite lançar as falhas encontradas nas verificações │
 * │                  │ que fazemos antes de executar o controller.           │
 * ├──────────────────┼───────────────────────────────────────────────────────┤
 * │ `Message`        │ Fornece as mensagens padronizadas usadas nas          │
 * │                  │ exceções do framework.                                │
 * ├──────────────────┼───────────────────────────────────────────────────────┤
 * │ `Authentication` │ Verifica a autenticação quando a opção `authTag` está │
 * │                  │ preenchida nas configurações da rota.                 │
 * ├──────────────────┼───────────────────────────────────────────────────────┤
 * │ `Redirect`       │ Redireciona para outro endereço quando alguma das     │
 * │                  │ configurações determina redirecionamento.             │
 * ├──────────────────┼───────────────────────────────────────────────────────┤
 * │ `Tools`          │ Ajuda a preparar o nome da classe de saída e o        │
 * │                  │ caminho do controller usado nas mensagens de erro.    │
 * └──────────────────┴───────────────────────────────────────────────────────┘
 */

use galastri\core\config\Config;
use galastri\core\Router;
use galastri\extensions\Exception;
use galastri\language\Message;
use galastri\modules\Authentication;
use galastri\modules\Redirect;
use galastri\modules\Tools;



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

        self::setTimezone();
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

    private static function setTimezone(): void
    {
        $timezone = Config::get('timezone');

        if ($timezone !== false && $timezone !== '') {
            date_default_timezone_set($timezone);
        }
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

        http_response_code(404);
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

        if ($authTag !== '' && !Authentication::validate($authTag)) {
            if ($authFailRedirectTo !== '' && $output != 'json') {
                Redirect::to($authFailRedirectTo);
            }
            
            $exception = new Exception(
                $authFailMessage
            );

            $exception->errorSource = 'authentication';

            throw $exception;
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
