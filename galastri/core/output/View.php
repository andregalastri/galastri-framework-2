<?php
/**
 * -----------------------------------------------------------------------------
 * 📂
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                  ARQUIVO /galastri/core/output/View.php                   ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 * Lá em `Galastri`, vimos o framework chamar a classe de formato de saída.
 * Quando a configuração `output` for definida como `view`, é aqui que a página
 * começa a ser montada.
 *
 * A classe localiza o arquivo da view e confere se a rota usa um template.
 * Durante a montagem da página, esses arquivos podem consultar os dados que o
 * controller preparou.
 * ─────────────────────────────────────────────────────────────────────────────
 */



/**
 * 🟢 NAMESPACE DA CLASSE
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * A classe `View` é um dos formatos de saída do núcleo do framework.
 */

namespace galastri\core\output;



/**
 * 🟢 CLASSES UTILIZADAS
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * ┌─────────────┬─────────────────────────────────────────────────────────────┐
 * │ Classe      │ Uso                                                         │
 * ├─────────────┼─────────────────────────────────────────────────────────────┤
 * │ `Router`    │ Fornece as partes do namespace do controller.               │
 * ├─────────────┼─────────────────────────────────────────────────────────────┤
 * │ `Galastri`  │ Informa qual método do controller foi chamado para a rota.  │
 * ├─────────────┼─────────────────────────────────────────────────────────────┤
 * │ `Config`    │ Fornece os valores de `viewFile`, `viewFolder` e            │
 * │             │ `templateFile`.                                             │
 * ├─────────────┼─────────────────────────────────────────────────────────────┤
 * │ `Exception` │ Sinaliza quando a view ou o template esperado não existe.   │
 * ├─────────────┼─────────────────────────────────────────────────────────────┤
 * │ `Message`   │ Fornece as mensagens usadas nessas exceções.                │
 * ├─────────────┼─────────────────────────────────────────────────────────────┤
 * │ `Tools`     │ Ajuda a separar a parte do namespace do controller que      │
 * │             │ entra no caminho padrão da view.                            │
 * └─────────────┴─────────────────────────────────────────────────────────────┘
 */

use galastri\core\Router;
use galastri\core\Galastri;
use galastri\core\config\Config;
use galastri\extensions\Exception;
use galastri\language\Message;
use galastri\modules\Tools;



/**
 * 🟢 CLASSE View
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * A classe `View` monta a resposta HTML da rota.
 * 
 * Pense numa página de produtos. O controller prepara a lista, e a view usa
 * esses dados pra montar o trecho da página que mostra os produtos. Se a rota
 * usa um template, ele dá a estrutura da página e pode incluir a view no ponto
 * em que a lista deve aparecer.
 *
 * A classe `View` localiza esses arquivos e disponibiliza o objeto `$g`, pelo
 * qual eles podem consultar os dados do controller. Quando a rota usa um
 * template, a execução começa por ele. Sem template, a classe executa a view
 * diretamente.
 *
 * Com o HTML pronto, `View` verifica se o navegador pode reaproveitar uma cópia
 * em cache antes de enviar a página.
 */

final class View
{
    
    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                                TRAITS                                 ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     */
    
    /**
     * 🟢 CACHE DO NAVEGADOR
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * O trait `BrowserCache` fornece o método `checkBrowserCache()`. Em
     * `render()`, passamos o HTML pronto a esse método pra saber se o navegador
     * pode reaproveitar a cópia da página que já tem em cache. Se puder,
     * `render()` retorna antes de enviar o HTML de novo.
     */
    
    use traits\BrowserCache;
    


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
     * 🟢 CAMINHO DO ARQUIVO DA VIEW
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Guarda o caminho absoluto da view depois que `checkViewFile()` confirma
     * que o arquivo existe.
     *
     * Quando o template chama `$g->include('view')`, `PhpEngine` consulta esta
     * propriedade pra saber qual arquivo incluir.
     *
     * 
     * _________________________________
     * @var string
     */

    public static string $viewPath;
    
    
    
    
    /**
     * 🟢 CAMINHO DO ARQUIVO DE TEMPLATE
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Guarda o caminho absoluto do template depois que `checkTemplateFile()`
     * confirma que o arquivo existe.
     *
     * A string vazia indica que `render()` deve executar a view diretamente.
     *
     * 
     * _________________________________
     * @var string
     */
    
    private static string $templatePath = '';

    
    
    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                                MÉTODOS                                ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     */
    
