<?php
/**
 * -----------------------------------------------------------------------------
 * 📂
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                 ARQUIVO /galastri/modules/Controller.php                  ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 * Lá em `Galastri`, vimos que a execução da aplicação começa quando criamos o
 * controller. Ao fazer isso, o PHP chama o construtor que ele herdou da classe
 * definida neste arquivo.
 *
 * É nesse construtor que chamamos os métodos da aplicação e reunimos os dados
 * que a classe de saída poderá consultar depois.
 * ─────────────────────────────────────────────────────────────────────────────
 */



/**
 * 🟢 NAMESPACE DA CLASSE
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * A classe `Controller` faz parte dos módulos do framework. Ela serve de base
 * para os controllers que criamos em `app/controllers`.
 */

namespace galastri\modules;



/**
 * 🟢 CLASSES UTILIZADAS
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Quando chegamos aqui, o framework já sabe qual rota foi acessada e quais
 * métodos devem rodar. As classes abaixo nos dão acesso a essas informações e
 * às configurações que podemos consultar ou ajustar durante a execução.
 *
 * ┌────────────┬──────────────────────────────────────────────────────────────┐
 * │ Classe     │ Uso                                                          │
 * ├────────────┼──────────────────────────────────────────────────────────────┤
 * │ `Router`   │ Fornece os parâmetros e os valores dos nós dinâmicos da      │
 * │            │ rota, além do caminho raiz e da base usada nos links.        │
 * ├────────────┼──────────────────────────────────────────────────────────────┤
 * │ `Galastri` │ Informa quais métodos foram escolhidos e se há um método     │
 * │            │ associado ao tipo de requisição HTTP para executar.          │
 * ├────────────┼──────────────────────────────────────────────────────────────┤
 * │ `Config`   │ Permite consultar e alterar configurações da rota. Também    │
 * │            │ informa se a resposta deve incluir os dados do projeto.      │
 * └────────────┴──────────────────────────────────────────────────────────────┘
 */

use galastri\core\Router;
use galastri\core\Galastri;
use galastri\core\config\Config;



/**
 * 🟢 CLASSE Controller
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Quando abrimos a página inicial do framework, `Galastri` cria um objeto da
 * classe `Index`. Se você abrir o arquivo desse controller, vai ver que ele
 * herda `Controller` e que seu método `main()` só retorna `[]`.
 *
 * - Aproveite para dar uma olhada no `Index` e depois volte aqui.  
 *   🔗 LINK: /app/controllers/Index.php
 *
 * O `Index` define o que fazer quando `main()` é chamado. Quem faz essa chamada
 * é o construtor que ele herdou de `Controller`. É por isso que aquela linha
 * com `new`, lá em `Galastri`, já põe a aplicação pra rodar.
 *
 * Essa divisão vale para os outros controllers que criarmos. Cada um implementa
 * seu próprio `main()` e os demais métodos de que precisar. A classe base chama
 * o método escolhido para a rota e, se houver outro associado ao tipo de
 * requisição HTTP, chama esse também. Depois, reúne os retornos para que a
 * classe de saída possa consultar a resposta.
 *
 * `Controller` é uma classe abstrata. O objeto que o framework cria é sempre de
 * uma classe da aplicação que herda essa classe aqui como base, conforme ocorre
 * em `Index`.
 */

abstract class Controller
{
    
    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                              CONSTANTES                               ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     * 
     * Esta classe não possui constantes.
     */
    
    
    
    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                             PROPRIEDADES                              ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     */

    /**
     * 🟢 O CONSTRUTOR JÁ RODOU?
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Começa com `false` e passa para `true` depois que o construtor chama os
     * métodos da aplicação e reúne seus retornos.
     *
     * Se o construtor for chamado de novo para esse mesmo objeto, usamos esse
     * valor para evitar que os métodos da aplicação rodem outra vez.
     *
     * _________________________________
     * @var bool
     */

    private bool $isConstructed = false;



    /**
     * 🟢 RETORNO DO MÉTODO DA ROTA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Guarda o array retornado pelo método escolhido para a rota. Pode ser o
     * retorno de `main()` ou de outro método, como `listar()`.
     *
     * _________________________________
     * @var array<int|string,mixed>
     */

    private array $controllerMethodData = [];



