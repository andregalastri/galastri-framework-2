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



/**
 * 🟢 CLASSE Galastri
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * A classe Galastri coordena o processamento da requisição depois que o
 * bootstrap prepara o ambiente. Ela organiza a sequência: carregar
 * configurações, identificar a rota, fazer as verificações necessárias,
 * preparar o controller e chamar a saída.
 *
 * Ela existe para reunir essa sequência em um lugar. Cada classe cuida de sua
 * parte, enquanto Galastri define a ordem em que esses trabalhos acontecem.
 */

final class Galastri
{
    
    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                              CONSTANTES                               ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     *
     *
     *
     * 🟢 NAMESPACE DO CONTROLLER
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Armazena o nome completo da classe base dos controllers.
     *
     * Os controllers criados na aplicação precisam herdar dessa classe. Por
     * isso, antes de instanciar um controller, `Galastri` precisa conferir se
     * essa herança existe.
     *
     * A constante fornece a referência usada nessa comparação: "esse controller
     * é uma classe derivada de galastri\modules\Controller?". Se não for, o
     * framework interrompe o processamento com uma exceção.
     *
     *
     * ---
     * @var string
     */
    
    private const MODULE_CONTROLLER = 'galastri\modules\Controller';
    
    
    
    /**
     * 🟢 NAMESPACE DA SAÍDA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Armazena o namespace em que ficam as classes responsáveis pelas saídas.
     *
     * Aqui não estamos identificando uma classe específica, porque a saída
     * depende da configuração da rota. Guardamos apenas a parte comum do nome.
     * Depois, juntamos esse namespace ao nome da saída escolhida.
     * 
     * ✔️ EXEMPLO
     * ┌───────────────────────────────────────────────────────────────────────┐
     * │ Se a configuração de saída indicar `json`, o framework prepara o nome │
     * │ `Json` e monta `galastri\core\output\Json`, que identifica a classe   │
     * │ que será usada como classe de saída.                                  │
     * └───────────────────────────────────────────────────────────────────────┘
     *
     *
     * ---
     * @var string
     */
    
    private const OUTPUT_NAMESPACE = 'galastri\core\output';
    
    
    
    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                             PROPRIEDADES                              ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     *
     *
     *
     * 🟢 NOME COMPLETO DO CONTROLLER
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Armazena o nome completo da classe do controller que atenderá a
     * requisição, formado pelo namespace + nome da classe.
     *
     * ✔️ EXEMPLO
     * ┌───────────────────────────────────────────────────────────────────────┐
     * │ Uma classe controller `app\controllers\Clientes`. Esse valor inclui   │
     * │ tanto o namespace quanto o nome da classe.                            │
     * └───────────────────────────────────────────────────────────────────────┘
     *
     * Depois que `Router` identifica o controller, `Galastri` monta esse nome
     * completo e o armazena aqui. Vamos precisamos disso em vários momentos:
     *
     * - Pra verificar se a classe existe;
     * - Conferir se ela herda da classe base Controller;
     * - Procurar os métodos que serão utilizados e, depois, criar sua
     *   instância.
     *
     * Essa propriedade, portanto, acompanha o controller desde as verificações
     * até sua criação. Guardar o nome permite que essas etapas trabalhem com a
     * mesma classe, sem precisar montar essa informação novamente a cada uso.
     *
     *
     * ---
     * @var string
     */
    
    private static string $controllerNamespace = '';
    
    
    
    /**
     * 🟢 NOME DO MÉTODO DA ROTA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Armazena o nome do método do controller escolhido para atender a rota.
     * Pode ser `main`, por exemplo, ou outro nome definido conforme a rota
     * acessada.
     *
     * Quem identifica esse método é o `Router`. `Galastri` recebe o nome,
     * guarda nesta propriedade e verifica se o método existe no controller.
     *
     * Mas guardar o nome e conferir sua existência ainda não executa o método.
     * A execução acontece depois, quando criamos a instância do controller. O
     * construtor da classe base `Controller` consulta esse nome através de
     * `Galastri::getControllerMethodName()` e chama o método correspondente.
     *
     * É por isso que o nome fica guardado aqui propriedade: ele precisa
     * continuar disponível depois da verificação, para que o `Controller` saiba
     * qual método chamar.
     *
     * Galastri prepara essa escolha, e `Controller` a utiliza para executar o
     * código da aplicação.
     *
     *
     * ---
     * @var string
     */
    