    /**
     * 🟢 CLASSE ESTÁTICA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Os recursos desta classe são acessados diretamente por chamadas como
     * `View::run()`, sem precisar criar um objeto.
     *
     * Por isso, deixamos o construtor privado para impedir que a classe seja
     * instanciada com `new View()`.
     */
    
    private function __construct() {}
    
    
    
    /**
     * 🟢 PREPARANDO A PÁGINA HTML
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este é o método que `Galastri` chama quando a saída da rota é `view`. Ele
     * organiza a busca pelos arquivos e a montagem da página.
     *
     * 
     * _________________________________
     * @return void
     */

    public static function run(): void
    {
        /**
         * Primeiro localizamos e conferimos o arquivo da view. Se a rota
         * indicar um template, também conferimos se esse arquivo existe.
         */
        
        self::checkViewFile();
        self::checkTemplateFile();

        
        
        /**
         * Com os caminhos resolvidos, `render()` começa a montar a página pelo
         * template, se houver, ou executa a view diretamente.
         */
        
        self::render();
    }

    
    
    /**
     * 🟢 LOCALIZANDO O ARQUIVO DA VIEW
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Localiza a view que será usada nesta requisição e guarda seu caminho
     * absoluto em `$viewPath`.
     *
     * _________________________________
     * @return void
     */
    
    private static function checkViewFile(): void
    {
        /**
         * Se o endpoint indicar um arquivo em `viewFile`, usamos esse valor.
         */
        
        $viewFile = Config::get('viewFile');
        
        
        
        /**
         * Se a configuração estiver vazia, montamos o caminho a partir do
         * namespace do controller e do método escolhido para a rota.
         */

        if ($viewFile == '') {
        
            /**
             * Retiramos as duas primeiras partes do namespace e juntamos as
             * demais com barras. Depois, acrescentamos o nome do método e a
             * extensão `.php`.
             *
             * Se o método for `listar()` em `app\controllers\Produtos`, por
             * exemplo, o resultado aqui é `Produtos/listar.php`.
             */
            
            $namespace = Router::getControllerNamespace();
            $controllerParts = Tools::deleteFirstKey($namespace, 2);
            $controllerName = implode('/', $controllerParts);

            $methodName = Galastri::getControllerMethodName();
            $viewFile = $controllerName.'/'.$methodName.'.php';
        }
        
        

        /**
         * Deixamos uma única barra no começo de `$viewFile` e juntamos esse
         * trecho à pasta de views, a partir da raiz do projeto.
         */
        
        $viewFile = '/'.ltrim($viewFile, '/');
        $viewPath = PROJECT_DIR.Config::get('viewFolder').$viewFile;
        
        

        /**
         * Se o caminho não existir, a exceção informa onde procuramos. Com o
         * caminho conferido, podemos guardá-lo pra usar na montagem da página.
         */
        
        if (!file_exists($viewPath)) {
            throw new Exception(
                Message::get("OUTPUT_VIEW_FILE_NOT_FOUND"),
                [
                    $viewPath,
                ]
            );
        }

        
        
        /**
         * Estando tudo certo, a propriedade `$viewPath` é preenchida com o
         * caminho.
         */
        
        self::$viewPath = $viewPath;
    }
    
    
    
    /**
     * 🟢 LOCALIZANDO O ARQUIVO DE TEMPLATE
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Confere se o template escolhido para a requisição existe e guarda seu
     * caminho pra usar na montagem da página.
     *
     * _________________________________
     * @return void
     */
    
    private static function checkTemplateFile(): void
    {
        /**
         * Armazenamos a configuração de `templateFile` e verificamos se ela não
         * está vazia. Se estiver, isso indica que a rota usa só a view, então
         * não atribuímos valor à propriedade `$templatePath`.
         */
        
        $templateFile = Config::get('templateFile');
        
        if ($templateFile != '') {
        
            /**
             * Se `templateFile` estiver preenchido, juntamos esse valor a
             * `PROJECT_DIR` pra obter o caminho absoluto ao arquivo de template
             * e testamos se o arquivo existe.
             * 
             * Se não existir, há algo errado, então lançamos uma exceção.
             */
            
            $templatePath = PROJECT_DIR.$templateFile;

            if (!file_exists($templatePath)) {
                throw new Exception(
                    Message::get("OUTPUT_TEMPLATE_FILE_NOT_FOUND"),
                    [
                        $templatePath,
                    ]
                );
            }

            
            
            /**
             * Estando tudo certo, a propriedade `$templatePath` é preenchida
             * com o caminho.
             * 
             * ⚠️ IMPORTANTE
             * ┌───────────────────────────────────────────────────────────────┐
             * │ `self::$templatePath` só é preenchido se a configuração       │
             * │ `templateFile` não for vazia.                                 │
             * └───────────────────────────────────────────────────────────────┘
             */
            
            self::$templatePath = $templatePath;
        }
    }

    
    
