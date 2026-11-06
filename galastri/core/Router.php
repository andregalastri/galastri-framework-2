<?php
/**
 * -----------------------------------------------------------------------------
 * 📂
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                     ARQUIVO /galastri/core/Router.php                     ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 * Este arquivo define a classe que interpreta o caminho solicitado na URL e o
 * relaciona às rotas da aplicação.
 *
 * O resultado dessa interpretação informa qual controller e método representam
 * o acesso, quais configurações se aplicam a ele e quais valores foram
 * recebidos nas partes da URL. Essas informações ficam disponíveis para que o
 * núcleo do framework possa preparar a execução da aplicação.
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
 * Para interpretar as rotas, precisamos consultar suas configurações, preparar
 * nomes de classes e métodos e informar quando uma definição não pode ser
 * utilizada. Estas classes fornecem os recursos usados nesse trabalho.
 *
 * ┌─────────────┬─────────────────────────────────────────────────────────────┐
 * │ Classe      │ Uso                                                         │
 * ├─────────────┼─────────────────────────────────────────────────────────────┤
 * │ `Config`    │ Importa o arquivo de rotas e mantém os valores das          │
 * │             │ configurações aplicadas ao acesso atual.                    │
 * ├─────────────┼─────────────────────────────────────────────────────────────┤
 * │ `Exception` │ Permite interromper a preparação da rota quando uma         │
 * │             │ definição necessária está ausente ou é inválida.            │
 * ├─────────────┼─────────────────────────────────────────────────────────────┤
 * │ `Message`   │ Fornece as mensagens padronizadas das exceções.             │
 * ├─────────────┼─────────────────────────────────────────────────────────────┤
 * │ `Tools`     │ Adapta os nomes vindos das rotas ao formato usado pelas     │
 * │             │ classes e pelos métodos dos controllers.                    │
 * └─────────────┴─────────────────────────────────────────────────────────────┘
 */

use galastri\core\config\Config;
use galastri\extensions\Exception;
use galastri\language\Message;
use galastri\modules\Tools;



/**
 * 🟢 CLASSE Router
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Quando recebemos uma URL como `/produtos/listar/ativos`, precisamos entender
 * o papel de cada parte.
 *
 * - 'produtos' pode identificar um controller,
 * - 'listar' pode indicar um método dentro do controller
 * - 'ativos' pode ser um parâmetro que esse método utilizará internamente.
 *
 * O arquivo de rotas é quem define essas relações. Nele, as chaves iniciadas
 * com `/` representam nós, que organizam o caminho até o controller. As chaves
 * iniciadas com `@` representam endpoints, que identificam os pontos de acesso
 * aos métodos desse controller.
 *
 * `Router` interpreta esse caminho e reúne as informações necessárias e as
 * prepara para atender à requisição. Também aplica as configurações encontradas
 * na rota.
 */

final class Router
{
    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                              CONSTANTES                               ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     */

    /**
     * 🟢 CAMINHO DO ARQUIVO DE ROTAS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Indica a localização do arquivo de configuração de rotas.
     *
     * Esse arquivo fornece a estrutura que `Router` irá consultar para
     * interpretar o caminho recebido da URL da requisição.
     *
     * Aproveite para ver sobre o arquivo de configuração de rotas agora e
     * depois volte aqui:
     * 🔗 LINK: /app/config/routes.php
     *
     *
     * _________________________________
     * @var string
     */

    private const APP_CONFIG_FILE_ROUTES = PROJECT_DIR.'/app/config/routes.php';



    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                             PROPRIEDADES                              ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     */

    /**
     * 🟢 CAMINHO RAIZ DAS ROTAS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Armazena o ponto de partida da URL que será gerenciada pelo framework.
     *
     * Se ainda não viu, veja mais detalhes sobre isso no arquivo de
     * configuração de rotas, depois volte aqui:
     * 🔗 LINK: /app/config/routes.php
     *
     *
     * _________________________________
     * @var string
     */

    private static string $rootDir;



    /**
     * 🟢 DADOS DA ROTA EM PROCESSAMENTO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Guarda a parte do arquivo de rotas que estamos consultando naquele
     * momento.
     *
     * No início, armazena o `array` de configuração de rotas inteiro. Conforme
     * encontramos um nó ou endpoint correspondente à URL, passa a guardar o
     * conteúdo daquela entrada. Assim, as próximas buscas ficam restritas às
     * opções disponíveis dentro do caminho já encontrado.
     *
     *
     * _________________________________
     * @var array<string,mixed>
     */

    private static array $routeData;



    /**
     * 🟢 PARTES DA URL AINDA DISPONÍVEIS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Armazena a lista das partes da URL, que vai sendo reduzida conforme forem
     * sendo processadas.
     *
     * ✔️ EXEMPLO
     * ┌───────────────────────────────────────────────────────────────────────┐
     * │ Uma rota `/produtos/listar/ativos` é armazenada como:                 │
     * │                                                                       │
     * │ [                                                                     │
     * │     '',                                                               │
     * │     'produtos',                                                       │
     * │     'listar',                                                         │
     * │     'ativos'                                                          │
     * │ ];                                                                    │
     * │                                                                       │
     * │ Router vai procurar o nó `/produtos` na configuração de rotas.        │
     * │                                                                       │
     * │ Assim que encontrar, remove 'produtos' da lista acima e continua      │
     * │ procurando um nó `/listar`. Se encontrar, remove 'listar' da lista e  │
     * │ assim por diante.                                                     │
     * │                                                                       │
     * │ Se 'listar' for encontrado como um endpoint `@listar`, a busca por    │
     * │ nós termina e 'listar' também é removido da lista.                    │
     * │                                                                       │
     * │ Neste caso, 'ativos' vai ficar sobrando, podendo ser usado como valor │
     * │ de um parâmetro de rota, a depender da configuração.                  │
     * └───────────────────────────────────────────────────────────────────────┘
     *
     * Note que o primeiro valor do array é uma string vazia. Explicaremos o
     * motivo disso mais à frente.
     *
     *
     * _________________________________
     * @var array<string>
     */

    private static array $urlParts;



    /**
     * 🟢 NOME DO NÓ ATUAL
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Guarda o nome do último nó encontrado no arquivo de rotas, como /produtos
     * ou /?apelido, durante o processamento de nós.
     *
     * ⚠️ IMPORTANTE
     * ┌───────────────────────────────────────────────────────────────────────┐
     * │ Esta propriedade é usada apenas na mensagem de erro que informa em    │
     * │ qual nó está faltando o endpoint principal `@main`.                   │
     * └───────────────────────────────────────────────────────────────────────┘
     *
     *
     * _________________________________
     * @var string
     */

    private static string $nodeName = '';



    /**
     * 🟢 NOME DO CONTROLLER IDENTIFICADO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Armazena o nome da classe de controller associada ao caminho encontrado,
     * normalmente, a partir do último nó encontrado.
     *
     * ✔️ EXEMPLO
     * ┌───────────────────────────────────────────────────────────────────────┐
     * │ Na rota `/produtos/busca`, `/produtos` pode ser identificado como o   │
     * │ nó, enquanto `busca` é o endpoint. Neste caso, esta propriedade vai   │
     * │ armazenar o nome 'Produtos', em PascalCase.                           │
     * └───────────────────────────────────────────────────────────────────────┘
     *
     * Note que aqui guardamos apenas o nome da classe. As partes que formam seu
     * namespace completo ficam em `$controllerNamespace`.
     *
     *
     * _________________________________
     * @var string
     */

    private static string $controllerName = '';



    /**
     * 🟢 PARTES DO NAMESPACE DO CONTROLLER
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Reúne as partes que formarão o nome completo da classe de controller. O
     * `array` começa com 'app' e 'controllers', que representam o local padrão
     * `app\controllers`.
     *
     * Conforme avançamos pelos nós, acrescentamos os nomes das classes
     * correspondentes. Uma configuração `namespace` pode fornecer outra base
     * para essa montagem.
     *
     * ✔️ EXEMPLO
     * ┌───────────────────────────────────────────────────────────────────────┐
     * │ Por exemplo,                                                          │
     * │                                                                       │
     * │ ['app', 'controllers', 'Produtos']                                    │
     * │                                                                       │
     * │ Este array representa o acesso à classe `Produtos` através do         │
     * │ namespace `app\controllers`. A classe `Galastri` será responsável por │
     * │ organizar as partes com barras invertidas para gerar a localização do │
     * │ controller, transformando o array em uma string                       │
     * │ `app\controllers\Produtos`.                                           │
     * └───────────────────────────────────────────────────────────────────────┘
     *
     *
     * _________________________________
     * @var array<string>
     */

