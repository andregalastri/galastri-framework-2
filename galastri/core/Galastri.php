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
     */

    /**
     * 🟢 CLASSE BASE DOS CONTROLLERS
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
     * _________________________________
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
     * _________________________________
     * @var string
     */

    private const OUTPUT_NAMESPACE = 'galastri\core\output';



    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                             PROPRIEDADES                              ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     */

    /**
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
     * completo e o armazena aqui. Precisamos disso em vários momentos:
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
     * _________________________________
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
     * É por isso que o nome fica guardado nesta propriedade: ele precisa
     * continuar disponível depois da verificação, para que o `Controller` saiba
     * qual método chamar.
     *
     * Galastri prepara essa escolha, e `Controller` a utiliza para executar o
     * código da aplicação.
     *
     *
     * _________________________________
     * @var string
     */

    private static string $controllerMethodName = '';



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
     * _________________________________
     * @var bool
     */

    private static bool $controllerExists = true;



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
     * _________________________________
     * @var string
     */

    private static string $output;



    /**
     * 🟢 INSTÂNCIA DA CLASSE CONTROLLER
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Armazena a instância do controller criado para atender à requisição.
     *
     * Até então, temos o nome da classe guardado em `$controllerNamespace`.
     * Quando `executeController()` é executado, ele usa esse nome para criar um
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
     */

    /**
     * 🟢 CLASSE ESTÁTICA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Os recursos desta classe são acessados diretamente por chamadas como
     * `Galastri::run()`, sem precisar criar um objeto.
     *
     * Por isso, deixamos o construtor privado para impedir que a classe seja
     * instanciada com `new Galastri()`.
     */

    private function __construct() {}

    
    
    /**
     * 🟢 RODANDO O FRAMEWORK
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * O run() reúne a sequência de trabalho que Galastri precisa conduzir para
     * atender à requisição. É aqui que definimos a ordem das etapas, desde a
     * preparação das configurações até a execução da saída. Cada método chamado
     * cuida de uma parte desse trabalho.
     *
     * O papel de run() é manter essa sequência explícita. Os detalhes do que
     * cada parte faz ficam nos métodos responsáveis por cada etapa; aqui
     * conseguimos acompanhar como essas partes trabalham juntas.
     *
     *
     * _________________________________
     * @return void
     */

    public static function run(): void
    {
        /**
         * Começamos com Config::run() e Router::run(), pois precisamos preparar
         * as configurações e identificar a rota antes de decidir como atender
         * ao acesso. A rota determina informações que usaremos nas próximas
         * etapas, como a saída escolhida, o controller e o método que será
         * chamado.
         *
         * Aproveite para ver sobre as classes `Config` e `Router` agora e
         * depois volte aqui:
         *
         * 🔗 LINK: /galastri/core/config/Config.php  
         * 🔗 LINK: /galastri/core/Router.php  
         */

        Config::run();
        Router::run();



        /**
         * Com essa preparação concluída, aplicamos o fuso horário e
         * identificamos a classe de saída. Precisamos conhecer essa classe
         * desde já porque algumas verificações dependem dela: a ausência de um
         * controller, por exemplo, pode ser permitida quando a saída consegue
         * entregar um arquivo diretamente do disco.
         */

        self::setTimezone();
        self::setOutputClass();



        /**
         * Em seguida, verificamos se a requisição pode prosseguir.
         *
         * Existem situações que podem interromper o processamento antes de
         * chegarmos ao código da aplicação.
         *
         * Aqui, as possibilidades de interrupção são:
         * - Redirecionamento forçado na configuração.
         * - Modo offline ativado.
         * - Rota inválida.
         * - Falha de autenticação.
         */

        self::checkForceRedirectTo();
        self::checkOffline();
        self::isValidRoute();
        self::checkAuthentication();



        /**
         * Aqui preparamos o controller: identificamos sua classe, conferimos a
         * herança exigida pelo framework e verificamos o método que
         * pretendemos executar.
         *
         * Fazemos isso antes de criar o objeto porque seu construtor já inicia
         * a chamada ao método da aplicação. Por isso, queremos chegar a esse
         * momento com essa preparação concluída.
         */
        self::checkControllerNamespace();
        self::setMethodName();



        /**
         * Quando há um controller disponível, chamamos `executeController()`.
         * Durante essa chamada, o controller executa o método da rota e prepara
         * os resultados que serão consultados pela saída.
         */
        if (self::controllerExists()) {
            self::executeController();
        }



        /**
         * Por fim, chamamos `executeOutput()`. Essa etapa fica fora da condição
         * `controllerExists()` porque também precisamos produzir uma resposta
         * quando seguimos sem controller, já que alguns formatos de saída podem
         * não exigir um controller para funcionar.
         */
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
     * que está definido nas configurações do PHP, que pode ser diferente
     * daquele que queremos usar na aplicação.
     *
     * Esse ajuste acontece no começo do processamento, antes de executar o
     * controller, para que o código da aplicação já esteja configurado com o
     * fuso quando precisar trabalhar com datas e horários.
     *
     *
     * _________________________________
     * @return void
     */

    private static function setTimezone(): void
    {
        /**
         * O método recupera o valor da configuração de rota `timezone`.
         */

        $timezone = Config::get('timezone');



        /**
         * É testado se o valor da configuração é diferente de `false` e de uma
         * string vazia. Caso seja, então aplica esse fuso usando a função
         * nativa `date_default_timezone_set()`.
         *
         * Do contrário, não faz nenhuma alteração e o PHP continua usando o
         * fuso que já estava em uso.
         */

        if ($timezone !== false && $timezone !== '') {
            date_default_timezone_set($timezone);
        }
    }



    /**
     * 🟢 VERIFICANDO REDIRECIONAMENTO FORÇADO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Aqui verificamos se a configuração forceRedirectTo manda encaminhar o
     * acesso para outro endereço antes de continuar o processamento.
     *
     * ✔️ EXEMPLO
     * ┌───────────────────────────────────────────────────────────────────────┐
     * │ Podemos ter um endereço antigo que deve levar o visitante para uma    │
     * │ página nova. Essa configuração permite fazer isso sem precisar        │
     * │ executar um controller só para redirecionar.                          │
     * └───────────────────────────────────────────────────────────────────────┘
     *
     *
     * _________________________________
     * @return void
     */

    private static function checkForceRedirectTo(): void
    {
        /**
         * O método recupera o valor da configuração de rota `forceRedirectTo`.
         */

        $forceRedirectTo = Config::get('forceRedirectTo');



        /**
         * É testado se o valor da configuração é diferente de `false` e de uma
         * string vazia. Caso seja, então chama o módulo `Redirect::to()`
         * apontando para o destino configurado.
         *
         * Se não houver redirecionamento configurado, seguimos normalmente.
         *
         * ℹ️ OBSERVAÇÃO
         * ┌───────────────────────────────────────────────────────────────────┐
         * │ Essa chamada prepara o redirecionamento e encerra a execução.     │
         * └───────────────────────────────────────────────────────────────────┘
         */

        if ($forceRedirectTo !== false && $forceRedirectTo !== '') {
            Redirect::to($forceRedirectTo);
        }
    }



    /**
     * 🟢 VERIFICANDO ROTA OFFLINE
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Esse método verifica se precisamos suspender o atendimento normal da
     * requisição porque a configuração offline está ativada. Isso pode ser útil
     * durante uma manutenção, por exemplo.
     *
     *
     * _________________________________
     * @return void
     */

    private static function checkOffline(): void
    {
        /**
         * Recuperamos três valores de configuração de rota: `offline`,
         * `offlineRedirectTo` e `offlineMessage`.
         */

        $offline = Config::get('offline');
        $offlineRedirectTo = Config::get('offlineRedirectTo');
        $offlineMessage = Config::get('offlineMessage');



        /**
         * Se offline estiver em `true`, temos dois caminhos:
         *
         * 1. Com `offlineRedirectTo` preenchido, encaminhamos o acesso para o
         *    endereço configurado, que pode ser uma página de manutenção, por
         *    exemplo.
         *
         * 2. Sem o redirecionamento, lançamos uma exceção com a mensagem de
         *    `offlineMessage`. O `ErrorHandler` assume a apresentação da falha,
         *    seguindo as configurações de exibição de erros.
         *
         *    🔗 LINK: /galastri/core/ErrorHandler.php
         *
         * Nos dois casos, impedimos que o processamento prossiga.
         */

        if ($offline) {

            if ($offlineRedirectTo !== '') {
                Redirect::to($offlineRedirectTo);
            }

            throw new Exception($offlineMessage);
        }
    }



    /**
     * 🟢 VERIFICANDO SE A ROTA É VÁLIDA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Quando chegamos aqui, o Router já interpretou a URL e informou se ela
     * corresponde a uma rota válida. Agora precisamos decidir se uma eventual
     * invalidade impede o prosseguimento da execução do código.
     *
     *
     * _________________________________
     * @return void
     */

    private static function isValidRoute(): void
    {
        /**
         * Para chamar `notFound()`, o método exige que duas condições sejam
         * verdadeiras ao mesmo tempo:
         *
         * 1. O `Router` considera a rota inválida.
         *
         * 2. A classe de saída exige um controller.
         *
         * Essa segunda condição existe por causa de saídas como file, que podem
         * entregar arquivos diretamente do disco.
         *
         * ✔️ EXEMPLO
         * ┌───────────────────────────────────────────────────────────────────┐
         * │ Imagine uma rota /arquivos configurada com essa saída e um acesso │
         * │ a /arquivos/manual.pdf. O trecho manual.pdf pode ficar disponível │
         * │ para a classe File procurar o arquivo.                            │
         * │                                                                   │
         * │ Para o Router, esse trecho restante pode tornar a rota inválida,  │
         * │ porque ele não corresponde a um método ou aos parâmetros          │
         * │ esperados. Mesmo assim, ainda podemos atender ao pedido           │
         * │ entregando o arquivo.                                             │
         * └───────────────────────────────────────────────────────────────────┘
         *
         * Por isso, quando a saída não exige controller, deixamos o
         * processamento continuar até as verificações próprias dessa saída. Se
         * o arquivo de nosso exemplo não existir, a classe `File` poderá chamar
         * `notFound()` depois.
         */

        if (!Router::isValidRoute() && self::$output::requiresController()) {
            self::notFound();
        }
    }



    /**
     * 🟢 TRATANDO O QUE NÃO FOI ENCONTRADO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Trata as situações em que a rota ou o arquivo solicitado não é encontrado
     * para que outras partes do framework possam recorrer à mesma lógica.
     *
     * Esse método é público porque também atende outras classes. A saída
     * `File`, por exemplo, o chama quando não encontra o arquivo solicitado.
     * Assim, tanto uma rota inválida quanto um arquivo ausente podem usar o
     * mesmo tratamento.
     *
     *
     * _________________________________
     * @return void
     */

    public static function notFound(): void
    {
        /**
         * Recuperamos três valores de configuração de rota: `output`,
         * `notFoundRedirectTo` e `notFoundMessage`.
         */

        $output = Config::get('output');
        $notFoundRedirectTo = Config::get('notFoundRedirectTo');
        $notFoundMessage = Config::get('notFoundMessage');



        /**
         * Primeiro, consultamos se existe um endereço em `notFoundRedirectTo`.
         * Se houver e a saída for diferente de `json`, redirecionamos para esse
         * endereço. Isso permite encaminhar o visitante para uma página
         * personalizada de "não encontrado".
         *
         * ⚠️ IMPORTANTE
         * ┌───────────────────────────────────────────────────────────────┐
         * │ Quando a saída é JSON, esse redirecionamento não é realizado, │
         * │ mesmo que esteja configurado.                                 │
         * │                                                               │
         * │ Isso precisa ser assim porque quem consome a resposta pode    │
         * │ receber a falha em JSON e tratá-la dentro do fluxo daquela    │
         * │ requisição.                                                   │
         * └───────────────────────────────────────────────────────────────┘
         */

        if ($notFoundRedirectTo !== '' && $output != 'json') {
            Redirect::to($notFoundRedirectTo);
        }



        /**
         * Quando não redirecionamos, definimos o status HTTP 404 e lançamos uma
         * exceção com a mensagem de `notFoundMessage`. O `ErrorHandler` recebe
         * essa exceção, preserva o status 404 já definido e prepara a
         * apresentação da falha conforme as configurações.
         *
         * 🔗 LINK: /galastri/core/ErrorHandler.php
         */

        http_response_code(404);
        throw new Exception($notFoundMessage);
    }



    /**
     * 🟢 VERIFICANDO AUTENTICAÇÃO REQUERIDA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Aqui verificamos a autenticação quando a configuração `authTag` está
     * preenchida. Essa tag identifica qual autenticação precisamos conferir
     * para permitir que a requisição continue.
     *
     * Se `authTag` estiver vazia, seguimos sem fazer essa verificação. Quando
     * estiver preenchida, chamamos `Authentication::validate()`, informando a
     * tag configurada necessária.
     *
     *
     * _________________________________
     * @return void
     */
    private static function checkAuthentication(): void
    {
        /**
         * Recuperamos quatro valores de configuração de rota: `authTag`,
         * `authFailRedirectTo`, `authFailMessage` e `output`.
         */

        $authTag = Config::get('authTag');
        $authFailRedirectTo = Config::get('authFailRedirectTo');
        $authFailMessage = Config::get('authFailMessage');
        $output = Config::get('output');



        /**
         * Se authTag não estiver vazia, e a autenticação falhar, temos dois
         * caminhos:
         */
        if ($authTag !== '' && !Authentication::validate($authTag)) {

            /**
             * Se `authFailRedirectTo` estiver preenchido e a saída for
             * diferente de `json`, redirecionamos para o endereço configurado,
             * que pode ser uma página de login, por exemplo. Esse
             * redirecionamento encerra a execução.
             *
             * ⚠️ IMPORTANTE
             * ┌───────────────────────────────────────────────────────────────┐
             * │ Quando a saída é JSON, esse redirecionamento não é realizado, │
             * │ mesmo que esteja configurado.                                 │
             * │                                                               │
             * │ Isso precisa ser assim porque quem consome a resposta pode    │
             * │ receber a falha em JSON e tratá-la dentro do fluxo daquela    │
             * │ requisição.                                                   │
             * └───────────────────────────────────────────────────────────────┘
             */

            if ($authFailRedirectTo !== '' && $output != 'json') {
                Redirect::to($authFailRedirectTo);
            }



            /**
             * Caso contrário, criamos uma exceção com a mensagem de
             * `authFailMessage`.
             *
             * Nesse segundo caminho, preenchemos a propriedade `errorSource` da
             * exceção com 'authentication' antes de lançá-la. Essa informação
             * permite que o `ErrorHandler` reconheça a origem da falha e
             * escolha o status HTTP 403.
             */

            $exception = new Exception($authFailMessage);

            $exception->errorSource = 'authentication';

            throw $exception;
        }
    }



    /**
     * 🟢 DEFININDO A CLASSE DE SAÍDA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * A configuração `output` informa qual saída queremos usar, mas precisamos
     * transformar esse valor no nome completo da classe responsável por ela.
     *
     * ✔️ EXEMPLO
     * ┌───────────────────────────────────────────────────────────────────┐
     * │ Se o valor for `json`, por exemplo, recuperamos e preparamos esse │
     * │ valor para obter o nome `Json`. Depois, juntamos esse nome ao     │
     * │ namespace guardado em `OUTPUT_NAMESPACE`, formando                │
     * │ `galastri\core\output\Json`.                                      │
     * └───────────────────────────────────────────────────────────────────┘
     *
     *
     * _________________________________
     * @return void
     */

    private static function setOutputClass(): void
    {
        /**
         * Recuperamos o valor de `output` já convertendo o nome para
         * PascalCase, que é o padrão para nomes de classes.
         *
         * Este resultado é preparado para conter o namespace completo + o nome
         * da classe, que será armazenado na propriedade $output.
         */

        $outputClass = Tools::toPascalCase(Config::get('output'));
        $outputClass = self::OUTPUT_NAMESPACE.'\\'.$outputClass;

        self::$output = $outputClass;
    }



    /**
     * 🟢 VERIFICAÇÃO E DEFINIÇÃO DO NAMESPACE DO CONTROLLER
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Quando o `Router` identifica a rota, ele nos entrega o nome do controller
     * que deve atendê-la. Porém, é necessário fazer algumas conferências antes
     * de prosseguir: a classe pode não existir ou pode existir sem ter a
     * estrutura que o framework espera de um controller.
     *
     * Este método cuida dessa preparação. Antes de executar o código da
     * aplicação, precisamos saber se temos um controller que podemos usar e se
     * a requisição pode continuar caso ele não exista.
     *
     *
     * _________________________________
     * @return void
     */

    private static function checkControllerNamespace(): void
    {
        /**
         * Primeiro, precisamos do nome completo da classe, como
         * `app\controllers\Clientes`. É esse nome que permite ao PHP
         * identificar exatamente qual classe queremos consultar e, depois,
         * instanciar.
         *
         * O Router entrega esse nome em um array, dividido em partes como
         * ['app', 'controllers', 'Clientes'], pois vai construindo essa
         * informação enquanto interpreta a rota. Aqui reunimos essas partes e
         * guardamos o resultado para que as verificações dos métodos e a
         * criação do objeto usem a mesma referência.
         */

        self::$controllerNamespace = implode('\\', Router::getControllerNamespace());



        /**
         * Depois precisamos verificar se o PHP encontra essa classe.
         *
         * Se a classe não existir, precisaremos considerar a saída escolhida
         * para decidir se a execução prossegue ou não.
         */
        if (!class_exists(self::$controllerNamespace)) {

            /**
             * Ao confirmar que a classe não existe, precisamos considerar a
             * saída escolhida:
             *
             * 1. Se a saída exigir um controller e o controller não existir,
             *    lançamos uma exceção informando que a classe não foi
             *    encontrada.
             *
             *    O caminho preparado em `$controllerLocation` compõe a mensagem
             *    de erro. Para montá-lo, retiramos a última parte, que
             *    representa o nome da classe, e juntamos as demais com /.
             *
             * 2. Se não exigir, mudamos a propriedade `$controllerExists` para
             *    `false` e deixamos a execução prosseguir.
             *
             *    O indicador `$controllerExists` é importante pois fará as
             *    próximas etapas pularem qualquer preparação de métodos e a
             *    criação de um objeto controller.
             */

            $controllerFullNamespace = Router::getControllerNamespace();
            $controllerNamespace = Tools::deleteLastKey($controllerFullNamespace);
            $controllerLocation = implode('/', ($controllerNamespace));

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



        /**
         * Se existir, precisamos conferir também se ela herda de
         * `galastri\modules\Controller`.
         *
         * Essa herança é necessária porque parte do trabalho de um controller é
         * fornecida pela classe base. É ela que executa os métodos da
         * aplicação, reúne seus retornos e disponibiliza os dados que a saída
         * consultará depois. Apenas encontrar uma classe com o nome esperado
         * não garante que esse funcionamento esteja presente.
         *
         * Se a classe encontrada não herdar dela, lançamos uma exceção.
         */

        } elseif (!is_subclass_of(self::$controllerNamespace, self::MODULE_CONTROLLER)) {

            throw new Exception(
                Message::get("CONTROLLER_MUST_EXTEND_MODULE"),
                [
                    self::$controllerNamespace,
                ]
            );
        }
    }



    /**
     * 🟢 VERIFICAÇÃO E DEFINIÇÃO DO MÉTODO DO CONTROLLER
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Depois de identificar a classe do controller, precisamos garantir que ela
     * tenha o método escolhido para atender à rota.
     *
     * O Router já nos entregou esse nome do método, mas a configuração da rota
     * e os métodos disponíveis na classe precisam estar de acordo.
     *
     *
     * _________________________________
     * @return void
     */

    private static function setMethodName(): void
    {
        /**
         * Primeiro consultamos $controllerExists. Se estivermos seguindo sem
         * controller, retornamos imediatamente, porque não há um método para
         * preparar.
         */
        if (!self::controllerExists()) {
            return;
        }



        /**
         * Guardamos o nome do método na propriedade `$controllerMethodName`,
         * para que o construtor da classe base `Controller` possa consultar
         * posteriormente.
         */
        self::$controllerMethodName = Router::getMethodName();



        /**
         * Como existe um controller, o método escolhido pela rota precisa
         * existir nele.
         *
         * Consultamos se o método não existe e lançamos uma exceção se isso se
         * confirmar. Se existir, não faremos nada, a execução prossegue.
         */
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



    /**
     * 🟢 A EXECUÇÃO DO CONTROLLER
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * O `executeController()` é o momento em que criamos esse controller e
     * iniciamos a execução do código da aplicação.
     *
     * Embora o método tenha apenas uma linha, que é criar o objeto, `new`
     * também executa o construtor do controller. Como o controller herda de
     * `galastri\modules\Controller`, é o construtor dessa classe base que
     * conduz o trabalho: chama o método escolhido para a rota e prepara sua
     * resposta.
     *
     *
     * Aproveite para ver sobre a classe base Controller agora e depois volte
     * aqui:
     * 🔗 LINK: /galastri/modules/Controller.php
     *
     *
     * É por isso que o método se chama `executeController()`: a criação do
     * objeto já desencadeia esse processamento.
     *
     * Usamos o nome guardado em `$controllerNamespace` para criar a classe
     * escolhida para aquela requisição. Assim, essa mesma instrução pode
     * instanciar `app\controllers\Clientes`, `app\controllers\Produtos` ou
     * qualquer outro controller identificado nas etapas anteriores.
     *
     * Depois que o construtor termina, guardamos o objeto em `$controller`.
     * Precisamos manter essa instância disponível porque ela contém o resultado
     * do processamento, que será consultado pela classe de saída para preparar
     * a resposta. É através dela que `getControllerResponse()` e
     * `getControllerFileContents()` recuperam os dados do controller.
     *
     *
     * _________________________________
     * @return void
     */

    private static function executeController(): void
    {
        self::$controller = new self::$controllerNamespace();
    }



    /**
     * 🟢 CHAMANDO A CLASSE DE FORMATO DE SAÍDA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * O `executeOutput()` é o momento em que entregamos à classe de saída a
     * responsabilidade de preparar e enviar a resposta da requisição.
     *
     * ✔️ EXEMPLO
     * ┌─────────────────────────────────────────────────────────────────────┐
     * │ Se o controller consultou uma lista de clientes e retornou esses    │
     * │ dados, ainda precisamos apresentá-los no formato esperado por quem  │
     * │ fez o acesso.                                                       │
     * │                                                                     │
     * │ Na saída `json`, a classe `Json` consulta a resposta do controller, │
     * │ converte os dados para JSON e envia esse conteúdo com o cabeçalho   │
     * │ correspondente.                                                     │
     * └─────────────────────────────────────────────────────────────────────┘
     *
     * - Veremos os formatos de saída em detalhes no próximo tópico. Se quiser
     *   adiantar, veja mais em:  
     *   🔗 LINK: /docs/fluxo-de-dados.md
     * 
     * A escolha da classe de saída já aconteceu em `setOutputClass()`, quando
     * guardamos seu nome completo em `$output`. Agora usamos essa referência
     * para chamar seu método estático `run()`.
     *
     *
     * _________________________________
     * @return void
     */

    private static function executeOutput(): void
    {
        /**
         * Essa única chamada permite que cada saída cuide de suas próprias
         * necessidades. `Galastri` sabe quando a saída deve ser executada; a
         * classe escolhida sabe como produzir a resposta. Assim, não precisamos
         * reunir aqui condições para converter dados, preparar páginas ou
         * entregar arquivos. Esses detalhes ficam nas classes responsáveis por
         * cada formato.
         *
         * E há uma diferença em relação a `executeController()`: a saída também
         * precisa ser executada quando seguimos sem controller.
         */
        self::$output::run();
    }



    /**
     * 🟢 CHECANDO SE O CONTROLLER EXISTE
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Consulta se há um controller disponível para atender à requisição.
     *
     * Essa informação permite que outras partes do framework decidam se devem
     * realizar trabalhos que dependem do controller.
     *
     *
     * _________________________________
     * @return bool  
     *    Retorna `true` se há um controller disponível. Se não, retorna
     *    `false`.
     */

    public static function controllerExists(): bool
    {
        return self::$controllerExists;
    }



    /**
     * 🟢 RECUPERANDO A RESPOSTA DO CONTROLLER
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Recupera a resposta preparada pelo controller.
     *
     * O `array` reúne os retornos dos métodos executados dentro de Controller
     * e, quando configurado, os metadados do projeto. As classes de saída podem
     * consultar esses dados através de Galastri.
     *
     *
     * _________________________________
     * @return array<int|string,mixed>  
     *    Retorna um `array` contendo toda a resposta reunida pelo controller.
     */

    public static function getControllerResponse(): array
    {
        return self::$controller->getResponse();
    }



    /**
     * 🟢 RECUPERANDO UM ARQUIVO GERADO OU ARMAZENADO NO CONTROLLER
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Recupera as informações de um arquivo preparado pelo controller. O
     * `array` contém, nesta ordem:
     *
     * ┌─────┬───────────────────┐
     * │ [0] │ o conteúdo        │
     * ├─────┼───────────────────┤
     * │ [1] │ o nome do arquivo │
     * ├─────┼───────────────────┤
     * │ [2] │ seu tipo MIME     │
     * └─────┴───────────────────┘
     *
     * A saída `File` usa essas informações para entregar o conteúdo fornecido
     * pela aplicação. A preparação dos cabeçalhos e o envio ficam por conta da
     * saída.
     *
     * Se o controller não tiver preparado um arquivo, o array retornado estará
     * vazio.
     *
     *
     * _________________________________
     * @return array<int,string>  
     *    Retorna um `array` com as informações de um arquivo preparado no
     *    controller ou vazio se não houver nada preparado.
     */

    public static function getControllerFileContents(): array
    {
        return self::$controller->getFileContents();
    }



    /**
     * 🟢 RECUPERANDO O NOME DA CLASSE CONTROLLER
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Retorna o nome completo da classe do controller, como
     * `app\controllers\Clientes`, guardado em `$controllerNamespace`.
     *
     * Isso permite consultar qual classe foi identificada para a requisição sem
     * acessar diretamente a propriedade privada.
     *
     * Apesar do nome do método, o valor inclui o namespace e o nome da classe.
     *
     * _________________________________
     * @return string  
     *    Retorna uma `string` com o nome completo da classe do controller,
     *    incluindo o namespace.
     */

    public static function getControllerNamespace(): string
    {
       return self::$controllerNamespace;
    }



    /**
     * 🟢 RECUPERANDO O NOME DO MÉTODO DO CONTROLLER
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Retorna o nome do método escolhido para atender à rota, guardado em
     * `$controllerMethodName`.
     *
     * A classe base `Controller` consulta esse valor para saber qual método
     * deve chamar.
     *
     *
     * _________________________________
     * @return string  
     *    Retorna uma `string` com o nome do método do controller escolhido para
     *    atender à rota.
     */

    public static function getControllerMethodName(): string
    {
       return self::$controllerMethodName;
    }
}



/**
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * ▶️ PRÓXIMO TÓPICO:
 * 🔗 LINK: /docs/fluxo-de-dados.md
 */