    /**
     * 🟢 MONTANDO E ENVIANDO A PÁGINA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Executa o template ou a view e guarda o HTML produzido. Com a página
     * pronta, consulta `checkBrowserCache()` pra saber se o navegador pode
     * reaproveitar sua cópia em cache. Essa verificação compara o ETag do HTML
     * gerado com os valores enviados pelo navegador no cabeçalho
     * `If-None-Match`.
     *
     * _________________________________
     * @return void
     */
    
    private static function render(): void
    {
        /**
         * Guardamos o nível atual de buffers pra saber até onde podemos voltar
         * se algo der errado. Depois, abrimos um buffer pra capturar o HTML
         * enquanto a página é montada.
         */

        $bufferLevel = ob_get_level();
        ob_start();

        
        
        try {
            
            /**
             * Criamos `$g` antes de carregar os arquivos. É por meio desse
             * objeto que o template e a view podem consultar os dados do
             * controller e incluir outros arquivos.
             *
             * ⚠️ IMPORTANTE
             * ┌───────────────────────────────────────────────────────────────┐
             * │ Hoje o framework possui apenas suporte à classe `PhpEngine`   │
             * │ para renderizar o HTML e por isso, no momento, deixamos o     │
             * │ nome da classe de renderização hardcoded mesmo.               │
             * │                                                               │
             * │ Futuramente podemos ampliar a disponibilização para outros    │
             * │ template engines, aí ajustamos.                               │
             * └───────────────────────────────────────────────────────────────┘
             *
             * - Veja como `$g` funciona em `PhpEngine.php` e depois volte aqui
             *   pra acompanhar a montagem da página.  
             * 
             *   🔗 LINK: /galastri/core/output/engines/PhpEngine.php
             */
            
            $phpEngine = 'galastri\core\output\engines\PhpEngine';
            $g = new $phpEngine();

            
            
            /**
             * Se houver um template, começamos por ele. O template pode incluir
             * a view com `$g->include('view')`. Com `$templatePath` vazia,
             * executamos a view diretamente.
             */
            
            require(
                self::$templatePath === ''
                ? self::$viewPath
                : self::$templatePath
            );

            
            
            /**
             * Recolhemos o HTML gerado e fechamos o buffer antes de verificar o
             * cache.
             */
            
            $renderedContent = ob_get_clean();
            
            
            
        } catch (\Throwable $e) {
            
            /**
             * Se algo der errado, descartamos a saída dos buffers abertos
             * durante a montagem. A limpeza vai até o nível que guardamos no
             * começo ou para ao encontrar um buffer que não pode ser removido.
             * 
             * Depois, relançamos a falha original.
             */
            
            while (ob_get_level() > $bufferLevel) {
                $buffer = ob_get_status();

                if (($buffer['flags'] & PHP_OUTPUT_HANDLER_REMOVABLE) === 0) {
                    break;
                }

                ob_end_clean();
            }

            throw $e;
        }
        
        
        
        /**
         * Se o navegador puder reaproveitar a cópia em cache, encerramos aqui.
         */

        if (self::checkBrowserCache($renderedContent)) {
            return;
        }
        
        
        
        /**
         * Se não, enviamos o HTML que acabamos de montar.
         */

        @print($renderedContent);
    }

    

    /**
     * 🟢 EXIGÊNCIA DE CONTROLLER
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Cada classe de saída informa, por esse método, se precisa de um
     * controller. `Galastri` consulta a saída escolhida antes de executá-la.
     *
     * Para `View`, esse retorno faz `Galastri` chamar `notFound()` se a
     * rota for inválida ou lançar uma exceção se a classe do controller
     * não for encontrada.
     *
     * _________________________________
     * @return bool  
     *    Retorna sempre `true`, pois a saída `View` exige um controller.
     */

    public static function requiresController(): bool
    {
        return true;
    }
}



/**
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * ▶️ PRÓXIMO TÓPICO:
 * 🔗 LINK: /galastri/core/output/Json.php
 */