<?php
/**
 * -----------------------------------------------------------------------------
 * 📂
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║            ARQUIVO /galastri/core/output/engines/PhpEngine.php            ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 * Lá em `View::render()`, criamos um objeto desta classe e guardamos ele em
 * `$g` antes de executar o template ou a view.
 *
 * É por esse objeto que os arquivos da página podem consultar os dados
 * preparados pelo controller, mostrar esses dados no HTML e incluir outros
 * arquivos. Vamos conhecer as ferramentas que ficam disponíveis em `$g`.
 * ─────────────────────────────────────────────────────────────────────────────
 */



/**
 * 🟢 NAMESPACE DA CLASSE
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * A classe `PhpEngine` faz parte de um motor de template, que é parte do núcleo
 * do framework.
 */

namespace galastri\core\output\engines;



/**
 * 🟢 CLASSES UTILIZADAS
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * ┌─────────────┬─────────────────────────────────────────────────────────────┐
 * │ Classe      │ Uso                                                         │
 * ├─────────────┼─────────────────────────────────────────────────────────────┤
 * │ `Galastri`  │ Entrega o array com os dados reunidos pelo controller.      │
 * ├─────────────┼─────────────────────────────────────────────────────────────┤
 * │ `View`      │ Fornece o caminho da view quando usamos                     │
 * │             │ `$g->include('view')`.                                      │
 * ├─────────────┼─────────────────────────────────────────────────────────────┤
 * │ `Exception` │ Permite lançar uma exceção quando `print()` recebe um valor │
 * │             │ de um tipo que não aceita.                                  │
 * ├─────────────┼─────────────────────────────────────────────────────────────┤
 * │ `Message`   │ Fornece a mensagem usada nessa exceção.                     │
 * └─────────────┴─────────────────────────────────────────────────────────────┘
 */

use galastri\core\Galastri;
use galastri\core\output\View;
use galastri\extensions\Exception;
use galastri\language\Message;



/**
 * 🟢 CLASSE PhpEngine
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * O template e a view são arquivos PHP que podem misturar HTML com consultas
 * aos dados preparados pelo controller. `PhpEngine` reúne os métodos que esses
 * arquivos usam pra trabalhar com os dados e incluir outros arquivos na página.
 *
 * A classe `View` cria uma instância de `PhpEngine` e a guarda na variável `$g`
 * antes de executar o template ou a view. Dentro desses arquivos, podemos usar:
 *
 * - `$g->get()` pra consultar um valor dos dados do controller;
 * 
 * - `$g->print()` pra escrever esse valor diretamente no HTML;
 * 
 * - `$g->include()` pra incluir a view ou outro arquivo PHP na página.
 */