    private static string $controllerMethodName = '';
    
    

    /**
     * 🟢 NOME DO MÉTODO HTTP DA REQUISIÇÃO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Armazena o nome de um método do controller que será chamado conforme o
     * tipo da requisição HTTP recebida, como GET ou POST.
     *
     * ✔️ EXEMPLO
     * ┌───────────────────────────────────────────────────────────────────────┐
     * │ Imagine uma rota que usa `main()` e que, na configuração              │
     * │ `httpMethod`, associa apenas POST ao método salvar(). Queremos que    │
     * │ `main()` seja executado ao acessar essa rota e que `salvar()` seja    │
     * │ chamado também quando o acesso ocorrer por POST.                      │
     * │                                                                       │
     * │ Nesse exemplo, o comportamento seria:                                 │
     * │                                                                       │
     * │ - Uma requisição GET executa apenas main().                           │
     * │                                                                       │
     * │ - Uma requisição POST executa `main()` e, depois, `salvar()`.         │
     * └───────────────────────────────────────────────────────────────────────┘
     *
     * Quando o controller é instanciado, o construtor da classe base executa
     * primeiro o método da rota. Em seguida, consulta
     * `Galastri::getControllerHttpMethodName()` para obter o nome do método
     * adicional armazenado aqui e chamá-lo se a associação for válida.
     *
     * 
     * ---
     * @var string
     */
    
    private static string $controllerHttpMethodName = '';

    
    
    /**
     * 🟢 EXISTÊNCIA DO CONTROLLER
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Indica se temos um controller para usar nesta requisição.
     *
     * Ela começa em `true`. Durante a preparação, verificamos se a classe do
     * controller existe. Se não existir, precisamos considerar se a saída
     * escolhida exige um controller. Atualmente apenas a saída `file` não
     * exige.
     *
     * Quando podemos continuar sem controller, mudamos essa propriedade para
     * `false`. `Galastri` consulta esse valor para pular a preparação dos
     * métodos e a criação da instância. A saída `File` também o consulta antes
     * de tentar obter conteúdo fornecido pelo controller.
     *
     * Se a saída exigir um controller, sua ausência gera uma exceção. Nesse
     * caso, a execução não prossegue.
     *
     *
     * ---
     * @var bool
     */
    
    private static bool $controllerExists = true;
    
    
    
    /**
     * 🟢 EXISTÊNCIA DO MÉTODO HTTP DA REQUISIÇÃO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Indica se há uma associação entre o método HTTP da requisição atual e um
     * método adicional do controller.
     *
     * Ela começa em `false`, porque essa chamada adicional é opcional. Durante
     * a preparação, consultamos a configuração `httpMethod`. Se houver uma
     * associação para o tipo de requisição recebido, guardamos o nome do método
     * e mudamos esse indicador para `true`.
     * 
     * ✔️ EXEMPLO
     * ┌───────────────────────────────────────────────────────────────────────┐
     * │ Por exemplo, se apenas POST estiver associado a `salvar()`, uma       │
     * │ requisição GET mantém o indicador em `false`.                         │
     * │                                                                       │
     * │ Apenas uma requisição POST faz o indicador passar para `true`. Neste  │
     * │ caso também verificamos se `salvar()` existe no controller; se não    │
     * │ existir, lançamos uma exceção.                                        │
     * └───────────────────────────────────────────────────────────────────────┘
     *
     * Depois, durante a ins Controller consulta esse valor através de
     * Galastri::httpMethodExists(). É essa consulta que decide se haverá a
     * chamada adicional, depois da execução do método da rota.
     *
     *
     * ---
     * @var bool
     */
    
