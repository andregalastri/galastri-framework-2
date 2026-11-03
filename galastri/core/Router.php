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
     * │ armazenar o nome 'Produto', em PascalCase, após ser tratada.          │
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
     * /?).
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
     * valor 'main' como padrão, para os casos quando não há um `endpoint`
     * explícito na URL.
     *
     * O nome do `endpoint` é adaptado ao formato camelCase e o controller
     * precisa seguir este padrão. Se o `endpoint` for 'nossa-empresa', então o
     * nome do método armazenado aqui será 'nossaEmpresa'.
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
     * │ Parâmetros opcionais (iniciado com ? na configuração de rota) não │
     * │ alteram esta propriedade.                                         │
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
     * Registra se já executamos a retirada de `Index` durante a montagem do
     * namespace de um controller que é a página inicial.
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
     * Guarda os valores recebidos nas partes da URL após o `endpoint`,
     * associados aos nomes definidos na configuração `parameters`.
     *
     * ✔️ EXEMPLO
     * ┌────────────────────────────────────────────────────────────────────────┐
     * │ Por exemplo, se configurarmos `'parameters' => '/tipo/?ordem'` e a URL │
     * │ terminar com `/ativos/nome`, obtemos:                                  │
     * │                                                                        │
     * │ [                                                                      │
     * │     'tipo' => 'ativos',                                                │
     * │     'ordem' => 'nome'                                                  │
     * │ ].                                                                     │
     * └────────────────────────────────────────────────────────────────────────┘
     * 
     * 💡 OBSERVAÇÃO
     * ┌───────────────────────────────────────────────────────────────────────┐
     * │ O ponto de interrogação na configuração dos parâmetros identifica um  │
     * │ parâmetro opcional.                                                   │
     * │                                                                       │
     * │ Quando não há uma parte da URL para preencher um parâmetro opcional,  │
     * │ seu valor é armazenado como null.                                     │
     * │                                                                       │
     * │ Já a ausência de um parâmetro obrigatório (ou seja, sem ?), retornará │
     * │ erro de rota inválida.                                                │
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
         * propriedade passará a armazenar apenas os dados do nó ou `endpoint`
         * que estivermos processando.
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
         * nó final, identificamos o `endpoint` e relacionamos as partes
         * restantes aos parâmetros da rota.
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
         * caso ele apareça do início da string.
         */

        $partsString = Tools::trimLeft($requestPath, self::$rootDir, 1);

        
        
        /**
         * Declaramos `$urlParts` com uma string vazia logo na primeira posição.
         * O motivo disso é que, antes de procurar pelos nós recebidos na URL,
         * precisamos processar as configurações de rota que estão logo no raiz
         * da cadeia.
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
     * reconhecer quando o próximo trecho corresponde a um `endpoint`, pois a
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
         * Ocorre o seguinte: `$urlParts` começa armazenando todas as partes da
         * URL. Quando `resolveNodes()` é chamado, ele vai iniciar uma cadeia de
         * resolução destas partes, trecho a trecho.
         * 
         * A cada parte da URL encontrada nas configurações de rota,
         * `resolveNodes()` chamará `processNodeData()`, que removerá a parte
         * correspondente ao nó atual de `$urlParts` e processará suas
         * configurações e chamará `resolveNodes()` novamente.
         * 
         * O item removido de `$urlParts` é sempre o da posição [0].
         * Isso significa que o item que estava na posição [1] passa a ocupar a
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
         * a) O nó raiz, indicado pela string vazia
         * b) Um nó fixo, cuja chave começa com `/`
         * c) Um endpoint, cuja chave começa com `@`
         * d) Um nó dinâmico, cuja chave começa com `/?`
         */ 
        
        /**
         * a)
         * Começaremos verificando se estamos no começo do processamento de
         * rotas.
         * 
         * Se a string de `$partValue` for vazia, isso indica que esta é a
         * primeira chamada de `resolveNodes()`. Neste caso, a única chave que
         * podemos puxar é a da raiz do array de rotas.
         * 
         * Atribuímos ao `$partName`, neste caso, o valor da chave da raiz.
         * 
         * 
         * b)
         * Porém, se `$partValue` não for vazia, significa que já estamos com o
         * processamento das rotas em andamento.
         * 
         * Neste caso, vamos começar testando se `$partValue` corresponde
         * a um nó fixo.
         * 
         * Atribuímos ao `$partName`, neste caso, o valor de $partValue iniciado
         * com barra /.
         */
        
        $partName = $partValue === '' ? self::$rootDir : '/'.$partValue;

        if (array_key_exists($partName, self::$routeData)) {
            
            /**
             * Se encontrarmos a chave armazenada em `$partName`, substituímos
             * o conteúdo de `$routeData` pelo conteúdo desse nó.
             *
             * Assim, na próxima chamada, procuraremos as próximas partes
             * apenas dentro do nó encontrado.
             *
             * Basicamente, vamos entrando no array em níveis cada vez mais
             * profundos, conforme encontramos os nós do caminho recebido.
             */
            
            self::$routeData = self::$routeData[$partName];
            
            
            
            /**
             * Armazenamos o nome do nó atual na propriedade $nodeName.
             * 
             * ⚠️ IMPORTANTE
             * ┌───────────────────────────────────────────────────────────────┐
             * │ Esta propriedade é usada apenas na mensagem de erro que       │
             * │ informa em qual nó está faltando o `endpoint` principal       │
             * │ `@main`, não tem outra utilidade.                             │
             * └───────────────────────────────────────────────────────────────┘
             */
            
            self::$nodeName = $partName;
            
            
            
            /**
             * Inicia o processamento das configurações do nó encontrado.
             */
            
            self::processNodeData($partValue);
            
            return;
        }

        
        
        /**
         * c)
         * Se o teste para nó fixo não funcionar, então vamos para o próximo
         * teste, verificando se $partValue é um `endpoint`.
         */
        
        if (array_key_exists('@'.$partValue, self::$routeData)) {
            
            /**
             * Se for um `endpoint`, então encerramos a busca por nós aqui. Não
             * faremos mais nada, trataremos do `endpoint` nas próximas etapas.
             */
            
            return;
        }

        
        
        /**
         * d)
         * Se o teste para `endpoint` não funcionar, então faremos um último
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
                 * Atribuímos esses dados à propriedade `self::$routeData`,
                 * para que a próxima chamada procure as próximas partes
                 * apenas dentro desse nó.
                 */
                
                self::$routeData = $routeData;
                
                
                
                /**
                 * Armazenamos o nome do nó atual na propriedade $nodeName.
                 * 
                 * ⚠️ IMPORTANTE
                 * ┌───────────────────────────────────────────────────────────────┐
                 * │ Esta propriedade é usada apenas na mensagem de erro que       │
                 * │ informa em qual nó está faltando o `endpoint` principal       │
                 * │ `@main`, não tem outra utilidade.                             │
                 * └───────────────────────────────────────────────────────────────┘
                 */
                
                self::$nodeName = $key;
                
                
                
                /**
                 * Armazenamos o nome e o valor do nó dinâmico no array
                 * `$dynamicNodes`.
                 * 
                 * Supondo que a chave do nó dinâmico seja `/?apelido` e que
                 * essa parte da URL contiver `maria`, armazenaremos:
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
         * │ Antes:                                                        │
         * │ $urlParts = ['produtos', 'listar', 'ativos'];                 │
         * │                                                               │
         * │ array_shift(self::$urlParts);                                 │
         * │                                                               │
         * │ Depois:                                                       │
         * │ $urlParts = ['listar', 'ativos'];                             │
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
     * contextos `debug` e `route` presentes em `$routeData` no momento
     * da chamada.
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
        
        self::configurePropertiesByContext('node');
        
        if (array_key_exists('controller', self::$routeData)) {
            self::$controllerName = Config::get('controller');

            array_pop(self::$controllerNamespace);

            self::$controllerNamespace[] = self::$controllerName;
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
        self::configurePropertiesByContext('endpoint');
        
        if (array_key_exists('method', self::$routeData)) {
            $methodName = Config::get('method');

            if ($methodName !== '') {
                self::$methodName = ltrim($methodName, '@');
            }
        }

        if (array_key_exists('httpMethod', self::$routeData)) {
            $httpMethod = [];

            foreach (Config::get('httpMethod') as $type => $method) {
                $httpMethod[mb_strtolower($type)] = ltrim($method, '@');
            }

            Config::set('httpMethod', $httpMethod);
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
     * @param string $partValue  
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
     * @return array<string>
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
     * @return array<string>
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
     * @return array<string,string>
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
     * @param string $key  
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
     * @return array<string,string|null>
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
     * @param string $key  
     *    xxx
     * _________________________________
     * @return null|string
     *    xxx.
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
     *    xxx.
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