    /**
     * 🟢 RETORNO DO MÉTODO ASSOCIADO AO HTTP
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Guarda o array retornado pelo método associado ao tipo de requisição
     * HTTP. Se a configuração associar POST a `salvar()`, por exemplo, é aqui
     * que guardamos o retorno de `salvar()` quando ele for chamado.
     *
     * Na ausência de um método HTTP a executar, o array fica vazio.
     *
     * _________________________________
     * @var array
     */

    private array $controllerHttpMethodData = [];



    /**
     * 🟢 DADOS DA RESPOSTA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Guarda o resultado de juntar os dois retornos acima. Se a opção
     * `includeProjectMetadata` estiver ativa, esse array também recebe a chave
     * `meta` com os dados do projeto.
     *
     * É esse array que `getResponse()` entrega quando a saída consulta o
     * resultado do controller.
     *
     * _________________________________
     * @var array
     */

    private array $response = [];



    /**
     * 🟢 ARQUIVO PREPARADO PELO CONTROLLER
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Guarda o conteúdo, o nome e o tipo MIME do arquivo, nessa ordem, quando a
     * aplicação chama `setFileContents()`.
     *
     * Até essa chamada, o array fica vazio. A saída `File` pode consultar esses
     * dados ao preparar o arquivo que será entregue.
     *
     * _________________________________
     * @var array{}|array{0:string,1:string,2:string}
     */

    private array $fileContents = [];

    
    
    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                                MÉTODOS                                ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     */
    
    /**
     * 🟢 EXECUTANDO OS MÉTODOS DO CONTROLLER
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Quando `Galastri` cria o controller da aplicação, o PHP chama este
     * construtor. A rota e os métodos já foram identificados. Agora podemos
     * executar o que foi escolhido e guardar o resultado.
     *
     * Primeiro, `callControllerMethod()` chama o método da rota. Depois,
     * `callHttpMethod()` chama o método associado ao tipo de requisição HTTP,
     * se houver um para executar. Cada chamada guarda seu retorno separado.
     *
     * Com os retornos em mãos, `mergeReturns()` monta a resposta do controller.
     *
     * O `final` faz com que os controllers da aplicação usem este construtor.
     * Cada controller define o que acontece nos seus próprios métodos, e o
     * construtor da classe base cuida da ordem em que eles são chamados.
     *
     * _________________________________
     * @return void
     */
    
    final public function __construct()
    {
        /**
         * O PHP chama o construtor quando cria o objeto. Como o método é
         * público, também dá pra chamá-lo de novo no mesmo objeto.
         *
         * Usamos `$isConstructed` pra saber se esse controller já passou por
         * toda a sequência abaixo. Na primeira vez, o valor é `false`. Só
         * mudamos pra `true` depois de montar a resposta. Se o construtor for
         * chamado outra vez nesse objeto, o `if` pula a sequência e os métodos
         * da aplicação não rodam de novo.
         */
        
        if (!$this->isConstructed) {
            
            $this->callControllerMethod();
            $this->callHttpMethod();
            $this->mergeReturns();

            $this->isConstructed = true;
        }
    }

    
    
    /**
     * 🟢 MÉTODO PRINCIPAL DO CONTROLLER
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * `main()` é o método que o framework chama por padrão para atender ao
     * endpoint `@main`. Se a configuração `method` indicar outro método para
     * esse endpoint, o framework usa o nome configurado.
     *
     * A declaração `abstract` deixa a implementação por conta da aplicação.
     * Cada controller concreto precisa ter um `main()` que retorne um array.
     * Pode ser um array vazio, como o `[]` que vimos no `Index`.
     *
     * _________________________________
     * @return array  
     *    Dados que serão reunidos na resposta do controller.
     */
    
    abstract protected function main(): array;

    
    
    /**
     * 🟢 CHAMANDO O MÉTODO ESCOLHIDO PARA A ROTA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Recuperamos de `Galastri` o nome do método que deve rodar e usamos esse
     * nome pra fazer a chamada no controller que acabou de ser criado.
     *
     *
     * _________________________________
     * @return void
     */
    
