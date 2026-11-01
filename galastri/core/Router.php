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
 * Quando recebemos uma URL como /produtos/listar/ativos, precisamos entender o
 * papel de cada parte. 'produtos' pode identificar um controller, 'listar' pode
 * indicar um método e 'ativos' pode ser um valor que esse método utilizará.
 *
 * O arquivo de rotas é quem define essas relações. Nele, as chaves iniciadas
 * com / representam nós, que organizam o caminho até o controller. As chaves
 * iniciadas com @ representam endpoints, que identificam os pontos de acesso
 * aos métodos desse controller.
 *
 * `Router` interpreta esse caminho e reúne as informações necessárias para
 * atender à requisição. Também aplica as configurações encontradas na rota,
 * como a saída escolhida e as opções de autenticação, através da classe
 * `Config`.
 *
 * Além dos nomes fixos, uma rota pode receber valores em nós dinâmicos e em
 * parâmetros após o endpoint. Guardamos esses valores com os nomes definidos
 * nas rotas para que a aplicação possa consultá-los depois.
 *
 * O trabalho desta classe é preparar essas informações. A verificação da
 * existência do controller e de seus métodos, assim como sua execução, fica a
 * cargo das próximas etapas do framework.
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
     * Indica o arquivo PHP em que a aplicação organiza suas rotas e as
     * configurações de cada acesso.
     *
     * Esse arquivo precisa retornar um array. Seus dados fornecem a estrutura
     * que `Router` consulta para interpretar o caminho recebido na URL.
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
     * Armazena o caminho que serve de ponto de partida para as rotas da
     * aplicação, como / ou /intranet.
     *
     * Esse valor vem da primeira chave do array retornado pelo arquivo de
     * rotas. Ele permite preparar a parte da URL que será percorrida e fornecer
     * o endereço base usado nos links da aplicação.
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
     * No início, contém todo o array importado. Conforme encontramos um nó ou
     * endpoint correspondente à URL, passa a guardar o conteúdo daquela
     * entrada. Assim, as próximas buscas ficam restritas às opções disponíveis
     * dentro do caminho já encontrado.
     *
     *
     * _________________________________
     * @var array<string,mixed>
     */
    
    private static array $routeData;
    
    
    
    /**
     * 🟢 PARTES DA URL AINDA DISPONÍVEIS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Armazena as partes do caminho solicitado, separadas pelas barras da URL.
     * A informação após o sinal de interrogação não participa dessa lista.
     *
     * Começamos com uma string vazia na primeira posição para representar o
     * acesso ao nó raiz. Depois, cada parte identificada como nó ou endpoint é
     * retirada do início do array. O que restar será usado na preparação dos
     * parâmetros da rota.
     *
     * Por exemplo, /produtos/listar/ativos começa como ['', 'produtos',
     * 'listar', 'ativos']. Depois de identificar o nó e o endpoint, sobra o
     * valor 'ativos'.
     *
     *
     * _________________________________
     * @var array<string>
     */
    
    private static array $urlParts;
    
    
    
    /**
     * 🟢 NOME DO NÓ ATUAL
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Guarda a chave do último nó encontrado no arquivo de rotas, como
     * /produtos ou /?apelido.
     *
     * Esse nome identifica a entrada da configuração, mesmo quando o trecho
     * recebido na URL é um valor dinâmico diferente. Também o usamos para
     * indicar o nó envolvido na mensagem de ausência do endpoint principal.
     *
     *
     * _________________________________
     * @var string
     */
    
    private static string $nodeName = '';
    
    
    
    /**
     * 🟢 NOME DO CONTROLLER IDENTIFICADO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Armazena o nome da classe de controller associada ao caminho encontrado.
     *
     * Normalmente, esse nome é preparado a partir do nó: 'produtos' corresponde
     * a Produtos e o nó raiz usa Index. A configuração `controller`, quando
     * informada no nó final, pode substituir o nome escolhido.
     *
     * Aqui guardamos apenas o nome da classe. As partes que formam seu
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
     * array começa com 'app' e 'controllers', que representam o local padrão
     * dos controllers da aplicação.
     *
     * Conforme avançamos pelos nós, acrescentamos os nomes das classes
     * correspondentes. Uma configuração `namespace` pode fornecer outra base
     * para essa montagem.
     *
     * Por exemplo, ['app', 'controllers', 'Produtos'] representa a classe
     * app\controllers\Produtos. A classe `Galastri` reúne essas partes com
     * barras invertidas para localizar o controller.
     *
     *
     * _________________________________
     * @var array<string>
     */
    private static array $controllerNamespace = ['app', 'controllers'];
    
    
    
    /**
     * 🟢 VALORES RECEBIDOS NOS NÓS DINÂMICOS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Guarda os valores recebidos nos nós cujas chaves começam com /?, usando o
     * nome definido na rota como chave do array.
     *
     * Se a rota contém /?apelido e a URL informa 'maria' nessa posição,
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
     * Armazena o nome do método do controller escolhido para atender ao
     * endpoint. Começa com 'main', que é o nome padrão quando não encontramos
     * um endpoint explícito na URL.
     *
     * O nome do endpoint é adaptado ao formato dos métodos do controller. A
     * configuração `method`, quando preenchida, permite indicar outro nome para
     * a chamada.
     *
     * A configuração `httpMethod` é preparada separadamente em `Config`, pois
     * associa tipos de requisição HTTP a chamadas adicionais do controller.
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
     * definida para o endpoint.
     *
     * Começa com true e passa a false quando falta um parâmetro obrigatório ou
     * sobra alguma parte da URL sem destino. Um parâmetro opcional pode ficar
     * sem valor sem invalidar o acesso.
     *
     * Esse resultado fica disponível para que `Galastri` decida como
     * prosseguir. A existência da classe de controller e de seus métodos é
     * conferida em outras etapas.
     *
     *
     * _________________________________
     * @var bool
     */
    private static bool $isValidRoute = true;
    
    
    
    /**
     * 🟢 CONTROLE DA REMOÇÃO DE INDEX DO NAMESPACE
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Registra se já executamos a retirada de Index durante a montagem do
     * namespace do controller.
     *
     * Index representa o controller padrão da raiz. Ao avançarmos para outros
     * nós, precisamos evitar que esse nome continue ocupando uma parte
     * intermediária do caminho.
     *
     * Esse controle faz a remoção acontecer apenas uma vez. Depois disso, um
     * nome Index acrescentado por outro nó não será removido novamente por essa
     * mesma etapa.
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
     * Por exemplo, com a definição /tipo/?ordem e os valores 'ativos' e 'nome'
     * na URL, obtemos ['tipo' => 'ativos', 'ordem' => 'nome']. O ponto de
     * interrogação identifica um parâmetro opcional e não faz parte da chave
     * armazenada.
     *
     * Quando não há uma parte da URL para preencher um parâmetro, seu valor
     * fica como null. A ausência de um parâmetro obrigatório também marca a
     * rota como inválida.
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
     * Prepara as informações que relacionam o acesso recebido às rotas da
     * aplicação. Precisamos desse resultado para saber quais configurações
     * usar, qual controller representa o caminho e qual método foi escolhido.
     *
     * Também deixamos disponíveis os valores recebidos nos nós dinâmicos e nos
     * parâmetros, além do resultado da conferência desses parâmetros.
     *
     * Essa preparação é chamada pelo núcleo antes de localizar e executar o
     * controller. Os métodos de consulta de `Router` permitem que as próximas
     * etapas recuperem as informações encontradas.
     *
     *
     * _________________________________
     * @return void
     */
    
    public static function run(): void
    {
        /**
         * Importamos o array do arquivo de rotas. Neste momento, routeData
         * contém a estrutura completa; durante a busca, passará a conter apenas
         * os dados do nó ou endpoint que estivermos processando.
         */

        self::$routeData = Config::importConfig(self::APP_CONFIG_FILE_ROUTES);

        /**
         * Preparamos a raiz e as partes da URL antes de procurar os nós. Assim,
         * a busca pode avançar pelo caminho começando no nó raiz.
         */

        self::configureRootDir();
        self::setUrlParts();
        self::resolveNodes();

        /**
         * Ao terminar a busca dos nós, aplicamos o controller informado no nó
         * final, identificamos o endpoint e relacionamos as partes restantes
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
         * A primeira chave do arquivo identifica a raiz. Uma aplicação pode
         * usar / quando suas rotas começam diretamente no domínio, ou um
         * caminho como /intranet quando há uma base anterior às demais partes.
         */

        $rootDir = array_key_first(self::$routeData);
        
        /**
         * Conferimos o formato antes de guardar o caminho. A expressão aceita a
         * raiz / ou caminhos iniciados por /, com partes formadas por letras,
         * números, sublinhados e hífens. Nesse segundo caso, o caminho termina
         * no nome da última parte.
         *
         * Se o formato não for aceito, a exceção informa o arquivo de rotas e a
         * raiz recebida.
         */

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
     * 🟢 PREPARAÇÃO DAS PARTES DA URL
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Organiza o caminho solicitado em partes que podemos relacionar aos nós,
     * ao endpoint e aos parâmetros da rota.
     *
     * Essa separação permite consumir uma parte por vez, mantendo disponíveis
     * os valores que ainda não foram interpretados. Os dados enviados após o
     * sinal de interrogação pertencem à consulta da URL e não participam desse
     * percurso.
     *
     *
     * _________________________________
     * @return void
     */
    
    private static function setUrlParts(): void
    {
        /**
         * REQUEST_URI pode incluir os dados após o sinal de interrogação, como
         * em /produtos/listar?ordem=nome. Guardamos apenas /produtos/listar,
         * pois é esse caminho que precisamos relacionar às rotas.
         */

        $requestPath = explode('?', $_SERVER['REQUEST_URI'], 2)[0];

        /**
         * Tentamos retirar a raiz do início do caminho, respeitando os limites
         * entre suas partes. O método auxiliar devolve o caminho inteiro se não
         * encontrar uma correspondência adequada.
         */

        $parts = self::removePrefixOnce($requestPath, self::$rootDir);

        /**
         * A primeira posição vazia representa a raiz. Ela permite que
         * resolveNodes() processe o nó raiz antes de avançar para os nomes
         * recebidos na URL.
         */

        $urlParts = [''];

        /**
         * Separamos o caminho pelas barras e guardamos as partes preenchidas. A
         * string '0' também é preservada, porque a comparação com a string
         * vazia considera o tipo e o conteúdo.
         *
         * Por exemplo, /produtos/listar fornece ['', 'produtos', 'listar'],
         * incluindo a posição que reservamos para a raiz.
         */

        foreach (explode('/', $parts) as $partValue) {
            if (!is_null($partValue) && $partValue !== '') {
                $urlParts[] = $partValue;
            }
        }

        self::$urlParts = $urlParts;
    }
    
    
    
    /**
     * 🟢 IDENTIFICAÇÃO DOS NÓS DA ROTA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Relaciona as partes ainda disponíveis da URL aos nós definidos no arquivo
     * de rotas. Cada nó encontrado determina onde continuamos procurando e
     * participa da escolha do controller.
     *
     * Os nós podem ter nomes fixos, como /produtos, ou receber valores através
     * de uma definição dinâmica, como /?apelido. Também precisamos reconhecer
     * quando o próximo trecho corresponde a um endpoint, pois a preparação
     * desse acesso pertence a outra etapa.
     *
     * Esse método permite percorrer nós dentro de outros nós, acompanhando a
     * organização que a aplicação definiu para suas rotas.
     *
     *
     * _________________________________
     * @return void
     */
    
    private static function resolveNodes(): void
    {
        /**
         * O primeiro elemento é a próxima parte que precisamos interpretar. Se
         * a lista estiver vazia, não há outro nó para procurar neste momento.
         */

        $partValue = self::$urlParts[0] ?? null;

        if ($partValue === null) {
            return;
        }

        /**
         * Na posição vazia inicial, procuramos a chave da raiz. Nas demais
         * posições, acrescentamos / para formar a chave de um nó fixo, como
         * /produtos.
         */

        $routeName = $partValue === '' ? self::$rootDir : '/'.$partValue;

        /**
         * Uma correspondência exata tem preferência. Ao encontrá-la, passamos a
         * trabalhar com os dados daquele nó e solicitamos sua preparação.
         *
         * processNodeData() retirará esta parte da lista e chamará
         * resolveNodes() novamente para procurar os próximos níveis.
         */

        if (array_key_exists($routeName, self::$routeData)) {
            self::$routeData = self::$routeData[$routeName];
            self::$nodeName = $routeName;
            self::processNodeData($partValue);
            return;
        }

        /**
         * Se o mesmo trecho identifica um endpoint do nível atual, encerramos a
         * busca dos nós sem retirar esse trecho da URL. resolveEndpoints()
         * precisará dele para escolher o ponto de acesso.
         *
         * Essa conferência vem antes da busca dinâmica para evitar que um
         * endpoint conhecido seja guardado como um valor variável.
         */

        if (array_key_exists('@'.$partValue, self::$routeData)) {
            return;
        }

        /**
         * Sem um nó fixo ou endpoint correspondente, procuramos uma chave
         * iniciada com /?. Ela representa um nó que pode receber um valor
         * variável nessa posição da URL.
         *
         * Por exemplo, /?apelido pode receber 'maria'. Guardamos 'maria' em
         * dynamicNodes['apelido'], mas usamos a chave /?apelido para preparar o
         * nome da classe.
         *
         * Usamos o primeiro nó dinâmico encontrado nesse nível. Se não houver
         * nenhum, a parte permanece na URL para as próximas etapas.
         */

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
     * 🟢 PREPARAÇÃO DO NÓ ENCONTRADO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Integra o nó encontrado ao caminho que estamos preparando. As
     * configurações daquele nível passam a fazer parte do acesso, e o nó
     * fornece a informação usada na montagem do namespace do controller.
     *
     * Essa preparação também permite continuar a busca nos níveis seguintes da
     * rota, mantendo o mesmo tratamento para a raiz, os nós fixos e os nós
     * dinâmicos.
     *
     *
     * _________________________________
     * @param string $className  
     *    Identificação usada para preparar o nome do controller. Recebe uma
     *    string vazia para a raiz, o trecho da URL para um nó fixo ou a chave
     *    da definição para um nó dinâmico, como /?apelido.
     * _________________________________
     * @return void
     */
    
    private static function processNodeData(string $className): void
    {
        /**
         * O nó já foi reconhecido, então retiramos a parte que o representa. A
         * próxima parte da URL passa a ocupar a posição 0.
         */

        array_shift(self::$urlParts);

        /**
         * Aplicamos as opções desse nó e incorporamos seu nome à localização do
         * controller. Depois, retomamos a busca dentro dos dados do nó que
         * acabamos de encontrar.
         */

        self::configureRouteProperties();
        self::resolveNamespace($className);
        self::resolveNodes();
    }
    
    
    
    /**
     * 🟢 APLICAÇÃO DAS CONFIGURAÇÕES DA ROTA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Disponibiliza em `Config` as opções definidas no nível da rota que
     * estamos processando. Essas opções orientam o atendimento da requisição,
     * como a saída usada, as regras de autenticação e os caminhos dos arquivos.
     *
     * O método atende tanto aos nós quanto ao endpoint escolhido. Assim, uma
     * configuração informada em um nível mais específico pode atualizar o valor
     * deixado pelos níveis anteriores. Quando a opção não aparece naquele
     * nível, seu valor atual permanece.
     *
     * Os valores recebidos continuam sujeitos às regras de `Definition`.
     * Algumas falhas também recebem mensagens específicas do roteamento para
     * identificar o arquivo e o valor envolvidos.
     *
     *
     * _________________________________
     * @return void
     */
    
    private static function configureRouteProperties(): void
    {
        /**
         * Procuramos as opções de depuração nos dados atuais da rota. Quando
         * uma opção aparece, Config::set() confere seu valor antes de
         * atualizá-la.
         *
         * Algumas chamadas também passam um segundo valor. Ele é tratado pela
         * condição atual de setConfigIfExists(), explicada naquele método; não
         * representa uma redefinição automática a cada nó.
         */

        self::setConfigIfExists('displayErrors', false);
        self::setConfigIfExists('stopOnNonFatalErrors', true);
        // self::setConfigIfExists('useErrorHandler', true);
        self::setConfigIfExists('showTrace', false);
        // self::setConfigIfExists('performanceAnalysis', false);
        self::setConfigIfExists('createLogsOnError', true);

        /**
         * Aplicamos as opções gerais de atendimento. Uma opção ausente neste
         * nível mantém o valor que já estiver em Config, seja ele o padrão ou
         * um valor atribuído por um nó anterior.
         */

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

        /**
         * O namespace precisa respeitar a definição da configuração. Se a
         * atribuição falhar, usamos uma mensagem específica de roteamento,
         * incluindo o caminho do arquivo e o valor rejeitado.
         *
         * O valor fica disponível em testedValue nos dados da exceção lançada
         * pela validação.
         */

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

        /**
         * As opções de cache também são conferidas pelas regras da configuração
         * antes de ficarem disponíveis para a saída.
         */

        self::setConfigIfExists('browserCache');

        /**
         * Os caminhos de template, pasta de visualizações e pasta de arquivos
         * podem falhar na validação de suas configurações. Cada bloco captura
         * essa falha e prepara a mensagem prevista aqui para aquele caso, com o
         * arquivo de rotas e o valor recebido.
         */

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

        /**
         * Por fim, aplicamos as opções usadas na entrega de arquivos e na
         * inclusão dos metadados do projeto na resposta.
         */

        self::setConfigIfExists('isDownloadableFile');
        self::setConfigIfExists('allowedFileExtensions');
        self::setConfigIfExists('permissionFailMessage');
        self::setConfigIfExists('validateMimeType');
        self::setConfigIfExists('forceMimeType');
        self::setConfigIfExists('includeProjectMetadata');
    }
    
    
    
    /**
     * 🟢 APLICAÇÃO DO CONTROLLER DEFINIDO NO NÓ
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Permite que o nó final indique explicitamente qual classe de controller
     * deve representar aquele acesso.
     *
     * Essa opção é útil quando queremos manter um nome no endereço e usar outro
     * nome de classe. O valor informado em `controller` substitui o nome
     * preparado a partir do nó, preservando as partes anteriores do namespace.
     *
     *
     * _________________________________
     * @return void
     */
    
    private static function configureNodeProperties(): void
    {
        /**
         * Se a configuração controller estiver presente e sua atribuição for
         * concluída, ela fornece o nome que usaremos para a classe. Retiramos o
         * último elemento do namespace e acrescentamos o nome informado,
         * mantendo o caminho anterior.
         */

        if(self::setConfigIfExists('controller')) {
            self::$controllerName = self::$routeData['controller'];
            
            array_pop(self::$controllerNamespace);

            self::$controllerNamespace[] = self::$routeData['controller'];
        }
    }
    
    
    
    /**
     * 🟢 APLICAÇÃO DAS CONFIGURAÇÕES DO ENDPOINT
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Prepara as opções específicas do endpoint escolhido: o nome do método
     * principal, as associações com os tipos de requisição HTTP, a definição
     * dos parâmetros e o arquivo de visualização.
     *
     * Essas opções permitem que o nome usado na URL seja diferente do método do
     * controller e que o mesmo endpoint tenha chamadas adicionais conforme a
     * requisição recebida.
     *
     * Também fornecem as informações que serão usadas para relacionar os
     * valores restantes da URL aos parâmetros definidos pela aplicação.
     *
     *
     * _________________________________
     * @return void
     */
    
    private static function configureEndpointProperties(): void
    {
        /**
         * O endpoint já forneceu um nome de método. Uma configuração method
         * preenchida permite substituí-lo. Retiramos os sinais @ do início
         * porque a chamada no controller utiliza apenas o nome do método.
         */

        if (self::setConfigIfExists('method') && self::$routeData['method'] !== '') {
            self::$methodName = ltrim(self::$routeData['method'], '@');
        }

        /**
         * Em httpMethod, cada chave identifica um tipo de requisição HTTP e
         * cada valor indica um método adicional do controller.
         *
         * Padronizamos as chaves em minúsculas e retiramos @ do início dos
         * nomes dos métodos. Por exemplo, ['POST' => '@salvar'] passa a ['post'
         * => 'salvar']. Depois, guardamos o array em Config para as próximas
         * etapas.
         */

        if (isset(self::$routeData['httpMethod'])) {
            $httpMethod = [];

            foreach(self::$routeData['httpMethod'] as $type => $method) {
                $httpMethod[mb_strtolower($type)] = ltrim($method, '@');
            }

            Config::set('httpMethod', $httpMethod);
        }

        /**
         * A definição parameters descreve os nomes e a presença obrigatória ou
         * opcional dos valores que esperamos após o endpoint. Uma falha nessa
         * atribuição recebe a mensagem específica para parâmetros de endpoint.
         */

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

        /**
         * viewFile pode indicar o arquivo de visualização do endpoint. Se seu
         * valor for rejeitado, informamos o arquivo de rotas e o conteúdo que
         * falhou na validação.
         */

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
     * 🟢 IDENTIFICAÇÃO DO ENDPOINT
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Define o ponto de acesso dentro do nó encontrado. Um endpoint como
     * @listar relaciona o trecho 'listar' da URL ao método que atenderá à
     * requisição.
     *
     * Quando nenhum endpoint da URL corresponde às opções daquele nó, usamos
     * main como escolha padrão. As partes que permanecerem na lista ficam
     * disponíveis para a preparação dos parâmetros.
     *
     * Para saídas diferentes de file, o nó precisa declarar o endpoint
     * principal. A saída file pode continuar sem uma entrada de endpoint
     * correspondente, pois a resposta pode depender de um arquivo em disco.
     *
     *
     * _________________________________
     * @return void
     */
    
    private static function resolveEndpoints(): void
    {
        /**
         * Para saídas diferentes de file, conferimos se há uma chave principal
         * entre os dados do nó. A expressão procura chaves que começam com
         * @main e têm o fim do texto ou dois-pontos logo após esse nome.
         *
         * Se não houver essa entrada, lançamos uma exceção antes de tentar
         * selecionar o endpoint.
         */

        if (Config::get('output') !== 'file' && count(preg_grep('/^@main(:|$)/', array_keys(self::$routeData))) == 0) {
            throw new Exception(
                Message::get("ROUTER_MAIN_METHOD_NOT_FOUND"),
                [
                    str_replace(PROJECT_DIR.'/', '', self::APP_CONFIG_FILE_ROUTES),
                    self::$nodeName,
                ]
            );
        }

        /**
         * main é a escolha inicial. Só mudaremos esse nome se a próxima parte
         * da URL corresponder a um dos endpoints encontrados no nó.
         */

        $methodName = 'main';

        /**
         * Percorremos as entradas iniciadas com @ e comparamos seu nome, sem
         * esse sinal, com a próxima parte da URL.
         *
         * Quando há correspondência, retiramos essa parte da URL e guardamos o
         * endpoint escolhido. Se não houver, mantemos main e deixamos a parte
         * disponível para os parâmetros.
         */

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

        /**
         * A saída file pode prosseguir sem uma entrada para o endpoint
         * escolhido. Nesse caso, encerramos este método mantendo os dados do nó
         * e as partes da URL que ainda estão disponíveis na lista.
         */

        if (Config::get('output') === 'file' && !isset(self::$routeData['@'.$methodName])) {
            return;
        }
        
        /**
         * Passamos a trabalhar com os dados do endpoint e aplicamos suas opções
         * gerais. Como esse nível é mais específico, ele pode atualizar valores
         * anteriormente informados nos nós.
         */

        self::$routeData = self::$routeData['@'.$methodName];
        self::configureRouteProperties();
        
        /**
         * Adaptamos o nome do endpoint ao formato dos métodos do controller,
         * como listar-produtos para listarProdutos. Depois, aplicamos suas
         * opções específicas, incluindo uma possível substituição através de
         * method.
         */

        self::$methodName = Tools::toCamelCase($methodName);
        self::configureEndpointProperties();
    }
    
    
    
    /**
     * 🟢 PREPARAÇÃO DOS PARÂMETROS DA ROTA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Relaciona as partes que sobraram da URL aos nomes definidos na
     * configuração `parameters`. Esse resultado permite que a aplicação
     * consulte cada valor pelo nome escolhido na rota.
     *
     * Além de guardar os valores, precisamos conferir se o acesso atende à
     * quantidade de parâmetros esperada. A falta de um parâmetro obrigatório ou
     * a presença de partes excedentes marca a rota como inválida.
     *
     * Os parâmetros cujos nomes começam com ? são opcionais e podem ficar sem
     * valor. Esse sinal identifica a regra de presença, mas não faz parte do
     * nome usado nas consultas.
     *
     *
     * _________________________________
     * @return void
     */
    
    private static function resolveParameters(): void
    {
        /**
         * A configuração parameters pode conter uma sequência como
         * /tipo/?ordem. Separamos seus nomes pelas barras e organizamos a lista
         * de posições a preencher.
         *
         * O sinal ? no início de um nome será usado para reconhecer os
         * parâmetros que podem ficar sem valor.
         */

        $parametersValues = array_values(array_filter(explode('/', Config::get('parameters'))));

        /**
         * Se ainda há partes na URL, mas a configuração não define nenhum
         * parâmetro, esses valores não têm um destino previsto. Marcamos a rota
         * como inválida.
         */

        if (count(self::$urlParts) > 0 && count($parametersValues) <= 0) {
            self::$isValidRoute = false;
        }

        /**
         * Relacionamos cada nome à próxima parte disponível da URL. Um nome sem
         * ? exige um valor; se a lista já acabou, marcamos a rota como
         * inválida.
         *
         * array_shift() fornece e retira a primeira parte. Quando o array está
         * vazio, fornece null. Guardamos esse resultado também para os
         * parâmetros opcionais que não foram recebidos, retirando ? do nome
         * usado como chave.
         */

        foreach ($parametersValues as $parameterId) {
            if (strpos($parameterId, '?') !== 0 && count(self::$urlParts) <= 0) {
                self::$isValidRoute = false;
            }

            self::$parameters[ltrim($parameterId, '?')] = array_shift(self::$urlParts);
        }

        /**
         * Depois de preencher todos os parâmetros definidos, não deve restar
         * nenhuma parte sem destino. Qualquer sobra também invalida a rota.
         */

        if (self::$urlParts !== []) {
            self::$isValidRoute = false;
        }
    }
    
    
    
    /**
     * 🟢 MONTAGEM DO NAMESPACE DO CONTROLLER
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Prepara o caminho de classe associado ao nó que acabamos de encontrar.
     * Precisamos reunir essa informação para que o núcleo possa localizar o
     * controller depois da interpretação das rotas.
     *
     * A montagem respeita a base padrão app\controllers ou uma base informada
     * pela configuração `namespace`. Os nomes dos nós permitem representar
     * controllers organizados em diferentes níveis.
     *
     * Para um nó dinâmico, usamos o nome definido na rota para formar a classe.
     * O valor variável recebido na URL permanece guardado separadamente para
     * consulta.
     *
     *
     * _________________________________
     * @param string $partValue  
     *    Identificação do nó usada para formar o nome da classe. Uma string
     *    vazia representa a raiz, e uma chave como /?apelido fornece o nome do
     *    nó dinâmico.
     * _________________________________
     * @return void
     */
    
    private static function resolveNamespace(string $partValue): void
    {
        /**
         * Uma configuração namespace no nó atual fornece outra base para a
         * localização do controller. Separamos suas partes pelas barras
         * invertidas e descartamos os elementos vazios antes de continuar a
         * montagem.
         */

        if (isset(self::$routeData['namespace'])) {
            self::$controllerNamespace = array_filter(explode('\\', self::$routeData['namespace']));
        }

        /**
         * O acesso à raiz acrescenta Index ao caminho padrão. Ao avançar para
         * outro nó, retiramos esse nome intermediário para que /produtos
         * corresponda a app\controllers\Produtos, e não a
         * app\controllers\Index\Produtos.
         *
         * Executamos o filtro apenas na primeira vez em que a base tiver mais
         * de duas partes. Ele retira os elementos iguais a Index e registra que
         * essa etapa já aconteceu.
         */

        if (!self::$removedIndexFromNamespace && count(self::$controllerNamespace) > 2) {
            self::$removedIndexFromNamespace = true;

            self::$controllerNamespace = array_filter(self::$controllerNamespace, function($value) {
                return $value !== 'Index';
            });
        }

        /**
         * A raiz usa o nome Index. Nos demais nós, adaptamos a identificação
         * para o formato das classes, como produtos para Produtos ou /?apelido
         * para Apelido.
         *
         * Por fim, acrescentamos esse nome ao array que representa o namespace
         * completo do controller.
         */

        self::$controllerName = $partValue === '' ? 'Index' : Tools::toPascalCase($partValue);

        self::$controllerNamespace[] = self::$controllerName;
    }

    
    
    
    /**
     * 🟢 REMOÇÃO DO CAMINHO BASE NO INÍCIO DA URL
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Permite separar o caminho base da aplicação do restante do endereço que
     * precisamos interpretar.
     *
     * A correspondência precisa respeitar o limite entre as partes do caminho.
     * Uma base /app pode ser retirada de /app/produtos, mas não de
     * /aplicacao/produtos.
     *
     * Apenas o início do texto é considerado. Se não houver uma correspondência
     * que atenda a essa condição, o texto recebido é devolvido inteiro.
     *
     *
     * _________________________________
     * @param string $string  
     *    Caminho do qual tentaremos retirar o prefixo.
     *
     * @param string $prefix  
     *    Trecho esperado no início do caminho, respeitando o fim do texto ou
     *    uma barra logo após ele.
     * _________________________________
     * @return string
     *    Retorna o restante do caminho após a remoção ou a string original
     *    quando as condições não forem atendidas.
     */

    private static function removePrefixOnce(string $string, string $prefix): string
    {
        /**
         * O prefixo precisa estar no início. Depois dele, exigimos o fim da
         * string ou uma barra, pois uma base /app não deve retirar o início de
         * uma parte maior, como /aplicacao.
         *
         * Quando as condições são atendidas, retornamos o trecho que começa
         * logo após o prefixo. Caso contrário, devolvemos a string recebida.
         */

        if (str_starts_with($string, $prefix) && (strlen($string) === strlen($prefix) || $string[strlen($prefix)] === '/')) {
            return substr($string, strlen($prefix));
        }

        return $string;
    }

    
    
    /**
     * 🟢 APLICAÇÃO DE UMA CONFIGURAÇÃO PRESENTE NA ROTA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Permite atualizar uma configuração quando ela aparece nos dados da rota
     * em processamento. Esse recurso é compartilhado pelas etapas que aplicam
     * as opções dos nós e dos endpoints.
     *
     * Usamos o retorno para saber se houve uma atribuição. Isso permite, por
     * exemplo, atualizar o nome do controller apenas quando a configuração
     * correspondente foi encontrada e aceita.
     *
     *
     * _________________________________
     * @param string $configName  
     *    Nome da configuração que será procurada nos dados da rota e atualizada
     *    através de Config::set().
     *
     * @param mixed $fallback  
     *    Segundo argumento opcional da chamada, embora não esteja declarado na
     *    assinatura. Só é considerado quando seu valor é diferente de null e
     *    Config::notExists() confirma que o nome não está registrado. A
     *    tentativa de atribuição continua sujeita à exigência de existência
     *    feita por Config::set().
     * _________________________________
     * @return bool
     *    Retorna true se uma atribuição for concluída ou false se nenhuma
     *    condição de atribuição for atendida. Se Config::set() lançar uma
     *    exceção, o método é interrompido sem retornar um booleano.
     */
    
    private static function setConfigIfExists(string $configName /*, [1]*/): bool
    {
        /**
         * Primeiro procuramos a configuração nos dados atuais da rota. isset()
         * considera a entrada presente apenas quando ela existe e seu valor é
         * diferente de null.
         *
         * Se encontrarmos esse valor, solicitamos a atualização em Config. Só
         * retornamos true depois que Config::set() concluir a atribuição; uma
         * falha de validação interrompe o método por exceção.
         */

        if (isset(self::$routeData[$configName])) {
            Config::set($configName, self::$routeData[$configName]);
            return true;
        }
        
        /**
         * Este segundo valor não aparece como um parâmetro declarado na
         * assinatura. O PHP, porém, permite recuperar os argumentos passados à
         * chamada com func_get_args(), inclusive os adicionais.
         *
         * Por exemplo, na chamada setConfigIfExists('showTrace', false),
         * func_get_args() fornece ['showTrace', false]. A posição 0 contém o
         * nome e a posição 1 contém o valor adicional.
         *
         * O teste abaixo só considera esse valor quando ele foi informado e é
         * diferente de null. Um valor false passa por isset(); um valor null
         * não passa.
         *
         * A segunda condição também exige que Config::notExists() confirme a
         * ausência desse nome entre as configurações registradas.
         *
         * Há uma consequência importante nessa implementação: Config::set()
         * exige que a configuração já exista. Como este bloco só entra quando
         * ela não existe, a tentativa abaixo lança uma exceção de configuração
         * ausente. Portanto, o segundo argumento não cria uma configuração nem
         * substitui automaticamente um valor existente.
         */

        if (isset(func_get_args()[1]) && Config::notExists($configName)) {
            Config::set($configName, func_get_args()[1]);
            return true;
        }

        return false;
    }

    
    
    /**
     * 🔶 CONSULTA DAS PARTES RESTANTES DA URL
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Retorna as partes da URL que ainda estão em `$urlParts`. Como os nós, o
     * endpoint e os valores dos parâmetros são retirados da lista durante o
     * processamento, essa lista representa o que ainda não recebeu um destino.
     *
     * Ela também pode ser utilizada pela saída de arquivos para consultar
     * partes restantes do caminho solicitado.
     *
     *
     * _________________________________
     * @return array<string>
     *    Retorna o array de partes restantes da URL, preservando a ordem em que
     *    aparecem no caminho.
     */

    public static function getUrlParts(): array
    {
        return self::$urlParts;
    }
    
    
    
    /**
     * 🔶 CONSULTA DO NOME DO CONTROLLER
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Retorna o nome da classe de controller identificado durante a
     * interpretação da rota.
     *
     * O valor corresponde apenas ao nome da classe. As partes de seu namespace
     * completo são fornecidas por `getControllerNamespace()`.
     *
     *
     * _________________________________
     * @return string
     *    Retorna o nome da classe de controller preparado para o acesso.
     */
    
    public static function getControllerName(): string
    {
        return self::$controllerName;
    }
    
    
    
    /**
     * 🔶 CONSULTA DO NAMESPACE DO CONTROLLER
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Retorna as partes que formam o nome completo da classe de controller. A
     * classe `Galastri` utiliza esse resultado para preparar a localização do
     * controller.
     *
     * Os elementos são fornecidos separadamente, como ['app', 'controllers',
     * 'Produtos'], para que possam ser reunidos conforme o uso necessário.
     *
     *
     * _________________________________
     * @return array<string>
     *    Retorna o array com as partes do namespace, incluindo o nome da classe
     *    ao final.
     */
    
    public static function getControllerNamespace(): array
    {
        return self::$controllerNamespace;
    }
    
    
    
    /**
     * 🔶 CONSULTA DOS VALORES DOS NÓS DINÂMICOS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Retorna os valores recebidos nos nós dinâmicos, associados aos nomes
     * usados em suas definições.
     *
     * Por exemplo, o nó /?apelido acessado com o valor 'maria' fornece a
     * entrada ['apelido' => 'maria'].
     *
     *
     * _________________________________
     * @return array<string,string>
     *    Retorna o array com os nomes dos nós dinâmicos e seus valores. O array
     *    fica vazio quando nenhum nó dinâmico foi processado.
     */
    
    public static function getDynamicNodeValues(): array
    {
        return self::$dynamicNodes;
    }
    
    
    
    /**
     * 🔶 CONSULTA DO VALOR DE UM NÓ DINÂMICO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Permite recuperar um dos valores recebidos nos nós dinâmicos usando o
     * nome definido na rota.
     *
     * A chave precisa estar entre os nós processados. Este método acessa
     * diretamente o valor armazenado e não fornece um retorno alternativo
     * quando o nome não existe.
     *
     *
     * _________________________________
     * @param string $key  
     *    Nome do nó dinâmico, sem /?. Para uma definição /?apelido, usamos a
     *    chave apelido.
     * _________________________________
     * @return string
     *    Retorna o valor recebido na URL para o nó dinâmico informado.
     */
    
    public static function getDynamicNode(string $key): string
    {
        return self::$dynamicNodes[$key];
    }
    
    
    
    /**
     * 🔶 CONSULTA DO NOME DO MÉTODO DA ROTA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Retorna o nome do método principal escolhido para o controller,
     * considerando o endpoint e uma eventual configuração `method`.
     *
     * A classe `Galastri` consulta esse nome para preparar a chamada que será
     * realizada no controller.
     *
     *
     * _________________________________
     * @return string
     *    Retorna o nome do método principal preparado para a rota.
     */
    
    public static function getMethodName(): string
    {
        return self::$methodName;
    }
    
    
    
    /**
     * 🔶 CONSULTA DO RESULTADO DA CONFERÊNCIA DA ROTA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Informa se a preparação dos parâmetros encontrou partes excedentes na URL
     * ou a ausência de valores obrigatórios.
     *
     * A classe `Galastri` consulta esse resultado para decidir se deve tratar o
     * acesso como não encontrado, considerando também a saída escolhida.
     *
     *
     * _________________________________
     * @return bool
     *    Retorna true enquanto a conferência dos parâmetros não tiver
     *    invalidado a rota ou false quando essa conferência encontrar uma
     *    incompatibilidade.
     */
    
    public static function isValidRoute(): bool
    {
        return self::$isValidRoute;
    }
    
    
    
    /**
     * 🔶 CONSULTA DOS VALORES DOS PARÂMETROS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Retorna os valores preparados a partir da configuração `parameters` e das
     * partes da URL que restaram após a identificação do endpoint.
     *
     * Cada chave corresponde ao nome definido na rota. Os sinais de
     * interrogação que indicam parâmetros opcionais não fazem parte dessas
     * chaves.
     *
     *
     * _________________________________
     * @return array<string,string|null>
     *    Retorna o array com os nomes dos parâmetros e seus valores. As
     *    posições sem um valor recebido contêm null.
     */
    
    public static function getParameterValues(): array
    {
        return self::$parameters;
    }
    
    
    
    /**
     * 🔶 CONSULTA DO VALOR DE UM PARÂMETRO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Permite recuperar um parâmetro da rota pelo nome, sem precisar consultar
     * o array completo.
     *
     * Retornamos null tanto quando a chave não existe quanto quando ela foi
     * preparada sem um valor recebido. A consulta, portanto, não distingue
     * essas duas situações.
     *
     *
     * _________________________________
     * @param string $key  
     *    Nome do parâmetro, sem o sinal de interrogação usado nas definições
     *    opcionais.
     * _________________________________
     * @return null|string
     *    Retorna o valor guardado para o parâmetro ou null quando não houver um
     *    valor disponível para essa chave.
     */
    
    public static function getParameter(string $key): ?string
    {
        return self::$parameters[$key] ?? null;
    }
    
    
    
    /**
     * 🔶 CONSULTA DO CAMINHO RAIZ
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Retorna o caminho raiz definido no arquivo de rotas e conferido durante a
     * preparação do acesso.
     *
     * Esse valor representa a base das rotas da aplicação, como / ou /intranet.
     *
     *
     * _________________________________
     * @return string
     *    Retorna o caminho raiz armazenado em $rootDir.
     */
    
    public static function getRootDir(): string
    {
        return self::$rootDir;
    }
    
    
    
    /**
     * 🔶 CONSULTA DO ENDEREÇO BASE PARA LINKS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Fornece o caminho raiz com uma barra ao final para que ele possa ser
     * usado como base dos links da aplicação.
     *
     * Por exemplo, uma raiz /intranet fornece /intranet/. Quando a raiz já é /,
     * o resultado continua sendo uma única barra.
     *
     *
     * _________________________________
     * @return string
     *    Retorna o caminho raiz terminado em uma única barra, sem incluir o
     *    domínio do endereço.
     */
    
    public static function getBaseHref(): string
    {
        /**
         * Retiramos as barras finais da raiz antes de acrescentar uma única
         * barra. Assim, tanto / quanto /intranet fornecem uma base terminada da
         * mesma forma.
         */

        return rtrim(self::$rootDir, '/').'/';
    }
}