final class PhpEngine
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
     * 🟢 DADOS DO CONTROLLER
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Guarda o array com os dados que o controller preparou para esta
     * requisição. O construtor recebe esse array de
     * `Galastri::getControllerResponse()` e o armazena aqui.
     *
     * Quando o template ou a view chama `$g->get()` ou `$g->print()`, os
     * valores consultados vêm deste array.
     *
     *
     * _________________________________
     * @var array
     */
    
    private array $controllerData;

    
    
    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                                MÉTODOS                                ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     */
    
    /**
     * 🟢 RECEBENDO OS DADOS DO CONTROLLER
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Quando `View` cria o objeto `$g`, o controller já executou os métodos da
     * aplicação e reuniu os dados que serão usados na página.
     *
     * Aqui buscamos esse array com `Galastri::getControllerResponse()` e o
     * guardamos em `$controllerData`. A partir daí, o template e a view podem
     * consultar esses dados pelos métodos do objeto `$g`.
     *
     *
     * _________________________________
     * @return void
     */
    
    public function __construct() {
        $this->controllerData = Galastri::getControllerResponse();
    }

    
    
    /**
     * 🟢 CONSULTANDO OS DADOS DO CONTROLLER
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Permite que o template e a view consultem os dados do controller que
     * guardamos em `$controllerData`.
     *
     * Cada argumento informa uma chave do array que queremos acessar. A
     * primeira chave é procurada em `$controllerData`. Se informarmos outra,
     * ela será procurada dentro do array armazenado pela chave anterior.
     *
     * ✔️ EXEMPLO
     * ┌───────────────────────────────────────────────────────────────────────┐
     * │ Imagine que o controller tenha preparado estes dados:                 │
     * │                                                                       │
     * │ ['produto' => ['nome' => 'Teclado']]                                  │
     * │                                                                       │
     * │ Na view, podemos consultar:                                           │
     * │                                                                       │
     * │ $g->get('produto')         // Retorna ['nome' => 'Teclado'].          │
     * │ $g->get('produto', 'nome') // Retorna 'Teclado'.                      │
     * │ $g->get('produto', 'preco') // Retorna null: a chave não existe.      │
     * └───────────────────────────────────────────────────────────────────────┘
     *
     * Se uma chave não existir, retorna `null`. Isso também acontece se
     * tentarmos procurar uma chave dentro de um valor que não seja um array,
     * como em `$g->get('produto', 'nome', 'sobrenome')` no exemplo acima:
     * `nome` armazena a string 'Teclado', onde não podemos consultar uma chave.
     *
     * Sem argumentos, retorna o array completo com os dados do controller.
     *
     *
     * _________________________________
     * @param int|string ...$keyList  
     *    Chaves que serão consultadas, na ordem em que foram informadas.
     * _________________________________
     * @return mixed  
     *    Valor armazenado pela última chave consultada ou o array completo dos
     *    dados do controller, se nenhuma chave for informada. Retorna `null`
     *    quando não consegue acessar uma das chaves ou quando o próprio valor
     *    consultado é `null`.
     */
    
    public function get(int|string ...$keyList): mixed
    {
        
        /**
         * Começamos pelo array com todos os dados do controller. Se nenhuma
         * chave for informada, é esse array que retornaremos no final.
         */

        $currentValue = $this->controllerData;

        
        
        /**
         * Iniciamos a busca pelas chaves informadas.
         */
            
        foreach ($keyList as $key) {
            
        
        
            /**
             * Antes de procurar a chave dentro do valor, conferimos se o valor
             * em si é um array (só se pode buscar um valor dentro de um array.
             * Se o valor for uma string, não há o que fazer, o usuário esté
             * pedindo por um valor impossível) e se essa chave existe nele. Se
             * uma dessas condições não for atendida, encerramos a consulta
             * retornando `null`.
             */
            
            if (!is_array($currentValue) || !array_key_exists($key, $currentValue)) {
                return null;
            }

            $currentValue = $currentValue[$key];
        }

        return $currentValue;
    }
    
    

    /**
     * 🟢 ESCREVENDO UM VALOR NO HTML
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Consulta os dados do controller por meio de `get()` e escreve o valor
     * encontrado no HTML. As chaves são informadas da mesma forma que vimos no
     * método anterior.
     *
     * Antes de escrever, usamos `htmlspecialchars()` pra transformar caracteres
     * como `<`, `>`, `&` e aspas em entidades HTML. Assim, esses caracteres são
     * exibidos como texto pelo navegador.
     *
     * ✔️ EXEMPLO
     * ┌───────────────────────────────────────────────────────────────────────┐
     * │ Se o nome do produto for '<Teclado>', esta chamada:                   │
     * │                                                                       │
     * │ $g->print('produto', 'nome');                                         │
     * │                                                                       │
     * │ Escreve `&lt;Teclado&gt;` no HTML. O navegador exibe <Teclado> como   │
     * │ texto, preservando os sinais de menor e maior.                        │
     * └───────────────────────────────────────────────────────────────────────┘
     *
     * Aceita strings e números. Se a consulta retornar `null` ou um array
     * vazio, não escreve nenhum caractere. Para booleanos, arrays com
     * elementos, objetos e recursos, lança uma exceção.
     *
     *
     * _________________________________
     * @param int|string ...$keyList  
     *    Chaves usadas por `get()` para localizar o valor que será escrito.
     * _________________________________
     * @return void
     */

    public function print(int|string ...$keyList) : void
    {
        /**
         * Deixamos a busca por conta de `get()`, passando todas as chaves que
         * recebemos.
         */
        
        $data = $this->get(...$keyList);
        
        
        
        /**
         * Tratamos o array vazio como `null` pra que ambos resultem em uma
         * string vazia, sem escrever caracteres no HTML.
         */
        
        if ($data === []) {
            $data = null;
        }
        
        
        
        /**
         * Rejeitamos os tipos que não aceitamos escrever. Strings e números
         * seguem para a escrita; `null` é substituído por uma string vazia. O
         * restante lança uma exceçõa com erro.
         */

        switch (gettype($data)) {
            case 'boolean':
            case 'array':
            case 'object':
            case 'resource':
            case 'unknown type':
                throw new Exception(Message::get('VIEW_INVALID_PRINT_DATA'));
            case 'NULL':
                $data = '';
        }

        
        
        /**
         * Escapamos os caracteres especiais do HTML usando UTF-8. A opção
         * `ENT_QUOTES` inclui tanto as aspas simples quanto as duplas nesse
         * tratamento. Por fim, `echo` escreve o resultado na página.
         */
        
        echo htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
    
    
    
    /**
     * 🟢 INCLUINDO ARQUIVOS NA PÁGINA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Executa um arquivo PHP e acrescenta o conteúdo que ele produzir à página
     * que está sendo montada.
     *
     * O valor 'view' tem um significado próprio para incluir a view escolhida
     * para a rota através do caminho que a classe `View` guardou em
     * `$viewPath`. Isso permite inserir a view em qualquer lugar do template
     * quando ele estiver definido.
     *
     * Para inserir outros arquivos, informamos um caminho a partir da raiz do
     * projeto.
     *
     * ✔️ EXEMPLO
     * ┌───────────────────────────────────────────────────────────────────────┐
     * │ Dentro do template, podemos incluir a view da rota:                   │
     * │                                                                       │
     * │ $g->include('view');                                                  │
     * │                                                                       │
     * │ Ou incluir outro arquivo, como um menu compartilhado:                 │
     * │                                                                       │
     * │ $g->include('/app/views/partes/menu.php');                            │
     * └───────────────────────────────────────────────────────────────────────┘
     *
     * O arquivo incluído também recebe a variável `$g`, apontando para este
     * mesmo objeto. Assim, ele também pode consultar os dados do controller,
     * escrever valores no HTML e incluir outros arquivos pelos métodos que já
     * vimos.
     *
     * Se o arquivo procurado não existir, retornamos `null`.
     *
     *
     * _________________________________
     * @param string $path  
     *    É o caminho do arquivo que se quer incluir, a partir da raiz do
     *    projeto. Para incluir a view da rota, basta informar 'view'.
     * _________________________________
     * @return bool|null  
     *    Retorna `true` quando o arquivo é executado sem um `return` explícito.
     *    Se ele retornar um valor escalar, esse valor é convertido para bool.
     *    Retorna `null` se o arquivo não existir ou se ele retornar `null`.
     */
    
    public function include(string $path): ?bool
    {
        $viewPath = View::$viewPath;
        
        
        
        /**
         * O arquivo será executado no escopo deste método. Por isso, criamos a
         * variável `$g` aqui, apontando para o próprio objeto, pra que ela
         * esteja disponível dentro do arquivo incluído.
         */
        
        $g = $this;

        /**
         * Se `$path` for informada usando a string 'view', usamos o caminho
         * preparado pela classe `View`. Conferimos se o arquivo ainda existe
         * antes de executá-lo.
         */

        if ($path === 'view' and $viewPath != '') {
            if (file_exists($viewPath)) {
                return require($viewPath);
            }
        } else {
            
            /**
             * Para os demais arquivos, retiramos as barras do começo do
             * caminho informado e acrescentamos a raiz do projeto, seguida
             * de uma única barra.
             */
            
            $path = PROJECT_DIR.'/'.ltrim($path, '/');

            if (file_exists($path)) {
                return include($path);
            }
        }

        /**
         * Se não encontramos o arquivo, encerramos sem incluir conteúdo.
         */
        
        return null;
    }
}



/**
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * ▶️ PRÓXIMO TÓPICO:
 * 🔗 LINK: /app/templates/template.php
 */