    private function callControllerMethod(): void
    {
        /**
         * Armazenamos o nome do método para repassarmos ele como uma chamada.
         * 
         * ✔️ EXEMPLO
         * ┌───────────────────────────────────────────────────────────────────┐
         * │ Se `$controllerMethodName` contiver `'listar'`, por exemplo, a    │
         * │ chamada `$this->$controllerMethodName()` se transforma em         │
         * │ `$this->listar()`.                                                │
         * └───────────────────────────────────────────────────────────────────┘
         */
        
        $controllerMethodName = Galastri::getControllerMethodName();

        
        
        /**
         * Guardamos o array retornado em `$controllerMethodData` para usar
         * depois, quando `mergeReturns()` montar a resposta do controller.
         */
        
        $this->controllerMethodData = $this->$controllerMethodName();
    }

    
    
    /**
     * 🟢 CHAMANDO O MÉTODO ASSOCIADO AO HTTP
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Executa o método associado ao tipo da requisição HTTP recebida, quando
     * houver um. É assim que uma rota pode chamar `salvar()` ao receber um
     * POST, por exemplo.
     *
     *
     * _________________________________
     * @return void
     */

    private function callHttpMethod(): void
    {
        /**
         * Recuperamos de `Galastri` o nome escolhido para o tipo de
         * requisição atual.
         */
        
        $controllerHttpMethodName = Galastri::getControllerHttpMethodName();

        
        
        /**
         * `Galastri` já conferiu se há um método pra chamar no controller.
         * Se houver, usamos o nome que acabamos de recuperar e guardamos
         * o array retornado em `$controllerHttpMethodData`.
         */
        
        if (Galastri::httpMethodExists()) {
            $this->controllerHttpMethodData = $this->$controllerHttpMethodName();
        }
    }

    
    
    /**
     * 🟢 MONTANDO A RESPOSTA DO CONTROLLER
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Reúne os dados retornados pelos métodos do controller em um único
     * array, que a classe de saída pode consultar quando for preparar a
     * resposta da requisição.
     *
     *
     * _________________________________
     * @return void
     */
    
    private function mergeReturns(): void
    {
        /**
         * Juntamos primeiro o retorno do método da rota e depois o retorno do
         * método associado ao HTTP.
         *
         * Se os dois retornarem uma chave como `mensagem`, por exemplo, fica o
         * valor do método associado ao HTTP, pois seu array vem por último na
         * chamada a `array_merge()`.
         *
         * Os valores com chaves numéricas são colocados em sequência, com novos
         * índices a partir de zero.
         */
        
        $this->response = array_merge(
            $this->controllerMethodData,
            $this->controllerHttpMethodData,
        );
        
        
        
        /**
         * Se a configuração de rota `includeProjectMetadata` estiver ativa,
         * acrescentamos a chave `meta` com o nome e o título do projeto, o
         * caminho raiz, a base dos links e as versões do framework e da
         * aplicação.
         *
         * Se algum método já tiver retornado uma chave `meta`, ela será
         * substituída pela que montamos aqui.
         */
        
        if (Config::get('includeProjectMetadata')) {
            $this->response = array_merge(
                $this->response,
                [
                    'meta' => [
                        'projectName' => Config::get('projectName'),
                        'title' => Config::get('title'),
                        'rootDir' => Router::getRootDir(),
                        'baseHref' => Router::getBaseHref(),
                        'galastriVersion' => GALASTRI_VERSION,
                        'appVersion' => APP_VERSION,
                    ],
                ]
            );
        }
    }

    
    
    /**
     * 🟢 DEFININDO O NOME DO PROJETO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Permite alterar o nome do projeto enquanto o controller atende à
     * requisição. Se `includeProjectMetadata` estiver ativa, o nome definido
     * aqui aparece em `meta.projectName` quando a resposta for montada.
     *
     *
     * _________________________________
     * @param string $value  
     *    Nome a ser usado para o projeto.
     * _________________________________
     * @return void
     */
    
    final protected function setProjectName(string $value): void
    {
        Config::set('projectName', $value);
    }

    
    
    /**
     * 🟢 CONSULTANDO O NOME DO PROJETO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * @return string  
     *    Retorna uma string com o nome que está armazenado em `projectName`
     *    neste momento da requisição. 
     */
    
    final protected function getProjectName(): string
    {
        return Config::get('projectName');
    }

    
    