    private static bool $controllerHttpMethodExists = false;

    
    
    /**
     * 🟢 NOME DA CLASSE DE SAÍDA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Armazena o nome completo da classe responsável pela saída da requisição.
     * Para a saída `json`, por exemplo, o valor armazenado será
     * galastri\core\output\Json.
     *
     * Esse nome é preparado por `setOutputClass()`, a partir da configuração
     * `output` da rota. Depois, usamos essa referência para consultar se a
     * saída exige um controller, através do método `requiresController()`, e
     * para chamar seu método `run()` ao final do processamento.
     *
     * Ela é uma string porque precisamos guardar o nome da classe para fazer
     * chamadas estáticas. Os dados que serão enviados na resposta ficam em
     * outro lugar; aqui apenas identificamos qual classe vai cuidar dessa
     * resposta.
     *
     *
     * ---
     * @var string
     */
    
    private static string $output;
    
    
    
    /**
     * 🟢 INSTÂNCIA DA CLASSE CONTROLLER
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Armazena a instância do controller criado para atender à requisição.
     *
     * Até então, temos o nome da classe guardado em `$controllerNamespace`.
     * Quando `executeController()`é executado ele usa esse nome para criar um
     * objeto dessa classe e guardamos o objeto nesta propriedade.
     *
     * Durante essa criação, o construtor da classe base `Controller` executa os
     * métodos da aplicação e reúne seus retornos. Precisamos manter a instância
     * disponível porque, depois, a classe de saída vai precisar consultar os
     * dados que foram preparados.
     *
     * É para isso que `Galastri::getControllerResponse()` usa essa propriedade:
     * ele acessa o controller guardado aqui e obtém sua resposta. Da mesma
     * forma, `getControllerFileContents()` permite recuperar o conteúdo de
     * arquivo que o controller tenha preparado.
     *
     * Inicializamos ela em `null`, pois ainda não criamos nenhum controller até
     * o momento. Se a requisição puder continuar sem controller, como pode
     * acontecer na saída `file`, a propriedade permanece em null.
     */
    private static ?object $controller = null;

    
    
    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                                MÉTODOS                                ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     *
     *
     *
     * 🟢 CLASSE ESTÁTICA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Os recursos desta classe são acessados diretamente por chamadas como
     * `Galastri::run()`, sem precisar criar um objeto.
     *
     * Por isso, deixamos o construtor privado para impedir que a classe seja
     * instanciada com `new Galastri()`.
     */
    
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
    
    

    /**
     * 🟢 DEFINIÇÃO DO FUSO HORÁRIO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Define o fuso horário padrão que o PHP vai usar durante a requisição.
     * Assim, as operações de data e hora seguem o fuso escolhido nas
     * configurações da aplicação.
     *
     * Por exemplo, se configurarmos `America/Sao_Paulo`, uma chamada como
     * date('H:i') vai apresentar a hora nesse fuso. Isso evita depender do fuso
     * que definido nas configurações do PHP, que pode ser diferente daquele que
     * queremos usar na aplicação.
     *
     * Esse ajuste acontece no começo do processamento, antes de executar o
     * controller, para que o código da aplicação já esteja configurado com o
     * fuso quando precisar trabalhar com datas e horários.
     *
     *
     * ---
     * @return void
     */
    
    private static function setTimezone(): void
    {
        /**
         * O método recupera o valor da configuração de rota `timezone`.
         */
        $timezone = Config::get('timezone');
        
        
        
        /**
         * Testa-se se o valor da configuração é diferente de `false` e de uma
         * string vazia. Caso seja, então aplica esse fuso usando a função
         * nativa `date_default_timezone_set()`.
         *
         * Do contrário, não faz nenhuma alteração e o PHP continua usando o
         * fuso que estiver definido nas configurações internas.
         */
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
        throw new Exception($notFoundMessage);
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