    private static array $controllerNamespace = ['app', 'controllers'];



    /**
     * 🟢 VALORES RECEBIDOS NOS NÓS DINÂMICOS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Guarda os valores dos nós dinâmicos, aqueles cujas chaves começam com
     * `/?`.
     *
     * Se a rota contém `/?apelido` e a URL informa 'maria' nessa posição,
     * guardamos ['apelido' => 'maria']. Assim, a mesma definição de nó pode
     * atender valores diferentes e deixá-los disponíveis para a aplicação.
     *
     *
     * _________________________________
     * @var array<string,string>
     */
    private static array $dynamicNodes = [];



    /**
     * 🟢 NOME DO MÉTODO DA ROTA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Armazena o nome do método do controller que irá atender a rota. Possui o
     * valor 'main' como padrão, para os casos quando não há um endpoint
     * explícito na URL.
     *
     * O nome do endpoint é adaptado ao formato camelCase e o controller precisa
     * seguir este padrão. Se o endpoint for 'nossa-empresa', então o nome do
     * método armazenado aqui será 'nossaEmpresa'.
     *
     *
     * _________________________________
     * @var string
     */
    private static string $methodName = 'main';



    /**
     * 🟢 RESULTADO DA CONFERÊNCIA DOS PARÂMETROS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Indica se as partes restantes da URL respeitam a quantidade de parâmetros
     * definida na configuração do endpoint.
     *
     * Começa com `true` e passa para `false` quando falta um parâmetro
     * obrigatório ou alguma parte da URL sobra.
     *
     * ⚠️ IMPORTANTE
     * ┌───────────────────────────────────────────────────────────────────┐
     * │ A falta de valor para um parâmetro opcional, indicado por `?`,    │
     * │ não invalida a rota.                                              │
     * └───────────────────────────────────────────────────────────────────┘
     *
     * Esse resultado fica disponível para que `Galastri` decida como
     * prosseguir.
     *
     *
     * _________________________________
     * @var bool
     */
    private static bool $isValidRoute = true;



    /**
     * 🟢 CONTROLE DA REMOÇÃO DE INDEX DO NAMESPACE
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Registra se já aplicamos o filtro de `Index` durante a montagem do
     * namespace do controller.
     *
     * `Index` representa o controller padrão da raiz. Ao avançarmos para outros
     * nós, precisamos evitar que esse nome continue ocupando uma parte
     * intermediária do caminho.
     *
     * Esse controle faz a remoção acontecer apenas uma vez. Caso haja um nome
     * `Index` acrescentado por outro nó, ele não será removido.
     *
     *
     * _________________________________
     * @var bool
     */
    private static bool $removedIndexFromNamespace = false;



    /**
     * 🟢 VALORES DOS PARÂMETROS DA ROTA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Guarda os valores recebidos nas partes da URL após o endpoint, associados
     * aos nomes definidos na configuração `parameters`.
     *
     * ✔️ EXEMPLO
     * ┌───────────────────────────────────────────────────────────────────────┐
     * │ Por exemplo, se configurarmos `'parameters' => '/tipo/?ordem'` e a    │
     * │ URL terminar com `/ativos/nome`, obtemos:                             │
     * │                                                                       │
     * │ [                                                                     │
     * │     'tipo' => 'ativos',                                               │
     * │     'ordem' => 'nome'                                                 │
     * │ ].                                                                    │
     * └───────────────────────────────────────────────────────────────────────┘
     *
     * 💡 OBSERVAÇÃO
     * ┌───────────────────────────────────────────────────────────────────────┐
     * │ O ponto de interrogação na configuração dos parâmetros identifica um  │
     * │ parâmetro opcional.                                                   │
     * │                                                                       │
     * │ Quando não há uma parte da URL para preencher um parâmetro opcional,  │
     * │ seu valor é armazenado como null.                                     │
     * │                                                                       │
     * │ Já se faltar um valor obrigatório, ele também fica como `null` e o    │
     * │ `Router` marca a rota como inválida.                                  │
     * └───────────────────────────────────────────────────────────────────────┘
     *
     *
     * _________________________________
     * @var array<string,string|null>
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
     * 🟢 INTERPRETAÇÃO DA ROTA SOLICITADA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Executa a sequência que relaciona a URL recebida com as configurações de
     * rotas da aplicação. Precisamos desse resultado para saber quais
     * configurações usar, qual controller representa o caminho e qual método
     * foi escolhido.
     *
     * Também armazena os valores recebidos de nós dinâmicos e nos parâmetros,
     * caso tenham sido configurados.
     *
     *
     * _________________________________
     * @return void
     */

    public static function run(): void
    {
        /**
         * Começamos importando o array do arquivo de configuração de rotas.
         * Neste momento, `$routeData` contém a estrutura inteira do arquivo.
         *
         * Durante o processo de busca pelas configurações com base na URL, esta
         * propriedade passará a armazenar apenas os dados do nó ou endpoint que
         * estivermos processando.
         */

        self::$routeData = Config::importConfig(self::APP_CONFIG_FILE_ROUTES);



        /**
         * Preparamos a raiz e as partes da URL antes de procurar os nós. Assim,
         * a busca pode avançar pela rota informada na URL começando pelo nó
         * raiz.
         */

        self::configureRootDir();
        self::setUrlParts();
        self::resolveNodes();



        /**
         * Ao terminar a busca dos nós, armazenamos o `controller` informado no
         * nó final, identificamos o endpoint e relacionamos as partes restantes
         * aos parâmetros da rota.
         */

        self::configureNodeProperties();
        self::resolveEndpoints();
        self::resolveParameters();
    }



    /**
     * 🟢 DEFINIÇÃO DO CAMINHO RAIZ
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Estabelece o ponto de partida das rotas da aplicação. Essa informação é
     * necessária tanto para interpretar o caminho solicitado quanto para
     * fornecer o endereço base usado nos links.
     *
     * O caminho precisa seguir o formato aceito pelo framework. Uma raiz
     * inválida interrompe a preparação com uma exceção que identifica o arquivo
     * de rotas e o valor recebido.
     *
     *
     * _________________________________
     * @return void
     */