    /**
     * 🟢 DEFININDO O TÍTULO DA ROTA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Altera o título da rota durante a requisição. Se `includeProjectMetadata`
     * estiver ativa, esse título aparece em `meta.title` quando a resposta do
     * controller for montada.
     *
     *
     * _________________________________
     * @param string $value  
     *    Título a ser usado na rota.
     * _________________________________
     * @return void
     */

    final protected function setTitle(string $value): void
    {
        Config::set('title', $value);
    }
    
    

    /**
     * 🟢 CONSULTANDO O TÍTULO DA ROTA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * @return string  
     *    Retorna uma string com o título armazenado em `title` neste momento da
     *    requisição.
     */
    
    final protected function getTitle(): string
    {
        return Config::get('title');
    }


    
    /**
     * 🟢 CONSULTANDO O FORMATO DE SAÍDA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * @return string  
     *    Retorna uma string com o formato de saída armazenado em `output` para
     *    a requisição atual. Pode ser `view`, `json` ou `file`.
     */
    
    final protected function getOutput(): string
    {
        return Config::get('output');
    }

    
    
    /**
     * 🟢 CONSULTANDO O FUSO HORÁRIO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * @return string  
     *    Retorna uma string com o fuso horário armazenado em `timezone` para a
     *    requisição atual.
     */
    final protected function getTimezone(): string
    {
        return Config::get('timezone');
    }


    
    /**
     * 🟢 DEFININDO O ARQUIVO DE TEMPLATE
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Define o arquivo de template que a saída `View` vai usar para montar a
     * página desta requisição.
     *
     *
     * _________________________________
     * @param string $value  
     *    Caminho do arquivo de template que queremos usar.
     * _________________________________
     * @return void
     */
    
    final protected function setTemplateFile(string $value): void
    {
        Config::set('templateFile', $value);
    }

    
    
    /**
     * 🟢 CONSULTANDO O ARQUIVO DE TEMPLATE
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * @return string  
     *    Retorna uma string com o caminho do arquivo de template armazenado em
     *    `templateFile` para a requisição atual.
     */
    
    final protected function getTemplateFile(): string
    {
        return Config::get('templateFile');
    }



    /**
     * 🟢 DEFININDO A PASTA DE VIEWS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Define em qual pasta a saída `View` vai procurar a view que será usada
     * para montar a página desta requisição.
     *
     *
     * _________________________________
     * @param string $value  
     *    Caminho da pasta de views.
     * _________________________________
     * @return void
     */
    
    final protected function setViewFolder(string $value): void
    {
        Config::set('viewFolder', $value);
    }

    
    
    /**
     * 🟢 CONSULTANDO A PASTA DE VIEWS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * @return string  
     *    Retorna uma string com o caminho da pasta de views armazenado em
     *    `viewFolder` para a requisição atual.
     */
    
    final protected function getViewFolder(): string
    {
        return Config::get('viewFolder');
    }


    
    /**
     * 🟢 DEFININDO A PASTA DE ARQUIVOS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Define a pasta onde a saída `File` vai procurar o arquivo quando precisar
     * buscá-lo no disco.
     *
     *
     * _________________________________
     * @param string $value  
     *    Caminho da pasta onde estão os arquivos.
     * _________________________________
     * @return void
     */
    
    final protected function setFileFolder(string $value): void
    {
        Config::set('fileFolder', $value);
    }
    
    
    
    /**
     * 🟢 CONSULTANDO A PASTA DE ARQUIVOS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * @return string  
     *    Retorna uma string com o caminho da pasta armazenado em `fileFolder`
     *    para a requisição atual.
     */
    
    final protected function getFileFolder(): string
    {
        return Config::get('fileFolder');
    }


    
    /**
     * 🟢 DEFININDO O ARQUIVO A SER ENTREGUE
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Guarda o arquivo preparado pelo controller para a saída `File` usar na
     * resposta. Dá pra montar um CSV durante a requisição, por exemplo, e
     * passar o conteúdo pronto pra saída cuidar da entrega.
     *
     *
     * _________________________________
     * @param string $contents  
     *    Conteúdo do arquivo a ser entregue.
     *
     * @param string $fileName  
     *    Nome do arquivo.
     *
     * @param string $mimeType  
     *    Tipo MIME do conteúdo, como `text/csv`.
     * _________________________________
     * @return void
     */
    
    final protected function
        setFileContents(string $contents, string $fileName, string $mimeType): void
    {
        $this->fileContents = [$contents, $fileName, $mimeType];
    }

    
    
    /**
     * 🟢 CONSULTANDO O ARQUIVO PREPARADO PELO CONTROLLER
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * @return array{}|array{0:string,1:string,2:string}  
     *    Retorna um array com três strings, na ordem em que foram informadas em
     *    `setFileContents()`.
     *    
     *    [0] é o conteúdo do arquivo.  
     *    [1] é o nome do arquivo.  
     *    [2] é o tipo MIME do arquivo.  
     *
     *    Antes da primeira chamada a `setFileContents()` neste controller,
     *    retorna um array vazio.
     */
    
    final public function getFileContents(): array
    {
        return $this->fileContents;
    }


    
    /**
     * 🟢 DEFININDO SE O ARQUIVO SERÁ BAIXADO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Define se a saída `File` deve enviar o arquivo para download ou permitir
     * que o navegador exiba seu conteúdo.
     *
     *
     * _________________________________
     * @param bool $value  
     *    Use `true` para enviar o arquivo para download ou `false` para
     *    permitir sua exibição no navegador.
     * _________________________________
     * @return void
     */
    
    final protected function setDownloadableFile(bool $value): void
    {
        Config::set('isDownloadableFile', $value);
    }

    
    
    /**
     * 🟢 CONSULTANDO SE O ARQUIVO SERÁ BAIXADO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * @return bool  
     *    Retorna `true` quando `isDownloadableFile` está configurado para fazer
     *    download do arquivo e `false` quando está configurado para exibição no
     *    navegador.
     */
    
    final protected function isDownloadableFile(): bool
    {
        return Config::get('isDownloadableFile');
    }
    
    
    
    /**
     * 🟢 CONSULTANDO A RESPOSTA DO CONTROLLER
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * @return array  
     *    Retorna um array com os dados reunidos por `mergeReturns()`. Se
     *    `includeProjectMetadata` estiver ativa, o array também inclui os dados
     *    do projeto na chave `meta`.
     */
    
    final public function getResponse(): array
    {
        return $this->response;
    }


    
    /**
     * 🟢 CONSULTANDO OS PARÂMETROS DA ROTA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * @return array  
     *    Retorna um array com os valores dos parâmetros da rota, usando o nome
     *    de cada parâmetro como chave.
     */
    
    final protected function getParameterValues(): array
    {
        return Router::getParameterValues();
    }
    
    
    /**
     * 🟢 CONSULTANDO UM PARÂMETRO DA ROTA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * @param string $key  
     *    Nome do parâmetro que queremos consultar.
     * _________________________________
     * @return null|string  
     *    Retorna uma string com o valor do parâmetro. Se o parâmetro estiver
     *    sem valor ou se o nome informado não existir, retorna `null`.
     */
    
    final protected function getParameter(string $key): null|string
    {
        return Router::getParameter($key) ?? null;
    }
    
    
    
    /**
     * 🟢 CONSULTANDO OS NÓS DINÂMICOS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * @return array  
     *    Retorna um array com os valores dos nós dinâmicos encontrados na URL,
     *    usando o nome de cada nó como chave. Se não houver nós dinâmicos,
     *    retorna um array vazio.
     */
    
    final protected function getDynamicNodeValues(): array
    {
        return Router::getDynamicNodeValues();
    }
    
    
    /**
     * 🟢 CONSULTANDO UM NÓ DINÂMICO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * @param string $key  
     *    Nome do nó dinâmico, sem o prefixo `/?` da configuração da rota.
     * _________________________________
     * @return null|string  
     *    Retorna uma string com o valor do nó dinâmico. Se o nome informado não
     *    estiver entre os nós encontrados na URL, retorna `null`.
     */
    
    final protected function getDynamicNode(string $key): null|string
    {
        return Router::getDynamicNode($key) ?? null;
    }
}



/**
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * ◀️ VOLTAR AO TÓPICO ANTERIOR:
 *
 * Volte ao método `executeController()` de `Galastri` e retome a leitura logo
 * depois do link que trouxe você até aqui.
 *
 * 🔗 LINK: /galastri/core/Galastri.php
 */