    private static function configureRootDir(): void
    {
        /**
         * A primeira chave do arquivo de configuração de rotas identifica a
         * raiz. Uma aplicação pode usar `/` quando suas rotas começam
         * diretamente no domínio, ou um caminho como `/projeto` quando há uma
         * base anterior às demais partes.
         */

        $rootDir = array_key_first(self::$routeData);



        /**
         * Conferimos o formato antes de guardar o caminho.
         *
         * A raiz deve conter a raiz `/` e pode conter um caminho em que cada
         * parte se inicia com `/` seguida de letras, números, sublinhados e
         * hífens.
         *
         * Se o formato não seguir este padrão, ele será inválido e uma exceção
         * é lançada.
         */

        $validFormat = '/^\/(?:[\p{L}\p{N}_-]+\/?)*[\p{L}\p{N}_-]+$|^\/$/u';
        $isRootDirValid = preg_match($validFormat, $rootDir);

        if ($isRootDirValid) {
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
     * 🟢 PREPARAÇÃO DAS PARTES DA URL
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Divide o caminho da URL em partes, armazenando em um `array` que podemos
     * relacionar a nós, endpoint e parâmetros da rota.
     *
     * Essa separação permite consumir uma parte por vez durante o processamento
     * de busca da rota, mantendo disponíveis os valores que ainda não foram
     * interpretados.
     *
     * 💡 OBSERVAÇÃO
     * ┌───────────────────────────────────────────────────────────────────────┐
     * │ Os dados enviados após o sinal de interrogação ? não participam desse │
     * │ percurso.                                                             │
     * └───────────────────────────────────────────────────────────────────────┘
     *
     *
     * _________________________________
     * @return void
     */

    private static function setUrlParts(): void
    {
        /**
         * Puxamos o valor de REQUEST_URI, que inclui o caminho completo da URL,
         * com exceção do domínio.
         *
         * Precisamos guardar apenas o caminho entre o domínio e um possível
         * ponto de interrogação ?.
         *
         * ✔️ EXEMPLO
         * ┌────────────────────────────────────────────────────────────────┐
         * │ Caso a URL seja https://exemplo.com/produtos/listar?ordem=nome │
         * │ guardamos apenas `/produtos/listar` pois é esse caminho que    │
         * │ precisamos relacionar com as rotas. O restante, não usaremos   │
         * │ aqui.                                                          │
         * └────────────────────────────────────────────────────────────────┘
         *
         * Para nós, portanto, só interessa o caminho formado por barras /.
         */

        $requestPath = explode('?', $_SERVER['REQUEST_URI'], 2)[0];



        /**
         * Começamos o tratamento do caminho fazendo a remoção do caminho raiz
         * caso ele apareça no início da string.
         */

        $partsString = Tools::trimLeft($requestPath, self::$rootDir, 1);



        /**
         * Declaramos `$urlParts` com uma string vazia logo na primeira posição.
         * O motivo disso é que, antes de procurar pelos nós recebidos na URL,
         * precisamos processar as configurações de rota que estão logo no nó
         * raiz da cadeia.
         *
         * A string vazia, portanto, é o sinal que resolveNodes() e
         * resolveNamespace() usarão para identificar que estamos tratando o nó
         * raiz.
         *
         * -  Quando `resolveNodes()` encontra a string vazia, ele entende que
         *    deve procurar as configurações da chave definida em $rootDir.
         *
         * -  Já quando `resolveNamespace()` encontra a string vazia, ele
         *    entende que deve usar `Index` como nome padrão do controller.
         *
         * Essa string vazia é removida da lista posteriormente, para que a
         * busca continue pelas próximas partes da URL.
         */

        $urlParts = [''];



        /**
         * Separamos o caminho pelas barras e guardamos as partes preenchidas.
         *
         * Por exemplo, /produtos/listar fornece ['', 'produtos', 'listar'],
         * incluindo a posição que reservamos para a raiz.
         */

        $partsList = explode('/', $partsString);

        foreach ($partsList as $partValue) {
            if (!is_null($partValue) && $partValue !== '') {
                $urlParts[] = $partValue;
            }
        }

        self::$urlParts = $urlParts;
    }



    /**
     * 🟢 IDENTIFICAÇÃO DOS NÓS DA ROTA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Faz o processamento das partes disponíveis da URL, relacionando-as aos
     * nós definidos no arquivo de configuração de rotas. Cada nó encontrado
     * participa da identificação do controller e determina o ponto de onde
     * continuaremos procurando.
     *
     * Os nós podem ter nomes fixos, como `/produtos`, ou receber valores
     * através de uma definição dinâmica, como `/?apelido`. Também precisamos
     * reconhecer quando o próximo trecho corresponde a um endpoint, pois a
     * preparação desse acesso pertence a outra etapa.
     *
     * Essa resolução permite percorrer nós dentro de outros nós, acompanhando a
     * organização que a aplicação definiu para suas rotas.
     *
     *
     * _________________________________
     * @return void
     */

    private static function resolveNodes(): void
    {
        /**
         * Este método funciona de maneira recursiva, por isso pode parecer que
         * algumas partes dele estejam fora de lugar, como o trecho abaixo.
         *
         * `$urlParts` começa com a string vazia reservada para o nó raiz e as
         * partes do caminho que sobraram após a remoção da raiz. Quando
         * `resolveNodes()` é chamado, ele vai iniciar uma cadeia de resolução
         * dessas partes, trecho a trecho.
         *
         * A cada parte da URL encontrada nas configurações de rota,
         * `resolveNodes()` chamará `processNodeData()`, que removerá a parte
         * correspondente ao nó atual de `$urlParts` e processará suas
         * configurações e chamará `resolveNodes()` novamente.
         *
         * O item removido de `$urlParts` é sempre o da posição [0]. Isso
         * significa que o item que estava na posição [1] passa a ocupar a
         * posição [0], o da posição [2] passa a ocupar a posição [1] e assim
         * por diante.
         *
         * Logo, `$urlParts` sempre vai armazenar, na posição zero [0], a
         * próxima parte da URL que deverá ser interpretada.
         *
         * Quando todas as partes de `$urlParts` forem consumidas durante essa
         * busca, ela ficará vazia. Nesse caso, a posição [0] de `$urlParts`
         * deixará de existir, não havendo mais partes para prosseguir. Isso
         * indicará que a chamada de `resolveNodes()` deve ser encerrada.
         *
         * É isso que estamos testando abaixo. Estamos verificando se
         * `$urlParts[0]` existe, o que pode parecer estranho à primeira vista,
         * mas trata-se de um teste que leva em conta as possíveis recursões. Se
         * a posição [0] não existir, a lista está vazia, logo, `$partValue`,
         * que nas demais recursões armazenava o valor da posição [0] de
         * `$urlParts`, armazenará `null`, indicando que a chamada deve ser
         * encerrada.
         */

        $partValue = self::$urlParts[0] ?? null;

        if ($partValue === null) {
            return;
        }



        /**
         * Se houver prosseguimento, precisamos identificar o que a parte
         * armazenada em `$partValue` representa:
         *
         * a) O nó raiz, indicado pela string vazia b) Um nó fixo, cuja chave
         * começa com `/` c) Um endpoint, cuja chave começa com `@` d) Um nó
         * dinâmico, cuja chave começa com `/?`
         */

        /**
         * a) Começaremos verificando se estamos no começo do processamento de
         * rotas.
         *
         * Se a string de `$partValue` for vazia, isso indica que esta é a
         * primeira chamada de `resolveNodes()`. Neste caso, a única chave que
         * podemos puxar é a da raiz do array de rotas.
         *
         * Atribuímos ao `$partName`, neste caso, o valor da chave da raiz.
         *
         *
         * b) Porém, se `$partValue` não for vazia, significa que já estamos com
         * o processamento das rotas em andamento.
         *
         * Neste caso, vamos começar testando se `$partValue` corresponde a um
         * nó fixo.
         *
         * Atribuímos ao `$partName`, neste caso, o valor de $partValue iniciado
         * com barra /.
         */

        $partName = $partValue === '' ? self::$rootDir : '/'.$partValue;

        if (array_key_exists($partName, self::$routeData)) {

            /**
             * Se encontrarmos a chave armazenada em `$partName`, substituímos o
             * conteúdo de `$routeData` pelo conteúdo desse nó.
             *
             * Assim, na próxima chamada, procuraremos as próximas partes apenas
             * dentro do nó encontrado.
             *
             * Basicamente, vamos entrando no array em níveis cada vez mais
             * profundos, conforme encontramos os nós do caminho recebido.
             */

            self::$routeData = self::$routeData[$partName];



            /**
             * Armazenamos o nome do nó atual na propriedade $nodeName.
             *
             * ⚠️ IMPORTANTE
             * ┌─────────────────────────────────────────────────────────┐
             * │ Esta propriedade é usada apenas na mensagem de erro que │
             * │ informa em qual nó está faltando o endpoint principal   │
             * │ `@main`, não tem outra utilidade.                       │
             * └─────────────────────────────────────────────────────────┘
             */

            self::$nodeName = $partName;



            /**
             * Inicia o processamento das configurações do nó encontrado.
             */

            self::processNodeData($partValue);

            return;
        }



        /**
         * c) Se o teste para nó fixo não funcionar, então vamos para o próximo
         * teste, verificando se $partValue é um endpoint.
         */

        if (array_key_exists('@'.$partValue, self::$routeData)) {

            /**
             * Se for um endpoint, então encerramos a busca por nós aqui. Não
             * faremos mais nada, trataremos do endpoint nas próximas etapas.
             */

            return;
        }



        /**
         * d) Se o teste para endpoint não funcionar, então faremos um último
         * teste, verificando se $partValue é um nó dinâmico.
         *
         * Para isso, precisamos procurar alguma chave iniciada com `/?` nas
         * configurações atuais da rota. Se ela existir, então o nó atual será
         * processado como um nó dinâmico.
         */

        foreach (self::$routeData as $key => $routeData) {

            /**
             * Testamos se a chave atual começa com `/?`.
             */
            if (strpos($key, '/?') === 0) {

                /**
                 * Encontramos um nó dinâmico. A variável local `$routeData`
                 * contém os dados desse nó, recebidos pelo foreach.
                 *
                 * Atribuímos esses dados à propriedade `self::$routeData`, para
                 * que a próxima chamada procure as próximas partes apenas
                 * dentro desse nó.
                 */

                self::$routeData = $routeData;



                /**
                 * Armazenamos o nome do nó atual na propriedade $nodeName.
                 *
                 * ⚠️ IMPORTANTE
                 * ┌─────────────────────────────────────────────────────────┐
                 * │ Esta propriedade é usada apenas na mensagem de erro que │
                 * │ informa em qual nó está faltando o endpoint principal   │
                 * │ `@main`, não tem outra utilidade.                       │
                 * └─────────────────────────────────────────────────────────┘
                 */

                self::$nodeName = $key;



                /**
                 * Armazenamos o nome e o valor do nó dinâmico no array
                 * `$dynamicNodes`.
                 *
                 * Supondo que a chave do nó dinâmico seja `/?apelido` e que
                 * essa parte da URL contenha `maria`, armazenaremos:
                 *
                 * self::$dynamicNodes['apelido'] = 'maria';
                 */
                $parameterName = ltrim($key, '/?');
                self::$dynamicNodes[$parameterName] = $partValue;



                /**
                 * Inicia o processamento das configurações do nó encontrado.
                 */

                self::processNodeData($key);

                return;
            }
        }
    }



    /**
     * 🟢 PROCESSAMENTO DO NÓ ENCONTRADO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Quando resolveNodes() chama esse método, ele já encontrou um nó e colocou
     * as configurações dele em `$routeData`. Agora precisamos processar esse nó
     * antes de seguir para a próxima parte da URL.
     *
     *
     * _________________________________
     * @param string $partValue
     *    Trecho que será tratado para compor o namespace completo da classe
     *    controller.
     * _________________________________
     * @return void
     */

    private static function processNodeData(string $partValue): void
    {
        /**
         * Primeiro, `array_shift()` remove a primeira parte de `$urlParts`.
         *
         * O motivo é que não precisamos mais dele, já o encontramos, então ele
         * é removido da lista para que possamos prosseguir.
         *
         * ✔️ EXEMPLO
         * ┌───────────────────────────────────────────────────────────────┐
         * │ Se acabamos de encontrar `/produtos`, aconteceria o seguinte: │
         * │                                                               │
         * │ Antes: $urlParts = ['produtos', 'listar', 'ativos'];          │
         * │                                                               │
         * │ array_shift(self::$urlParts);                                 │
         * │                                                               │
         * │ Depois: $urlParts = ['listar', 'ativos'];                     │
         * └───────────────────────────────────────────────────────────────┘
         */

        array_shift(self::$urlParts);



        /**
         * Em seguida, `configureRouteProperties()` aplica as configurações dos
         * contextos `debug` e `route` que estiverem presentes nesse nó. Assim,
         * conforme entramos no caminho, as configurações encontradas passam a
         * valer. O que não estiver definido ali mantém o valor que já tinha.
         */

        self::configureRouteProperties();



        /**
         * Depois, `resolveNamespace()` atualiza o namespace da classe
         * controller, acrescentando o nome obtido a partir de `$partValue`.
         */

        self::resolveNamespace($partValue);



        /**
         * Com essa parte resolvida, chegamos a um `resolveNodes()` de novo.
         *
         * Só que agora a situação já mudou: a parte anterior da URL não está
         * mais na lista e `$routeData` contém apenas o conteúdo do nó em que
         * acabamos de entrar. Então a próxima busca acontece dentro dele,
         * olhando a próxima parte da URL.
         *
         * É esse reencontro entre os dois métodos que faz a busca avançar:
         * `resolveNodes()` faz a busca e encontra o nó; `processNodeData()`
         * prepara o que corresponde a ele e chama a busca novamente.
         *
         * Quando não houver outro nó para entrar, inclusive quando encontramos
         * um endpoint, essa sequência para, e o run() segue com as etapas
         * restantes.
         */

        self::resolveNodes();
    }



    /**
     * 🟢 APLICAÇÃO DAS CONFIGURAÇÕES POR CONTEXTO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Esse método pega as configurações presentes em `$routeData` e aplica
     * apenas aquelas que pertencem ao contexto solicitado.
     *
     * O $context funciona como um filtro. Se receber 'debug', por exemplo,
     * estamos dizendo: "Nesta rodada, vamos cuidar só das configurações de
     * debug".
     *
     * ✔️ EXEMPLO
     * ┌──────────────────────────────────────────────────────────────────────┐
     * │ Se o método receber `debug` no contexto, ele terá a tarefa de cuidar │
     * │ das configurações apenas deste contexto, consultando quais           │
     * │ configurações pertencem a este grupo e aplicando somente as          │
     * │ configurações que estiverem declaradas na rota atual.                │
     * │                                                                      │
     * │ E isso serve também para os demais contextos e para cada bloco de    │
     * │ configuração de rota encontrado.                                     │
     * └──────────────────────────────────────────────────────────────────────┘
     *
     *
     * _________________________________
     * @param string $context
     *    Contexto usado para selecionar quais configurações presentes em
     *    `$routeData` serão aplicadas.
     * _________________________________
     * @return void
     */

    private static function configurePropertiesByContext(string $context): void
    {
        /**
         * O `foreach` percorre todas as configurações registradas em
         * `Config::list()`. Em cada loop, temos o nome da configuração em
         * `$name` e o objeto que descreve suas regras em `$definition`.
         */

        foreach (Config::list() as $name => $definition) {



            /**
             * Antes de aplicar qualquer valor, fazemos duas conferências:
             *
             * 1. Essa configuração pertence ao contexto solicitado? Se
             *    `getContext()` retornar outro contexto, o `continue` pula para
             *    a próxima configuração.
             *
             * 2. Ela foi informada na configuração da rota que estamos
             *    processando? Se o nome não existir em `$routeData`, pulamos
             *    também. Nesse caso, o valor que já estava em `Config` continua
             *    valendo.
             */

            if ($definition->getContext() !== $context) {
                continue;
            }



            /**
             * 💡 OBSERVAÇÃO
             * ┌────────────────────────────────────────────────────────────┐
             * │ Usamos `array_key_exists()` ao invés de `isset()` pois uma │
             * │ configuração com valor `null` também poderá contar como    │
             * │ válida, caso a configuração esteja definida para aceitar   │
             * │ valores `null`.                                            │
             * └────────────────────────────────────────────────────────────┘
             */

            if (!array_key_exists($name, self::$routeData)) {
                continue;
            }



            /**
             * Passou pelas duas? Então pegamos o valor informado na rota e o
             * entregamos ao `Config::set()`, que cuida de validá-lo e
             * armazená-lo.
             */

            Config::set($name, self::$routeData[$name]);
        }
    }



    /**
     * 🟢 APLICAÇÃO DAS CONFIGURAÇÕES DE DEBUG E ROTA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Esse método reúne duas chamadas que aplicam as configurações dos
     * contextos `debug` e `route` presentes em `$routeData` no momento da
     * chamada.
     *
     * É como dizer: "Já estamos com os dados desse nó ou endpoint em mãos.
     * Agora veja quais configurações de `debug` e de `route` foram informadas
     * aqui e aplique os valores".
     *
     *
     * _________________________________
     * @return void
     */

    private static function configureRouteProperties(): void
    {
        self::configurePropertiesByContext('debug');
        self::configurePropertiesByContext('route');
    }



    /**
     * 🟢 APLICAÇÃO DAS CONFIGURAÇÕES DO NÓ
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Esse método reúne comandos que aplicam as configurações de contexto
     * `node` presentes em `$routeData` no momento da chamada.
     *
     * É como dizer: "Já estamos com os dados do último nó encontrado em mãos.
     * Agora veja quais configurações de `node` foram informadas aqui e aplique
     * os valores".
     *
     *
     * _________________________________
     * @return void
     */

    private static function configureNodeProperties(): void
    {
        /**
         * Aplica as configurações do contexto 'node' que estiverem presentes em
         * $routeData neste momento.
         */

        self::configurePropertiesByContext('node');



        /**
         * Depois vem um ajuste especial: dentro de `$routeData`, existe uma
         * configuração chamada `controller`?
         *
         * Até aqui, o nome do `controller` foi montado a partir dos nós
         * encontrados, mas pode acontecer do desenvolvedor preferir escolher um
         * controller diferente do padrão.
         *
         * Quando houver uma configuração `controller` no nó, o nome do
         * controller deixa de ser o padrão e passa a ser o que foi configurado.
         *
         *
         * ✔️ EXEMPLO
         * ┌───────────────────────────────────────────────────────────────────┐
         * │ Suponha a seguinte rota: `/admin/produtos`.                       │
         * │                                                                   │
         * │ Por padrão, essa rota estaria definindo que o controller é        │
         * │ `Produtos`                                                        │
         * │                                                                   │
         * │ Mas imagine que o nó `/produtos` tenha esta configuração:         │
         * │                                                                   │
         * │ 'controller' => 'Catalogo'                                        │
         * │                                                                   │
         * │ Isso é como dizer: "O endereço continua terminando em             │
         * │ `/produtos`, mas quem atende aqui é a classe controller           │
         * │ `Catalogo`".                                                      │
         * │                                                                   │
         * │ Ou seja, a propriedade `$controllerName` precisa ser alterada     │
         * │ para o que estiver definido na configuração do nó.                │
         * └───────────────────────────────────────────────────────────────────┘
         */

        if (array_key_exists('controller', self::$routeData)) {

            /**
             * Por isso, puxamos a configuração `controller` e atribuímos o
             * valor dela para a propriedade $controllerName.
             */

            self::$controllerName = Config::get('controller');



            /**
             * Também precisamos atualizar o namespace com o nome do controller
             * escolhido. Primeiro, `array_pop()` remove o último nome do array.
             * Depois, acrescentamos o valor de `$controllerName` no lugar.
             *
             * ✔️ CONFORME O EXEMPLO ANTERIOR
             * ┌──────────────────────────────────────────────────────────────┐
             * │ Removemos `Produtos` com `array_pop()` e acrescentamos       │
             * │ `Catalogo` em seguida. Assim, `$controllerNamespace` contém: │
             * │ ['app', 'controllers', 'Admin', 'Catalogo'].                 │
             * │                                                              │
             * │ A rota não muda, ela ainda aponta pra `/admin/produtos`.     │
             * └──────────────────────────────────────────────────────────────┘
             */

            array_pop(self::$controllerNamespace);

            self::$controllerNamespace[] = self::$controllerName;
        }
    }



    /**
     * 🟢 APLICAÇÃO DAS CONFIGURAÇÕES DO ENDPOINT
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Esse método reúne comandos que aplicam as configurações de contexto
     * endpoint presentes em `$routeData` no momento da chamada.
     *
     * É como dizer: "Já estamos com os dados do endpoint encontrado em mãos.
     * Agora veja quais configurações de endpoint foram informadas aqui e
     * aplique os valores".
     *
     * _________________________________
     * @return void
     */

    private static function configureEndpointProperties(): void
    {
        /**
        * Aplica as configurações do contexto 'endpoint' que estiverem presentes
        * em $routeData neste momento.
        */

        self::configurePropertiesByContext('endpoint');

        /**
         * Depois vêm alguns ajustes especiais, que respondem às seguintes
         * perguntas:
         *
         * 1. Dentro de `$routeData`, existe uma configuração chamada `method`?
         *
         *    Até aqui, o nome do método a ser chamado foi montado a partir do
         *    endpoint encontrado, mas pode acontecer do desenvolvedor preferir
         *    escolher um método diferente do padrão.
         *
         *    Quando a configuração `method` do endpoint informar uma string não
         *    vazia, usamos esse valor para substituir o nome do método definido
         *    até aqui.
         *
         *    ✔️ EXEMPLO
         *    ┌────────────────────────────────────────────────────────────────┐
         *    │ Suponha a seguinte rota: `/produtos/listar`.                   │
         *    │                                                                │
         *    │ Por padrão, essa rota estaria definindo que o método é         │
         *    │ `listar`                                                       │
         *    │                                                                │
         *    │ Mas imagine que o endpoint `@listar` tenha esta configuração:  │
         *    │                                                                │
         *    │ 'method' => '@abrirLista'                                      │
         *    │                                                                │
         *    │ Isso é como dizer: "O endereço continua terminando em          │
         *    │ `/listar`, mas o método a ser executado aqui é                 │
         *    │ `abrirLista()`".                                               │
         *    │                                                                │
         *    │ Ou seja, a propriedade `$methodName` precisa ser alterada para │
         *    │ o que estiver definido na configuração do endpoint.            │
         *    └────────────────────────────────────────────────────────────────┘
         */

        if (array_key_exists('method', self::$routeData)) {

            /**
             * Por isso, puxamos a configuração `method` e atribuímos o valor
             * dela para uma variável temporária.
             */

            $methodName = Config::get('method');



            /**
             * Se o valor puxado da configuração `method` não for vazio, então
             * armazenamos o valor na propriedade `$methodName`.
             */

            if ($methodName !== '') {



                /**
                 * Antes de armazenar, porém, removemos os caracteres `@` que
                 * estiverem no início da string.
                 */

                self::$methodName = ltrim($methodName, '@');
            }
        }



        /**
         * 2. Dentro de `$routeData`, existe uma configuração chamada
         *    `httpMethod`?
         *
         *    Esta configuração associa tipos de requisição HTTP a nomes de
         *    métodos do controller, por isso, será reformatada antes de ser
         *    armazenada.
         */

        if (array_key_exists('httpMethod', self::$routeData)) {



            /**
             * A nova formatação segue um padrão previsível: chaves em minúsculo
             * e valores sem `@`.
             *
             * ✔️ EXEMPLO
             * ┌───────────────────────────────────────────────────────────────┐
             * │ Suponha a seguinte configuração no endpoint:                  │
             * │ ┌─────────────────────────────┐                               │
             * │ │ 'httpMethod' => [           │                               │
             * │ │     'POST' => '@salvar',    │                               │
             * │ │     'DELETE' => '@excluir', │                               │
             * │ │ ]                           │                               │
             * │ └─────────────────────────────┘                               │
             * │                                                               │
             * │ O `foreach` percorre essas associações e monta um novo array  │
             * │ com uma escrita padronizada, resultando em:                   │
             * │                                                               │
             * │ ┌────────────────────────────┐                                │
             * │ │ [                          │                                │
             * │ │     'post' => 'salvar',    │                                │
             * │ │     'delete' => 'excluir', │                                │
             * │ │ ]                          │                                │
             * │ └────────────────────────────┘                                │
             * └───────────────────────────────────────────────────────────────┘
             */

            $httpMethod = [];

            foreach (Config::get('httpMethod') as $type => $method) {
                $httpMethod[mb_strtolower($type)] = ltrim($method, '@');
            }



            /**
             * Por fim, o valor reformatado é inserido no lugar da configuração
             * bruta.
             */

            Config::set('httpMethod', $httpMethod);
        }
    }



    /**
     * 🟢 IDENTIFICAÇÃO E CONFIGURAÇÃO DO ENDPOINT
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * O objetivo desse método é identificar qual endpoint será usado, aplicar
     * suas configurações definidas no array de rotas e preparar o nome do
     * método que será chamado no controller.
     *
     * Até aqui, o `Router` descobriu em qual nó da aplicação a URL chegou. Mas
     * encontrar esse nó ainda não encerra o trabalho: dentro dele podem existir
     * vários `endpoints`, cada um representando uma ação disponível naquele
     * endereço.
     *
     *
     * _________________________________
     * @return void
     */

    private static function resolveEndpoints(): void
    {
        /**
         * Separamos alguns dados em variáveis para facilitar a leitura do
         * código.
         */

        $output = Config::get('output');
        $routeData = self::$routeData;
        $routeKeys = array_keys($routeData);



        /**
         * O `preg_grep()` seleciona a chave exata `@main` e as chaves iniciadas
         * com `@main:`, reunindo os resultados no array `$mainEndpoints`.
         * Se o array estiver vazio, nenhuma chave corresponde a esses formatos.
         */

        $mainEndpoints = preg_grep('/^@main(:|$)/', $routeKeys);



        /**
         * Quando a saída é diferente de 'file' e a busca acima não encontra
         * nenhuma chave, lançamos uma exceção informando o arquivo de rotas e
         * o nome do nó.
         *
         * Na seleção do endpoint, a chave `@main` funciona como o ponto de
         * acesso padrão daquele nó, usado quando a próxima parte da URL não
         * corresponde a nenhum endpoint.
         *
         * ⚠️ IMPORTANTE
         * ┌───────────────────────────────────────────────────────────────────┐
         * │ A saída 'file' fica dispensada dessa exigência porque pode        │
         * │ entregar um arquivo sem precisar de um controller nem um          │
         * │ endpoint.                                                         │
         * └───────────────────────────────────────────────────────────────────┘
         */

        if ($output !== 'file' && $mainEndpoints === []) {
            throw new Exception(
                Message::get("ROUTER_MAIN_METHOD_NOT_FOUND"),
                [
                    str_replace(
                        PROJECT_DIR.'/', '', self::APP_CONFIG_FILE_ROUTES),
                    self::$nodeName,
                ]
            );
        }



        /**
         * Agora precisamos encontrar a chave do endpoint atual dentro do
         * `$routeData`.
         *
         * Declaramos aqui algumas variáveis auxiliares:
         *
         * -  `$methodName`: armazena o nome do método que será usado como base
         *    para a chamada no controller. Começa com 'main' e será substituído
         *    se encontrarmos um endpoint correspondente à próxima parte da URL.
         *
         * -  `$endpointkey`: armazena a chave do endpoint cujas configurações
         *    serão consultadas em `$routeData`. Começa com '@main', que será
         *    usado caso nenhuma chave corresponda à próxima parte da URL.
         *
         * -  `$endpointExists`: indica se existe um endpoint para atender ao
         *    acesso, incluindo o endpoint padrão `@main`.
         */

        $methodName = 'main';
        $endpointkey = '@main';
        $endpointExists = isset($routeData[$endpointkey]);



        /**
         * O `foreach` então percorre as entradas de `$routeData`. Precisamos
         * buscar apenas aquelas cujas chaves começam com `@`.
         */

        foreach ($routeData as $key => $endpointData) {



            if (strpos($key, '@') === 0) {

                /**
                 * Para cada chave encontrada, removemos os caracteres `@` do
                 * início e comparamos o nome obtido com a próxima parte da
                 * URL, disponível em `$urlParts[0]`.
                 *
                 * Se `$urlParts` estiver vazio, usaremos 'main' na comparação.
                 */

                $urlPartAsMethod = self::$urlParts[0] ?? 'main';
                $endpointMethodName = ltrim($key, '@');



                /**
                 * Se a parte da URL coincidir com o nome do endpoint, removemos
                 * essa parte de `$urlParts`, pois ela já foi identificada.
                 *
                 * Guardamos o nome encontrado em `$methodName` e a chave em
                 * `$endpointkey`, para consultar suas configurações depois.
                 *
                 * 💡 OBSERVAÇÃO
                 * ┌────────────────────────────────────────────────────┐
                 * │ Note que se nenhuma parte corresponder ao nome do  │
                 * │ endpoint, não removemos a parte de $urlParts.      │
                 * │                                                    │
                 * │ Isso é necessário pois na próxima etapa vamos      │
                 * │ verificar se estas partes remanescentes do `array` │
                 * │ correspondem a possíveis valores de parâmetros do  │
                 * │ endpoint.                                          │
                 * └────────────────────────────────────────────────────┘
                 */

                if ($urlPartAsMethod == $endpointMethodName) {

                    array_shift(self::$urlParts);

                    $methodName = $endpointMethodName;
                    $endpointkey = $key;
                    $endpointExists = true;

                    break;
                }
            }
        }


        /**
         * Com a escolha feita, verificamos se há configurações de um endpoint
         * para processar.
         *
         * Se não houver e a saída for 'file', encerramos este método, pois esse
         * formato de saída pode entregar um arquivo sem depender de um
         * endpoint. O restante do processamento da rota continua normalmente.
         */

        if ($output === 'file' && !$endpointExists) {
            return;
        }


        
        /**
         * Se existir um endpoint, então armazenamos os dados deste endpoint
         * dentro da propriedade $routeData.
         *
         * Também formatamos o nome do método em camelCase e o armazenamos na
         * propriedade `self::$methodName`.
         */

        self::$routeData = $routeData[$endpointkey];
        self::$methodName = Tools::toCamelCase($methodName);

        
        
        /**
         * Reaplicamos as configurações gerais de rota, para garantir que
         * configurações de contexto `route` ou `debug` dentro do endpoint sejam
         * aplicadas.
         *
         * Por fim, chamamos `configureEndpointProperties()` para cuidar das
         * configurações específicas do contexto `endpoint`.
         */

        self::configureRouteProperties();
        self::configureEndpointProperties();
    }



    /**
     * 🟢 DISTRIBUIÇÃO E CONFERÊNCIA DOS PARÂMETROS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Esse método pega as partes que sobraram de `$urlParts`, após a
     * identificação dos nós e do endpoint, e distribui esses valores entre os
     * parâmetros do endpoint, caso tenham sido configurados.
     * 
     * ✔️ EXEMPLO
     * ┌──────────────────────────────────────────────────────────────────┐
     * │ Imagine esta configuração:                                       │
     * │ ┌──────────────────────────────┐                                 │
     * │ │ 'parameters' => '/id/?ordem' │                                 │
     * │ └──────────────────────────────┘                                 │
     * │                                                                  │
     * │ E suponha que estas partes da URL sobraram em $urlParts:         │
     * │ ┌────────────────┐                                               │
     * │ │ ['42', 'nome'] │                                               │
     * │ └────────────────┘                                               │
     * │                                                                  │
     * │ Neste caso, o parâmetro `id` armazenará o valor '42', enquanto o │
     * │ parâmetro `ordem` irá armazenar 'nome'.                          │
     * └──────────────────────────────────────────────────────────────────┘
     * 
     * O método também confere se faltou algum valor obrigatório ou se sobraram
     * partes da URL após o preenchimento dos parâmetros configurados.
     *
     * 
     * _________________________________
     * @return void
     */

    private static function resolveParameters(): void
    {
        /**
         * Declaramos algumas variáveis auxiliares para ajudar na compreensão do
         * código.
         * 
         * Puxamos a string armazenada na configuração `parameters` e usamos
         * `explode()` para separar os nomes dos parâmetros em um array.
         * Depois, `array_filter()` remove as entradas vazias e `array_values()`
         * reorganiza os índices para que sejam consecutivos, começando em zero.
         * 
         * ✔️ EXEMPLO
         * ┌────────────────────────────────────────┐
         * │ No nosso exemplo anterior, a string... │
         * │                                        │
         * │ '/id/?ordem'                           │
         * │                                        │
         * │ ...será convertida pra um array        │
         * │                                        │
         * │ ['id', '?ordem']                       │
         * └────────────────────────────────────────┘
         */
        
        $parametersConfig = Config::get('parameters');

        $parametersList = explode('/', $parametersConfig);
        $parametersList = array_filter($parametersList);
        $parametersList = array_values($parametersList);
        
        /**
         * Depois, fazemos algumas conferências:
         *
         * - `$hasUrlParts` informa se ainda há partes da URL disponíveis
         * 
         * - `$hasParameters` informa se temos parâmetros configurados para
         *   recebê-las.
         *
         * Se existem valores na URL, mas nenhum parâmetro foi definido,
         * marcamos a rota como inválida, pois há dados em excesso que não temos
         * onde encaixar.
         * 
         * ⚠️ IMPORTANTE
         * ┌──────────────────────────────────────────────────────────────────┐
         * │ A invalidação da rota só tem sentido quando a classe de formato  │
         * │ de saída exigir um controller. Quando não exigir, as partes      │
         * │ remanescentes de $urlParts ainda ficam disponíveis para que o    │
         * │ formato de saída decida o que fazer com elas.                    │
         * └──────────────────────────────────────────────────────────────────┘
         * 
         * Neste caso, encerramos o método prematuramente, pois não há nada mais
         * a ser feito.
         */
        
        $hasUrlParts = count(self::$urlParts) > 0;
        $hasParameters = count($parametersList) > 0;

        if ($hasUrlParts && !$hasParameters) {
            self::$isValidRoute = false;
            return;
        }
        
        /**
         * Prosseguimos com o `foreach` que fará a distribuição dos valores
         * armazenados em $urlParts para os parâmetros, um de cada vez.
         */

        foreach ($parametersList as $parameterId) {
                
            /**
             * Em cada volta, verificamos se o nome do parâmetro começa com ?.
             * Se começar, é opcional; caso contrário, é obrigatório.
             *
             * Também atualizamos `$hasUrlParts` porque a lista diminui conforme
             * atribuímos os valores aos parâmetros.
             *
             * Se o parâmetro atual for obrigatório e não houver mais nenhum
             * valor disponível em `$urlParts`, marcamos a rota como inválida.
             * Mesmo assim, continuamos o processamento para registrar todos os
             * parâmetros, incluindo aqueles que ficarão sem valor.
             */
            
            $isRequired = strpos($parameterId, '?') !== 0;
            $hasUrlParts = count(self::$urlParts) > 0;

            if ($isRequired && !$hasUrlParts) {
                self::$isValidRoute = false;
            }
            
            /**
             * Removemos os caracteres `?` do início do nome do parâmetro.
             * Depois, retiramos o primeiro valor de `$urlParts` e o guardamos
             * em `$parameterValue`. Se o array estiver vazio, recebemos `null`.
             */

            $parameterName = ltrim($parameterId, '?');
            $parameterValue = array_shift(self::$urlParts);

            /**
             * Guardamos a associação entre o nome do parâmetro e o valor da
             * parte da URL.
             * 
             * ✔️ EXEMPLO
             * ┌────────────────────────────────────────────────────────────┐
             * │ Voltando ao exemplo do início da descrição deste método, o │
             * │ resultado armazenado na propriedade $parameters após todo  │
             * │ este processamento será:                                   │
             * │ ┌────────────────────────┐                                 │
             * │ │ [                      │                                 │
             * │ │     'id' => '42',      │                                 │
             * │ │     'ordem' => 'nome', │                                 │
             * │ │ ]                      │                                 │
             * │ └────────────────────────┘                                 │
             * │                                                            │
             * │ Se a URL não trouxer 'nome', `ordem` receberá `null`.      │
             * │ Por ser opcional, sua ausência não invalida a rota:        │
             * │ ┌──────────────────────┐                                   │
             * │ │ [                    │                                   │
             * │ │     'id' => '42',    │                                   │
             * │ │     'ordem' => null, │                                   │
             * │ │ ]                    │                                   │
             * │ └──────────────────────┘                                   │
             * └────────────────────────────────────────────────────────────┘
             */
            
            self::$parameters[$parameterName] = $parameterValue;
        }

        
        /**
         * Por último, conferimos se sobrou alguma parte da URL.
         * 
         * Após o preenchimento de todos os parâmetros, a lista em `$urlParts`
         * precisa estar vazia. Se ainda houver valores, significa que a URL
         * trouxe mais partes do que a configuração permite, então a rota também
         * é marcada como inválida.
         */
        
        if (self::$urlParts !== []) {
            self::$isValidRoute = false;
        }
        
        /**
         * ⚠️ IMPORTANTE
         * ┌───────────────────────────────────────────────────────────────┐
         * │ Esse método apenas registra o resultado em `$isValidRoute`; a │
         * │ decisão de interromper o acesso fica para a conferência feita │
         * │ depois na classe `Galastri`.                                  │
         * └───────────────────────────────────────────────────────────────┘
         */
    }



    /**
     * 🟢 MONTAGEM DO NAMESPACE DO CONTROLLER
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Esse método monta, aos poucos, o caminho completo do namespace até a
     * classe do controller, acompanhando a passagem pelos nós da rota, para
     * determinar onde o framework deve procurar a classe do controller
     * responsável pela rota acessada. Para isso, combina os nomes dos nós
     * percorridos com a estrutura de namespaces da aplicação, respeitando uma
     * eventual configuração manual de namespace.
     * 
     * Com isso, ele transforma o caminho reconhecido em uma identificação de
     * classe, como `app\controllers\Admin\Produtos`, que será usada depois por
     * `Galastri` para localizar e executar o controller.
     * 
     *
     * _________________________________
     * @param string $partValue  
     *    Trecho que será tratado para compor o namespace completo da classe
     *    controller. Uma string vazia representa o nó raiz.
     * _________________________________
     * @return void
     */

    private static function resolveNamespace(string $partValue): void
    {
        /**
         * Começamos definindo variáveis auxiliares para facilitar a leitura do
         * código.
         * 
         * -  `$routeData` armazena os dados do nó atual.
         * 
         * -  `$namespace` armazena o caminho construído até o momento.
         * 
         * -  `$removedIndexFromNamespace` indica se a limpeza de 'Index' já foi
         *    realizada. Explicaremos isso melhor mais adiante.
         */
        
        $routeData = self::$routeData;
        $namespace = self::$controllerNamespace;
        $removedIndexFromNamespace = self::$removedIndexFromNamespace;
        
        
        
        /**
         * Aqui verificamos se o nó possui uma configuração `namespace`, que
         * fornece outra base para o namespace.
         * 
         * Por padrão, o namespace acompanha os nomes dos nós já percorridos.
         * Mas o nó atual pode informar uma base própria, substituindo o caminho
         * acumulado até aqui.
         * 
         * Se esta configuração existir, então construímos o caminho com base no
         * que foi informado nela.
         * 
         * ✔️ EXEMPLO
         * ┌──────────────────────────────────────────────────────────────────┐
         * │ Na configuração de rotas:                                        │
         * │                                                                  │
         * │ 'namespace' => 'app\\administracao'                              │
         * │                                                                  │
         * │ É processado e transformado em um array através da função        │
         * │ explode(), separando a string pelas barras invertidas.           │
         * │                                                                  │
         * │ ['app', 'administracao']                                         │
         * └──────────────────────────────────────────────────────────────────┘
         */
        
        if (isset($routeData['namespace'])) {
            
            /**
             * Pegamos a string armazenada na configuração `namespace` e
             * separamos os valores em um array, dividindo com base nas barras
             * invertidas.
             */
            
            
            
            $configuredNamespace = $routeData['namespace'];
            $namespaceParts = explode('\\', $configuredNamespace);
            
            /**
             * Filtramos e armazenamos o array sem valores vazios.
             */
            
            
            
            $namespace = array_filter($namespaceParts);
        }
        
        
        
        /**
         * Neste bloco `if` temos que fazer uma explicação mais detalhada.
         * 
         * O método `resolveNamespace()` é executado para cada nó encontrado,
         * incluindo o nó raiz, acrescentando ao array `$controllerNamespace` o
         * nome de classe correspondente àquele nó.
         * 
         * Por exemplo, em `https://site.com/produtos/listar`:
         * 
         * - `$controllerNamespace` armazenará ['app', 'controllers',
         *   'Produtos'].
         * 
         * - 'listar' fica de fora pois é o endpoint.
         * 
         * - 'app' e 'controllers' são os valores iniciais padrão.
         * 
         * Temos aqui, portanto, o conhecimento de que o controller `Produtos`
         * irá atender esta URL.
         * 
         * Mas e quando a URL for apenas `https://site.com`?
         *
         * Se considerássemos somente as partes preenchidas do caminho, teríamos
         * apenas ['app', 'controllers'], sem indicação de controller. Qual
         * classe atenderia esse acesso?
         * 
         * Para entender isso, precisamos lembrar que `setUrlParts()` sempre
         * gera o array `$urlParts` com uma string vazia na posição [0].
         * 
         * - Para `https://site.com/produtos/listar`, ele gera ['', 'produtos',
         *   'listar'].
         * 
         * - Para `https://site.com`, ele gera apenas [''], já que não há outras
         *   partes pra armazenar.
         * 
         * A string vazia na posição [0] é colocada de propósito: ela representa
         * o nó raiz, por onde começamos a construir o namespace. Nesse ponto,
         * ainda não temos o nome de outro nó para identificar o controller.
         * Por isso, `resolveNamespace()` usa o nome padrão `Index` para
         * identificar o controller da raiz, mesmo quando não houver outras
         * partes no caminho da URL.
         * 
         * `Index`? Por quê?
         * 
         * Porque é isso que se espera de um acesso como `https://site.com`: o
         * acesso à página inicial, à index.
         * 
         * Na montagem padrão, portanto, começamos com `Index` como controller
         * da raiz e ajustamos esse caminho conforme encontramos outros nós.
         * 
         * Portanto:
         * 
         * - Em `https://site.com`, o nome completo da classe é
         *   `app\controllers\Index`.
         * 
         * E aí vem o motivo do processamento abaixo. Esse `Index` inicial
         * representa o controller da raiz e de seus endpoints. Se avançarmos
         * para outro nó, precisamos retirá-lo antes de acrescentar o nome
         * correspondente ao próximo nó.
         *
         * Na montagem padrão, isso acontece na segunda chamada: o namespace
         * já contém ['app', 'controllers', 'Index'], portanto possui mais de
         * duas partes, e a remoção ainda não aconteceu.
         *
         * Sem essa limpeza, formaríamos um caminho como
         * `app\controllers\Index\Produtos`, exigindo uma pasta `Index` para
         * guardar os demais controllers.
         *
         * Registramos essa remoção na propriedade `$removedIndexFromNamespace`
         * para que ela aconteça apenas uma vez. Assim, um nó posterior chamado
         * `/index` poderá acrescentar seu próprio `Index` ao caminho e mantê-lo
         * ali.
         * 
         * Portanto, considerando `produtos` e `controle` como nós, e `listar`
         * e `cadastrar` como endpoints:
         *
         * - Em `https://site.com`, o nome completo da classe será
         *   `app\controllers\Index`, localizada em
         *   `app/controllers/Index.php`.
         *
         * - Em `https://site.com/produtos/listar`, será
         *   `app\controllers\Produtos`, localizada em
         *   `app/controllers/Produtos.php`.
         *
         * - Em `https://site.com/produtos/controle/cadastrar`, será
         *   `app\controllers\Produtos\Controle`, localizada em
         *   `app/controllers/Produtos/Controle.php`.
         *
         * Veja que, ao avançarmos para outro nó, o nome anterior passa a
         * representar uma subpasta no caminho do controller. No último
         * exemplo, `Produtos` representa a pasta e `Controle` é a classe
         * que atenderá o acesso.
         *
         * Esses exemplos consideram o namespace padrão. Se a configuração
         * `namespace` fornecer outra base, a contagem e a filtragem serão
         * feitas sobre a base configurada, que substituiu o caminho acumulado.
         */
        
        if (!$removedIndexFromNamespace && count($namespace) > 2) {
            
        
        
            /**
             * Com a condição acima satisfeita, removemos da lista todas as
             * partes cujo valor seja 'Index' e marcamos a limpeza como feita.
             *
             * Como `$removedIndexFromNamespace` é uma propriedade estática, seu
             * valor permanece disponível entre as chamadas deste método, sem
             * precisar recebê-lo por parâmetro. Nas próximas passagens, a
             * condição encontrará esse indicador como `true` e impedirá que a
             * limpeza seja executada novamente.
             */
            
            $namespace = array_filter($namespace, function ($value) {
                return $value !== 'Index';
            });
            
            self::$removedIndexFromNamespace = true;
        }

        
        
        /**
         * Por fim, preparamos o nome correspondente ao nó atual. A string vazia
         * representa a raiz e recebe o nome Index. Nos demais casos, tratamos o
         * valor para PascalCase: minha-conta, por exemplo, vira MinhaConta.
         */
        
        self::$controllerName =
            $partValue === '' ? 'Index' : Tools::toPascalCase($partValue);

            
            
        /**
         * Concluímos acrescentando o nome da classe à lista de composição do
         * namespace e guardamos o caminho atualizado.
         */
        
        $namespace[] = self::$controllerName;
        self::$controllerNamespace = $namespace;
    }






    /**
     * 🟢 PARTES RESTANTES DA URL
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * @return array<string>  
     *    Retorna um `array` de `strings` com as partes da URL que ainda
     *    sobraram após o processamento da rota.
     */

    public static function getUrlParts(): array
    {
        return self::$urlParts;
    }



    /**
     * 🟢 NOME DO CONTROLLER
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * @return string  
     *    Retorna uma `string` com o nome da classe do controller identificado.
     */

    public static function getControllerName(): string
    {
        return self::$controllerName;
    }



    /**
     * 🟢 PARTES DO NAMESPACE DO CONTROLLER
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * @return array<string>  
     *    Retorna um `array` de `strings` com as partes que compõem o nome
     *    completo da classe do controller.
     */

    public static function getControllerNamespace(): array
    {
        return self::$controllerNamespace;
    }



    /**
     * 🟢 VALORES DOS NÓS DINÂMICOS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * @return array<string,string>  
     *    Retorna um `array` de `strings` com todos os valores recebidos nos nós
     *    dinâmicos, associados aos respectivos nomes.
     */

    public static function getDynamicNodeValues(): array
    {
        return self::$dynamicNodes;
    }



    /**
     * 🟢 VALOR DE UM NÓ DINÂMICO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * @param string $key
     *    É o nome do nó dinâmico sem o prefixo `/?` da configuração da rota.
     * _________________________________
     * @return null|string
     *    Retorna uma `string` com o valor de um nó dinâmico específico ou `null`
     *    se o nome não existir.
     */

    public static function getDynamicNode(string $key): ?string
    {
        return self::$dynamicNodes[$key] ?? null;
    }



    /**
     * 🟢 NOME DO MÉTODO DO CONTROLLER
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * @return string  
     *    Retorna uma `string` com o nome do método do controller escolhido para
     *    atender ao endpoint, já considerando uma possível substituição pela
     *    configuração method.
     */

    public static function getMethodName(): string
    {
        return self::$methodName;
    }



    /**
     * 🟢 VALIDADE DA ROTA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * @return bool  
     *    Retorna `true` se a rota é considerada válida de acordo com a quantidade de
     *    parâmetros ou `false`, em caso negativo.
     */

    public static function isValidRoute(): bool
    {
        return self::$isValidRoute;
    }



    /**
     * 🟢 VALORES DOS PARÂMETROS DA ROTA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * @return array<string,string|null>  
     *    Retorna um `array` com os nomes dos parâmetros da rota associados aos
     *    respectivos valores, que podem ser strings ou `null`.
     */

    public static function getParameterValues(): array
    {
        return self::$parameters;
    }



    /**
     * 🟢 VALOR DE UM PARÂMETRO DA ROTA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * @param string $key  
     *    Nome do parâmetro a ser recuperado.
     * _________________________________
     * @return null|string  
     *    Retorna uma `string` com o valor do parâmetro informado ou `null` se
     *    ele não existir ou estiver sem valor.
     */

    public static function getParameter(string $key): ?string
    {
        return self::$parameters[$key] ?? null;
    }



    /**
     * 🟢 CAMINHO RAIZ DAS ROTAS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * @return string  
     *    Retorna uma `string` com o caminho raiz configurado para as rotas.
     */

    public static function getRootDir(): string
    {
        return self::$rootDir;
    }



    /**
     * 🟢 CAMINHO BASE PARA LINKS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * @return string  
     *    Retorna uma string com o caminho raiz formatado para servir de base na
     *    montagem de links.
     */

    public static function getBaseHref(): string
    {
        return rtrim(self::$rootDir, '/').'/';
    }
}
