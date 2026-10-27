<?php
/**
 * -----------------------------------------------------------------------------
 * 📂
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                  ARQUIVO /galastri/core/ErrorHandler.php                  ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 * Este arquivo define o gerenciador de erros do framework. Ele recebe as
 * informações de uma falha, prepara o que pode ser mostrado na resposta e,
 * quando configurado, guarda os detalhes em um arquivo de log.
 *
 * O gerenciador é ativado no bootstrap, antes do bootstrap da aplicação e do
 * processamento da requisição por Galastri::run().
 *
 * 🔗 LINK: /galastri/bootstrap/bootstrap.php
 * ─────────────────────────────────────────────────────────────────────────────
 */



/**
 * 🟢 NAMESPACE DA CLASSE
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * ErrorHandler pertence ao núcleo do framework.
 */

namespace galastri\core;



/**
 * 🟢 CLASSES UTILIZADAS
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Estas classes fornecem os recursos que usamos para preparar as respostas de
 * erro, consultar as configurações e registrar os detalhes das falhas.
 *
 * ┌─────────────┬─────────────────────────────────────────────────────────────┐
 * │ Classe      │ Uso                                                         │
 * ├─────────────┼─────────────────────────────────────────────────────────────┤
 * │ `Config`    │ Consulta as configurações que definem quais detalhes podem  │
 * │             │ aparecer na resposta, qual apresentação será usada e se     │
 * │             │ devemos gravar logs ou interromper a execução nos avisos.   │
 * ├─────────────┼─────────────────────────────────────────────────────────────┤
 * │ `Json`      │ Identifica se a resposta já está em streaming e permite     │
 * │             │ enviar o erro como uma nova linha JSON nesse mesmo fluxo.   │
 * ├─────────────┼─────────────────────────────────────────────────────────────┤
 * │ `Exception` │ Representa a exceção própria do framework. Sua propriedade  │
 * │             │ `displayValidationError` pode autorizar a exibição da       │
 * │             │ mensagem de validação, enquanto `errorSource` informa a     │
 * │             │ classificação usada para escolher o status HTTP.            │
 * ├─────────────┼─────────────────────────────────────────────────────────────┤
 * │ `Message`   │ Busca a mensagem genérica que mostramos quando os detalhes  │
 * │             │ da falha não podem ser exibidos.                            │
 * ├─────────────┼─────────────────────────────────────────────────────────────┤
 * │ `Tools`     │ Oferece funções auxiliares para identificar tipos de        │
 * │             │ valores, preparar o caminho do log, criar sua pasta e       │
 * │             │ gravar os registros no arquivo.                             │
 * └─────────────┴─────────────────────────────────────────────────────────────┘
 */

use galastri\core\config\Config;
use galastri\core\output\Json;
use galastri\extensions\Exception;
use galastri\language\Message;
use galastri\modules\Tools;



/**
 * 🟢 CLASSE ErrorHandler
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Quando acontece uma falha, queremos apresentar uma resposta que combine com a
 * saída da requisição e registrar informações que ajudem a descobrir o que
 * houve.
 *
 * Esta classe reúne esse trabalho. Erros do PHP, exceções que não foram
 * capturadas e determinados erros fatais chegam por caminhos diferentes, mas
 * são encaminhados ao mesmo método printError().
 *
 * A resposta pode ser um texto em HTML, JSON ou uma imagem PNG. Antes de
 * mostrá-la, consultamos as configurações para decidir quais detalhes podem
 * aparecer. O log, quando habilitado, recebe os dados originais da falha.
 *
 * Chamar run() prepara esses caminhos. As funções registradas por ele só entram
 * em ação quando o PHP comunica um erro, uma exceção não capturada ou o
 * encerramento da execução.
 */

final class ErrorHandler
{
    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                              CONSTANTES                               ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     *
     *
     *
     * 🟢 CAMINHO DO ARQUIVO DE LOG
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Define onde os registros de erro serão guardados. O trecho {{}} é um
     * espaço reservado que createLogFile() troca pela data atual.
     *
     * ✔️ EXEMPLO
     * ┌───────────────────────────────────────────────────────────────────────┐
     * │ Uma falha ocorrida em 26/10/2026 é registrada neste arquivo:          │
     * │                                                                       │
     * │ /logs/ErrorHandler-2026-10-26.log                                     │
     * │                                                                       │
     * │ As falhas do mesmo dia compartilham o mesmo arquivo. Os registros     │
     * │ mais recentes ficam no início.                                        │
     * └───────────────────────────────────────────────────────────────────────┘
     *
     *
     * ---
     * @var string
     */
    
    const PATH_LOG = PROJECT_DIR.'/logs/ErrorHandler-{{}}.log';



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
     * `ErrorHandler::run()`, sem precisar criar um objeto.
     *
     * Por isso, deixamos o construtor privado para impedir que a classe seja
     * instanciada com `new ErrorHandler()`.
     */
    
    private function __construct() {}



    /**
     * 🟢 ATIVANDO O GERENCIADOR DE ERROS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Executa uma série de métodos internos para preparar o PHP para encaminhar
     * as falhas aos métodos desta classe.
     *
     * Registramos uma função para os erros que o PHP permite tratar durante a
     * execução, outra para exceções não capturadas e uma terceira para conferir
     * o último erro no encerramento.
     *
     * Essas funções ficam aguardando os acontecimentos correspondentes para
     * executar o tratamento registrado.
     *
     * O bootstrap faz essa ativação antes de carregar o bootstrap da aplicação.
     * Uma falha que impeça o código de chegar até aqui ainda não terá esses
     * tratamentos disponíveis.
     *
     *
     * ---
     * @return void
     */
    
    public static function run(): void
    {
        /**
         * Começamos registrando os três caminhos de tratamento. Cada método
         * abaixo entrega uma função ao PHP, que a executará no momento
         * correspondente.
         */
        
        self::setErrorHandler();
        self::setExceptionHandler();
        self::registerShutdownFunction();
    }



    /**
     * 🟢 RECEBENDO OS ERROS DO PHP
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Registra a função que o PHP vai chamar quando ocorrer um erro abrangido
     * por `set_error_handler()`, como um aviso `E_WARNING`.
     *
     * O PHP entrega o nível, a mensagem, o arquivo e a linha do erro. Primeiro
     * conferimos se esse nível está habilitado em `error_reporting()`. Depois,
     * encaminhamos as informações para `printError()`.
     *
     *
     * ---
     * @return void
     */
    
    private static function setErrorHandler(): void
    {
        /**
         * A função sem nome passada a `set_error_handler()` será chamada pelo
         * PHP quando houver um erro que esse mecanismo possa tratar. Seus
         * parâmetros recebem as informações da falha.
         */
        
        set_error_handler(
            function (int $level, string $message, string $file, int $line): void
            {
                /**
                 * O PHP pode chamar nosso tratamento mesmo para um nível que
                 * não está habilitado em `error_reporting()`. Por isso, fazemos
                 * essa conferência antes de preparar a resposta.
                 *
                 * O operador `&` verifica se o nível recebido está entre os
                 * níveis ativos. Aqui ele não cria uma referência entre
                 * variáveis. Se o resultado for zero, não encaminhamos esse
                 * erro para `printError()`.
                 */
                
                if ((error_reporting() & $level) === 0) {
                    return;
                }



                /**
                 * Transformamos o nível em um nome. Se ele não estiver na lista
                 * de getErrorCode(), usamos E_WARNING como alternativa.
                 *
                 * Também recolhemos o histórico das chamadas neste ponto.
                 * DEBUG_BACKTRACE_PROVIDE_OBJECT permite incluir o objeto das
                 * chamadas de métodos, enquanto DEBUG_BACKTRACE_IGNORE_ARGS
                 * deixa os argumentos de fora. O operador | reúne essas duas
                 * opções.
                 *
                 * Com essas informações, printError() pode preparar a resposta
                 * e o log.
                 */
                
                $errorCode = self::getErrorCode($level, 'E_WARNING');
                
                $debugBacktrace = debug_backtrace(
                    DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS
                );
                
                self::printError($errorCode, $message, $file, $line, $debugBacktrace);
            }
        );
    }



    /**
     * 🟢 RECEBENDO EXCEÇÕES NÃO CAPTURADAS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Quando uma exceção é lançada com `throw`, o PHP procura um `catch` que
     * possa tratá-la. Se encontrar, a aplicação assume esse tratamento, mas se
     * ninguém capturar a exceção, precisamos de um lugar para receber essa
     * falha e preparar a resposta que será apresentada.
     *
     * É isso que fazemos aqui: usamos set_exception_handler() para registrar
     * uma função que o PHP chamará quando uma exceção não for capturada.
     *
     * Essa função recebe um objeto com informações sobre a falha. Podemos
     * consultar sua mensagem, seu código de erro, o arquivo e a linha de
     * origem, além do histórico de chamadas que levou até sua criação.
     *
     * ℹ️ OBSERVAÇÕES  
     * ┌───────────────────────────────────────────────────────────────────────┐
     * │ Algumas falhas são lançadas pelo próprio PHP como objetos.            │
     * │                                                                       │
     * │ EXEMPLO                                                               │
     * │ ┌───────────────────────────────────────────────────────────────────┐ │
     * │ │ function mostrarNome(string $nome) {                              │ │
     * │ │     echo $nome;                                                   │ │
     * │ │ }                                                                 │ │
     * │ │                                                                   │ │
     * │ │ mostrarNome(['teste']);                                           │ │
     * │ └───────────────────────────────────────────────────────────────────┘ │
     * │                                                                       │
     * │ Não precisamos escrever um `throw` no código para que esse tratamento │
     * │ seja acionado. Algumas falhas, como passar um array para uma função   │
     * │ que exige uma string, fazem o próprio PHP criar e lançar um erro como │
     * │ TypeError.                                                            │
     * │                                                                       │
     * │ Se nenhum `catch` capturar esse erro, o PHP chamará a função que      │
     * │ registramos com set_exception_handler().                              │
     * ├───────────────────────────────────────────────────────────────────────┤
     * │ Quando a falha chega a esse tratamento global, a execução já não pode │
     * │ ser retomada.                                                         │
     * │                                                                       │
     * │ EXEMPLO                                                               │
     * │ ┌───────────────────────────────────────────────────────────────────┐ │
     * │ │ function mostrarNome(string $nome) {                              │ │
     * │ │     echo $nome;                                                   │ │
     * │ │ }                                                                 │ │
     * │ │                                                                   │ │
     * │ │ mostrarNome(['teste']); // Gera o TypeError                       │ │
     * │ │                                                                   │ │
     * │ │ echo 'Continuando o programa...';                                 │ │
     * │ └───────────────────────────────────────────────────────────────────┘ │
     * │                                                                       │
     * │ Neste caso, o PHP chama nosso tratamento para apresentar a falha, mas │
     * │ não volta depois para executar esse `echo`; o PHP encerra a execução. │
     * │                                                                       │
     * │ Para tratar uma falha assim e continuar o programa, é necessário      │
     * │ capturá-la com um try/catch. O tratamento global que faremos aqui é o │
     * │ último destino de uma falha se ninguém a capturar.                    │
     * └───────────────────────────────────────────────────────────────────────┘
     *
     *
     * ---
     * @return void
     */

    private static function setExceptionHandler(): void
    {
        /**
         * set_exception_handler() recebe e armazena uma função como sendo o
         * tratamento global de exceções.
         *
         * O bloco `function` abaixo é armazenado e fica disponível para o PHP
         * chamar quando uma exceção não for capturada.
         *
         * Quando isso acontecer, o próprio PHP preencherá o parâmetro `$e` com
         * o objeto com os dados da falha.
         */

        set_exception_handler(
            function (object $e): void
            {
                
                /**
                 * Começamos com a preparação de uma cópia do histórico de
                 * chamadas que ocorreram até o ponto em que a exceção ocorreu.
                 * Começamos com um array vazio e acrescentaremos nele cada
                 * chamada do histórico.
                 */

                $trace = [];



                /**
                 * Aqui pegamos a lista das chamadas que estavam em andamento
                 * quando a exceção foi criada. É como um retrato do caminho que
                 * levou a execução até aquele ponto.
                 *
                 * ✔️ EXEMPLO
                 * ┌───────────────────────────────────────────────────────────┐
                 * │ Um controller chama cadastrarUsuario(), que chama         │
                 * │ salvarNoBanco() que chama conectarNoBanco().              │
                 * │                                                           │
                 * │ Se dentro de conectarNoBanco(), uma exceção for lançada,  │
                 * │ o histórico mostrará quem chamou conectarNoBanco(), quem  │
                 * │ chamou salvarNoBanco() e quem chamou cadastrarUsuario().  │
                 * │                                                           │
                 * │ Ou seja, mostra uma trilha que permite verificar cada     │
                 * │ passo de chamadas, dando mais informações para depuração  │
                 * │ do erro.                                                  │
                 * └───────────────────────────────────────────────────────────┘
                 *
                 * ℹ️ OBSERVAÇÃO
                 * ┌───────────────────────────────────────────────────────────┐
                 * │ Não se trata, porém, de uma lista completa de todas as    │
                 * │ instruções que o programa executou. São apenas as         │
                 * │ chamadas que ainda estavam em andamento naquele momento.  │
                 * └───────────────────────────────────────────────────────────┘
                 *
                 * Executamos o método `getTrace()` para recuperar essa lista em
                 * formato de array. Cada entrada informa a função, a classe, o
                 * arquivo, a linha e os argumentos recebidos, mas a
                 * disponibilidade destes dados pode variar.
                 *
                 * A cada passagem deste `foreach`, `$value` contém os dados de
                 * uma dessas chamadas.
                 */

                foreach ($e->getTrace() as $key => $value) {

                    /**
                     * Dentro dos dados do histórico, pode existir um campo
                     * chamado `args`. Ele guarda os valores que foram passados
                     * como argumentos àquela função ou método.
                     *
                     * ✔️ EXEMPLO
                     * ┌───────────────────────────────────────────────────────┐
                     * │ Uma chamada `autenticar($email, $senha)` pode deixar  │
                     * │ os valores desses parâmetros dentro de `args`.        │
                     * │ Mantê-los permitiria que fossem incluídos no log ou   │
                     * │ na resposta, quando o histórico fosse exibido.        │
                     * ├───────────────────────────────────────────────────────┤
                     * │ O problema é que os argumentos também podem conter    │
                     * │ arrays e objetos grandes, aumentando bastante o       │
                     * │ tamanho do histórico e tornando a exibição do erro    │
                     * │ confusa.                                              │
                     * │                                                       │
                     * │ Por isso, nesta apresentação de erros, preservamos os │
                     * │ dados que identificam as chamadas, mas optamos por    │
                     * │ retirar `args` completamente.                         │
                     * └───────────────────────────────────────────────────────┘
                     *
                     * $value armazena um array contendo chaves como `file`,
                     * `line`, `function` ou, o que queremos remover, `args`.
                     * Por isso, executamos um `array_filter()` que percorre
                     * todos os campos de $value.
                     *
                     * A opção ARRAY_FILTER_USE_KEY faz a função de filtragem
                     * receber as chaves ao invés do seu valor, o que permite
                     * verificarmos os nomes dos campos. Quando o nome do campo
                     * for `args`, removemos esse campo do array, mantendo os
                     * demais.
                     *
                     * O array filtrado é, então, armazenado em `$trace`.
                     */

                    $trace[] = array_filter($value, function ($key) {
                        return $key !== 'args';
                    }, ARRAY_FILTER_USE_KEY);
                }



                /**
                 * Agora reunimos as informações que serão entregues ao método
                 * `printError()`.
                 *
                 * `getCode()` e `getMessage()` fornecem o código e a mensagem
                 * da falha. `getFile()` e `getLine()` identificam o ponto de
                 * origem registrado no objeto.
                 *
                 * Também passamos `$trace`, que contém o histórico preparado no
                 * laço anterior.
                 */
                
                $code = $e->getCode();
                $message = $e->getMessage();
                $file = $e->getFile();
                $line = $e->getLine();
                
                /**
                 * Neste ponto fazemos um teste que verifica se o objeto `$e` é
                 * uma instância da classe galastri\extensions\Exception (uma
                 * classe própria do framework que é uma extensão do \Exception
                 * nativo do PHP).
                 *
                 * Caso seja, então precisamos atribuir dois valores que vão
                 * incrementar a saída de erro.
                 * 
                 * ┌──────────────────────────┬───────────────────────────────┐
                 * │ `$errorSource`           │ Traz a origem da exceção,     │
                 * │                          │ podendo vir da classe         │
                 * │                          │ Validation, Permission ou     │
                 * │                          │ Authentication. Esta          │
                 * │                          │ informação é importante pois  │
                 * │                          │ permite maior precisão no     │
                 * │                          │ código de retorno HTTP à      │
                 * │                          │ requisição.                   │
                 * │                          │                               │
                 * │                          │ Por padrão ela é `null`,      │
                 * │                          │ podendo manter um status já   │
                 * │                          │ definido ou código 500.       │
                 * ├──────────────────────────┼───────────────────────────────┤
                 * │ `displayValidationError` │ Traz uma autorização          │
                 * │                          │ alternativa para mostrar a    │
                 * │                          │ mensagem de erro de validação │
                 * │                          │ mesmo com a configuração de   │
                 * │                          │ rota `displayErrors`          │
                 * │                          │ desativada.                   │
                 * │                          │                               │
                 * │                          │ Por padrão ela é `false`,     │
                 * │                          │ garantindo que a configuração │
                 * │                          │ de rota seja levada em conta. │
                 * └──────────────────────────┴───────────────────────────────┘
                 */
                
                $errorSource = null;
                $displayValidationError = false;

                if ($e instanceof Exception) {
                    $errorSource = $e->errorSource;
                    $displayValidationError = $e->displayValidationError;
                }
                
                self::printError(
                    $code, $message, $file, $line, $trace,
                    $displayValidationError, $errorSource
                );
            }
        );
    }



    /**
     * 🟢 VERIFICANDO ERROS FATAIS NO ENCERRAMENTO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Registra uma função para o momento em que a execução estiver terminando.
     * Ela será chamada também em encerramentos normais, então não podemos
     * considerar sua chamada, por si só, um sinal de erro.
     *
     * Por isso, consultamos o último erro registrado pelo PHP e só encaminhamos
     * os tipos fatais listados neste método. Se não houver erro ou se ele for
     * de outro tipo, não fazemos nada e a execução se encerra normalmente.
     *
     *
     * ---
     * @return void
     */
    
    private static function registerShutdownFunction(): void
    {
        /**
         * `register_shutdown_function` registra a função para o encerramento da
         * execução. Como esse momento também acontece sem falhas, precisamos
         * conferir o que realmente ocorreu.
         */
        
        register_shutdown_function(
            function (): void
            {
                /**
                 * Consultamos o último erro registrado pelo PHP. Se não existir
                 * nenhum, error_get_last() devolve null. Neste caso, não
                 * fazemos nada e a execução se encerra normalmente.
                 */
                
                $error = error_get_last();

                if ($error === null) {
                    return;
                }



                /**
                 * Ter um último erro não significa que ele causou o
                 * encerramento. Aqui, testamos se o tipo do último erro
                 * pertence à lista de erros fatais que este método trata.
                 */
                
                $errorTypeList = [
                    E_ERROR,
                    E_PARSE,
                    E_CORE_ERROR,
                    E_COMPILE_ERROR,
                    E_USER_ERROR,
                    E_RECOVERABLE_ERROR,
                ];
                
                
                $isFatalError = in_array($error['type'], $errorTypeList, true);



                /**
                 * Se o erro não corresponder a nenhum dos tipos listados, não
                 * fazemos nada e a execução se encerra normalmente.
                 */
                
                if (!$isFatalError) {
                    return;
                }



                /**
                 * Se o erro corresponder a um dos tipos da lista, então será
                 * necessário encaminhar os dados do erro fatal para o mesmo
                 * tratamento usado pelos outros caminhos.
                 *
                 * O histórico coletado aqui pertence ao momento do
                 * encerramento. Ele não reconstrói a pilha original da falha; o
                 * arquivo e a linha do erro vêm dos campos guardados em
                 * `$error`.
                 */
                
                $code = self::getErrorCode($error['type']);
                $message = $error['message'];
                $file = $error['file'];
                $line = $error['line'];
                $debugBacktrace = debug_backtrace(
                    DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS
                );
                
                self::printError($code, $message, $file, $line, $debugBacktrace);
            }
        );
    }



    /**
     * 🟢 CONVERTENDO O NÍVEL DO ERRO EM UM NOME
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * O PHP identifica os níveis de erro com números, usando constantes como
     * `E_WARNING` e `E_ERROR`. Para a resposta e o log ficarem mais fáceis de
     * ler, procuramos aqui o nome correspondente ao número recebido.
     *
     * ✔️ EXEMPLO
     * ┌───────────────────────────────────────────────────────────────────────┐
     * │ A constante `E_WARNING` representa um nível numérico do PHP. Ao       │
     * │ receber esse número, devolvemos o texto 'E_WARNING', que fica mais    │
     * │ fácil de identificar na resposta e no log.                            │
     * └───────────────────────────────────────────────────────────────────────┘
     *
     * A lista não contém todos os níveis existentes no PHP. Quando não
     * encontramos o nível recebido, devolvemos o valor padrão informado por
     * quem chamou. Se nenhum padrão foi passado, esse retorno será `false`.
     *
     *
     * ---
     * @param bool|int $errorLevel  
     * :: Nível recebido do PHP ou valor informado por quem chamou.
     *
     * @param mixed $default  
     * :: Valor devolvido quando o nível não está na lista.
     *
     * @return mixed Nome do nível ou o valor padrão recebido.
     */
    
    private static function getErrorCode(bool|int $errorLevel,
        mixed $default = false): mixed
    {
        /**
         * Criamos um mapeamento dos valores numéricos das constantes aos nomes
         * que queremos escrever na resposta e no log.
         */
        
        $errorMap = [
            E_ERROR => 'E_ERROR',
            E_CORE_ERROR => 'E_CORE_ERROR',
            E_COMPILE_ERROR => 'E_COMPILE_ERROR',
            E_USER_ERROR => 'E_USER_ERROR',
            E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
            E_CORE_WARNING => 'E_CORE_WARNING',
            E_COMPILE_WARNING => 'E_COMPILE_WARNING',
            E_PARSE => 'E_PARSE',
            E_WARNING => 'E_WARNING',
            E_USER_WARNING => 'E_USER_WARNING',
            E_NOTICE => 'E_NOTICE',
            E_USER_NOTICE => 'E_USER_NOTICE',
            E_DEPRECATED => 'E_DEPRECATED',
            E_USER_DEPRECATED => 'E_USER_DEPRECATED',
        ];



        /**
         * Procuramos o nível na lista. O operador ?? devolve `$default` quando
         * não há uma entrada para esse valor.
         */
        
        return $errorMap[$errorLevel] ?? $default;
    }



    /**
     * 🟢 ESCOLHENDO A MENSAGEM QUE PODE SER EXIBIDA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método decide se a resposta vai mostrar a mensagem original do erro
     * ou uma mensagem genérica.
     *
     * Durante o desenvolvimento da aplicação, é normal deixar a configuração de
     * rota `displayErrors` em `true`, para permitir a exibição de mensagens de
     * erro internas do PHP, banco de dados, etc. que trazem informações
     * relevantes pro desenvolvedor.
     *
     * Mas em produção, essa configuração deverá, idealmente, ser desativada com
     * `false` pra garantir que nenhuma informação interna com dados sensíveis
     * vaze para usuários.
     *
     * Se nenhuma dessas condições permitir mostrar a mensagem real, exibimos a
     * mensagem genérica armazenada em `DEFAULT_ERROR_MESSAGE`, na classe
     * `Message`.
     *
     * O arquivo de log salvo na pasta /logs, porém, continua armazenando o
     * caminho original do arquivo.
     *
     *
     * ---
     * @param string $message  
     * :: Mensagem original da falha.
     *
     * @param bool $displayValidationError  
     * :: Autoriza mostrar esta mensagem ou não.
     *
     * @return string Mensagem original ou a mensagem genérica.
     */
    
    private static function
        getMessage(string $message, bool $displayValidationError = false): string
    {
        /**
         * Consultamos as configurações de rota `displayErrors` com fallback pra
         * `false` como padrão enquanto a configuração das rotas ainda não
         * estiver carregada. Assim, se uma falha ocorrer antes do carregamento
         * das configurações da rota, não liberamos a exibição de mensagens
         * internas por esse caminho.
         *
         * Já o parâmetro `$displayValidationError` é usado pela classe
         * Validation para autorizar a exibição de mensagens de erro de
         * validação. Ela opera como um autorizador alternativo, mas que não
         * sobrescreve a configuração de rota, apenas permite que as mensagens
         * de erro de validação de dados possam ser mostradas mesmo com a
         * configuração de rota `displayErrors` em `false`.
         *
         * Sendo a configuração de rota, ou `$displayValidationError`, `true`, a
         * mensagem é retornada para ser mostrada.
         */
        
        if (Config::get('displayErrors', false) || $displayValidationError) {
            return $message;
        }



        /**
         * Sem autorização para exibir a mensagem original, buscamos a mensagem
         * genérica com `Message::get()`. Esse método devolve um array, e a
         * posição [0] contém o texto que será apresentado.
         */
        
        return Message::get('DEFAULT_ERROR_MESSAGE')[0];
    }



    /**
     * 🟢 DECIDINDO SE O NÚMERO DA LINHA DO ERRO PODE APARECER
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método decide se a resposta vai mostrar o número da linha original
     * do erro ou um dado sem valor prático (no caso zero aqui).
     *
     * A questão aqui é a mesma sobre a exibição de mensagens ou não: segurança
     * de dados na hora de exibir erros.
     *
     * O número da linha ajuda a localizar o problema no código, mas também é um
     * detalhe interno da aplicação que precisa ficar sob a autorização da
     * configuração de rotas `displayErrors` para ser mostrada ou não.
     * 
     * O arquivo de log salvo na pasta /logs, porém, continua armazenando a
     * linha original.
     *
     *
     * ---
     * @param int $line  
     * :: Linha original da falha.
     *
     * @return int Linha recebida ou zero quando os detalhes estão ocultos.
     */
    
    private static function getLine(int $line): int
    {
        /**
         * Consultamos a configuração de rota `displayErrors`, com fallback pra
         * `false` como padrão enquanto a configuração das rotas ainda não
         * estiver carregada. Assim, se uma falha ocorrer antes do carregamento
         * das configurações da rota, não liberamos a exibição do número real da
         * linha.
         *
         * Sendo a configuração de rota `true`, retorna o número da linha real
         * para ser mostrado.
         */
        
        return Config::get('displayErrors', false) ? $line : 0;
    }



    /**
     * 🟢 DECIDINDO SE O CAMINHO DO ARQUIVO PODE APARECER
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método decide se a resposta vai mostrar o caminho do arquivo onde o
     * erro ocorreu ou não.
     *
     * A questão aqui é a mesma sobre a exibição de mensagens ou não: segurança
     * de dados na hora de exibir erros.
     *
     * O caminho do arquivo é crucial na depuração de problemas e é uma das
     * informações mais sensíveis por mostrar a estrutura interna do projeto.
     *
     * ℹ️ OBSERVAÇÃO
     * ┌──────────────────────────────────────────────────────────────────────┐
     * │ O caminho do arquivo onde ocorreu o erro é mostrado a partir da raiz │
     * │ do projeto, sem mostrar pastas anteriores.                           │
     * │                                                                      │
     * │ EXEMPLO                                                              │
     * │ ┌──────────────────────────────────────────────────────────────────┐ │
     * │ │ Se ocorrer um erro no arquivo controller Index.php,              │ │
     * │ │ ao invés de mostrar o caminho assim:                             │ │
     * │ │                                                                  │ │
     * │ │ /var/www/galastri-framework/app/controllers/Index.php            │ │
     * │ │                                                                  │ │
     * │ │ ...será mostrado assim:                                          │ │
     * │ │                                                                  │ │
     * │ │ ./app/controllers/Index.php                                      │ │
     * │ └──────────────────────────────────────────────────────────────────┘ │
     * └──────────────────────────────────────────────────────────────────────┘
     *
     * O arquivo de log salvo na pasta /logs, porém, continua armazenando a
     * mensagem original.
     *
     * 
     * ---
     * @param string $file  
     * :: Caminho original do arquivo onde a falha ocorreu.
     *
     * @return string|null Caminho preparado ou null para ocultar o arquivo.
     */
    
    private static function getFile(string $file): ?string
    {
        /**
         * Usamos `pathinfo()` para separar a pasta do nome do arquivo. Na
         * pasta, retiramos o caminho definido em PROJECT_DIR para não repetir a
         * raiz do projeto na apresentação do caminho até o arquivo.
         */
        
        $directory = str_replace(PROJECT_DIR, '', pathinfo($file)['dirname']);



        /**
         * Montamos o caminho simplificado partindo da pasta do projeto até o
         * nome do arquivo, colocando um ponto no início.
         */
        
        $path = '.'.$directory.'/'.pathinfo($file)['basename'];



        /**
         * Consultamos a configuração de rota `displayErrors`, com fallback pra
         * `false` como padrão enquanto a configuração das rotas ainda não
         * estiver carregada. Assim, se uma falha ocorrer antes do carregamento
         * das configurações da rota, não liberamos a exibição do caminho do
         * arquivo. 
         *
         * Sendo a configuração de rota `true`, retorna o caminho do arquivo
         * para ser mostrado.
         */
        
        return Config::get('displayErrors', false) ? $path : null;
    }



    /**
     * 🟢 DECIDINDO SE O HISTÓRICO (TRACE) PODE APARECER
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * O histórico de chamadas, também chamado de trace, mostra o caminho
     * percorrido entre funções e métodos até o ponto em que foi coletado. Isso
     * pode ajudar a entender como chegamos a uma falha.
     *
     * Só devolvemos esse histórico quando `displayErrors` e `showTrace` estão
     * habilitados ao mesmo tempo nas configurações de rota. Isso significa que
     * o desenvolvedor pode fazer a exibição de erros, mas não obriga a resposta
     * a incluir o histórico de chamadas se ele não quiser.
     *
     * O arquivo de log salvo na pasta /logs, porém, continua armazenando o
     * histórico.
     *
     *
     * ---
     * @param array $trace  
     * :: Histórico recebido pelo gerenciador.
     *
     * @return array Histórico recebido ou um array vazio.
     */
    
    private static function getTrace(array $trace): array
    {
        /**
         * Consultamos a configuração de rota `displayErrors` e `showTrace`,
         * ambas com fallback pra `false` como padrão enquanto a configuração
         * das rotas ainda não estiver carregada. Assim, se uma falha ocorrer
         * antes do carregamento das configurações da rota, não liberamos a
         * exibição do histórico.
         *
         * As duas configurações precisam ser verdadeiras para que o histórico
         * seja exibido. `showTrace` permite o histórico, enquanto
         * `displayErrors` permite os detalhes da falha.
         *
         * Se alguma configuração não estiver disponível, usamos false.
         */
        
        if (Config::get('displayErrors', false) && Config::get('showTrace', false)) {
            return $trace;
        }



        /**
         * Sem as duas permissões, mantemos o campo trace na resposta, mas com
         * uma lista vazia.
         */
        
        return [];
    }



    /**
     * 🟢 MONTANDO E ENVIANDO A RESPOSTA DE ERRO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método centraliza todos os dados coletados para preparar a exibição
     * do erro. Quando chegamos aqui, já sabemos qual a mensagem, o código, a
     * origem e o histórico disponíveis para aquela falha.
     *
     * Primeiro registramos a falha no log, caso a configuração de rota
     * `createLogsOnError` esteja habilitada. Nesse registro usamos os dados
     * originais, mesmo que as configurações de exibição determinem que eles
     * fiquem ocultos na resposta.
     *
     * Depois verificamos se podemos deixar a execução continuar. Se a falha não
     * for fatal e a configuração de rota `stopOnNonFatalErrors` estiver
     * desativada, prosseguimos com a execução sem acrescentar conteúdo de falha
     * para não contaminar a saída e nem alterar seu status HTTP.
     *
     * Caso contrário, montamos os dados da resposta, consultando os métodos que
     * decidem quais detalhes podem aparecer. Ajustamos o status HTTP, quando
     * ainda é possível, apresentamos a falha e encerramos a execução.
     *
     * A configuração de rota `output` define como será a apresentação dos
     * dados: `json` envia um JSON, `file` gera uma imagem PNG e os demais
     * valores usam a apresentação em HTML.
     *
     * ℹ️ OBSERVAÇÃO
     * ┌───────────────────────────────────────────────────────────────────────┐
     * │ Este método não faz uma limpeza geral do buffer de saída. Um conteúdo │
     * │ produzido antes da falha pode aparecer junto da resposta de erro. O   │
     * │ arquivo de log, porém, armazena apenas o erro em si.                  │
     * └───────────────────────────────────────────────────────────────────────┘
     *
     *
     * ---
     * @param int|string $code  
     * :: Código que identifica a falha.
     *
     * @param string $message  
     * :: Mensagem original.
     *
     * @param string $file  
     * :: Arquivo original da falha.
     *
     * @param int $line  
     * :: Linha original da falha.
     *
     * @param array $trace  
     * :: Histórico disponível para esta falha.
     *
     * @param bool $displayValidationError  
     * :: Autorizador da classe `Validation` que opera como autorização
     *    alternativa à da configuração de rota `displayErrors`.
     *
     * @param string|null $errorSource  
     * :: Classificação da origem da falha. Será usada para escolher o status
     *    HTTP. Pode receber 'validation', 'authentication', 'permission' ou
     *    `null` quando não houver uma origem específica.
     *
     * @return void
     */
    
    private static function printError(int|string $code, string $message,
        string $file, int $line, array $trace,
        bool $displayValidationError = false,
        ?string $errorSource = null): void
    {
        /**
         * Começamos guardando a data e o horário desta passagem. Os mesmos
         * valores serão usados na resposta e no registro em log, para que ambos
         * identifiquem o mesmo atendimento da falha.
         */
        
        $currentDate = date('Y-m-d');
        $currentTime = date('H:i:s');


        
        /**
         * Antes de decidir se apresentaremos a falha ou deixaremos a execução
         * continuar, conferimos se devemos registrá-la no arquivo de log.
         * Usamos `true` como fallback se a configuração de rota
         * `createLogsOnError` ainda não estiver disponível.
         *
         * Neste caso, passamos os parâmetros originais para o log, sem ocultar
         * dados, já que este é um arquivo de relatório destinado à consulta
         * pelo desenvolvedor e deve conter o máximo de detalhes possível.
         */
        
        if (Config::get('createLogsOnError', true)) {
            
            self::createLogFile(
                $code, $message, $file, $line, $trace, $currentDate, $currentTime
            );
        }



        /**
         * É necessário decidir se o código prossegue em caso de avisos, ou se
         * ele para como se fosse um erro fatal.
         *
         * Este comportamento é definido na configuração de rota
         * `stopOnNonFatalErrors`. Se for `true`, ele encerra a execução mesmo
         * sendo apenas um aviso.
         *
         * A lista abaixo reúne os códigos das falhas não fatais que
         * consideramos nessa decisão, incluindo avisos, notificações e
         * mensagens de depreciação. A comparação é estrita: o código precisa
         * corresponder a um dos nomes da lista.
         */
        
        $nonFatalErrorList = [
            'E_WARNING',
            'E_CORE_WARNING',
            'E_COMPILE_WARNING',
            'E_USER_WARNING',
            'E_NOTICE',
            'E_USER_NOTICE',
            'E_DEPRECATED',
            'E_USER_DEPRECATED',
        ];
        
        $isNonFatalError = in_array($code, $nonFatalErrorList, true);


        
        /**
         * Se a falha não for fatal e `stopOnNonFatalErrors` estiver desativado,
         * deixamos a execução continuar. O log já foi tratado acima, então
         * podemos retornar sem mostrar a falha nem alterar o status HTTP.
         *
         * Nos demais casos, seguimos para a apresentação da falha e encerramos
         * a execução no final deste método.
         *
         * ⚠️ IMPORTANTE
         * ┌─────────────────────────────────────────────────────────────────┐
         * │ O `return` encerra apenas este método. Ele permite que o código │
         * │ continue quando o próprio PHP ainda permite essa continuação.   │
         * │                                                                 │
         * │ Este trecho NÃO trata de casos em que o próprio PHP encerra a   │
         * │ execução, como lançamento de exceções não capturadas, por       │
         * │ exemplo. Nestes casos, o comportamento do PHP de encerrar a     │
         * │ execução não tem como ser controlada.                           │
         * └─────────────────────────────────────────────────────────────────┘
         */
        
        if ($isNonFatalError && !Config::get('stopOnNonFatalErrors', true)) {
            return;
        }
        
        
        
        /**
         * Montamos os campos que serão enviados na resposta. Arquivo, linha,
         * mensagem e histórico passam pelas verificações de exibição antes de
         * entrar nesse array, decidindo se mostrarão os dados originais ou se
         * serão ocultados da exibição visual.
         *
         * Os campos `warning` e `error` ficam como `true` nesta saída de falha.
         * Eles são indicadores da resposta, não a classificação usada para
         * decidir se a execução pode continuar. É útil pois o frontend
         * facilmente consegue detectar se houve erro ou não para decidir o que
         * fazer.
         */

        $data = [
            'date' => $currentDate,
            'time' => $currentTime,
            'code' => $code,
            'origin' => self::getFile($file),
            'line' => self::getLine($line),
            'message' => self::getMessage($message, $displayValidationError),
            'trace' => self::getTrace($trace),
            'warning' => true,
            'error' => true,
        ];
        
        
        
        /**
         * O status HTTP faz parte dos cabeçalhos da resposta. Só tentamos
         * ajustá-lo se eles ainda não foram enviados. Deve ser assim pois
         * falhas podem ocorrer durante um streaming iniciado pela classe
         * `ResponseStream`, usada para enviar dados antes do fim da requisição,
         * por exemplo.
         *
         * Passamos `$errorSource` para getHttpStatusCode(), que escolhe o status
         * conforme a classificação da falha.
         */

        if (!headers_sent()) {
            http_response_code(self::getHttpStatusCode($errorSource));
        }
        
        
        
        /**
         * Por fim, escolhemos a apresentação do erro conforme a saída definida
         * na configuração de rota `output`. Se a configuração ainda não estiver
         * disponível, usamos `view` por padrão, que mostra os campos como texto
         * em HTML.
         */

        switch (Config::get('output', 'view')) {
            case 'json':
                self::printErrorJson($data);
                break;

            case 'file':
                self::printErrorFile($data);
                break;

            default:
                self::printErrorView($data);
        }
        
        exit;
    }



    /**
     * 🟢 ENVIANDO O ERRO EM JSON
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Quando a saída configurada nas rotas é um `json`, os dados do erro devem
     * estar em formato JSON. Aqui as decisões sobre ocultar mensagem, arquivo,
     * linha e histórico já foram tomadas por `printError()`.
     *
     * Se a resposta já estiver em streaming, usamos Json::stream() para enviar
     * mais uma linha no mesmo formato das etapas anteriores. Isso permite que
     * quem está recebendo acompanhe também a falha, sem mudar o formato no meio
     * da resposta.
     *
     * Se o streaming ainda não começou, enviamos um JSON comum, com recuos e
     * quebras de linha para facilitar sua leitura.
     *
     *
     * ---
     * @param array $data  
     * :: Dados já preparados para a resposta.
     *
     * @return void
     */
    
    private static function printErrorJson(array $data): void
    {
        /**
         * Testamos se há uma transmissão por streaming e acrescentamos o erro
         * como uma nova linha JSON. O `return` impede que os mesmos dados sejam
         * enviados novamente na finalização deste método, que seria uma
         * repetição desnecessária.
         */
        
        if (Json::isStreaming()) {
            Json::stream($data);
            return;
        }



        /**
         * Não sendo um streaming, a resposta é um JSON comum. Informamos o tipo
         * do conteúdo enquanto os cabeçalhos ainda não tiverem sido enviados.
         */
        
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }



        /**
         * Convertemos o `array` em formato JSON e enviamos o texto.
         *
         * Ativamos algumas opções para ajustar a saída usando as constantes do
         * PHP dentro do `json_encode`:
         * ┌────────────────────────────────┬────────────────────────────────────┐
         * │ `JSON_PRETTY_PRINT`            │ Organiza os recuos e torna a       │
         * │                                │ leitura do JSON mais fácil.        │
         * ├────────────────────────────────┼────────────────────────────────────┤
         * │ `JSON_UNESCAPED_SLASHES`       │ Deixam barras e caracteres Unicode │
         * │                                │ sem escapes desnecessários.        │
         * │ `JSON_UNESCAPED_UNICODE`       │                                    │
         * ├────────────────────────────────┼────────────────────────────────────┤
         * │ `JSON_INVALID_UTF8_SUBSTITUTE` │ Substitui sequências inválidas em  │
         * │                                │ UTF-8, permitindo representar o    │
         * │                                │ restante do texto.                 │
         * └────────────────────────────────┴────────────────────────────────────┘
         */
        
        echo json_encode(
            $data,
               JSON_PRETTY_PRINT
             | JSON_UNESCAPED_SLASHES
             | JSON_UNESCAPED_UNICODE
             | JSON_INVALID_UTF8_SUBSTITUTE
        );
    }



    /**
     * 🟢 ENVIANDO O ERRO EM HTML / VIEW
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Quando a saída configurada nas rotas é um `view`, os dados do erro devem
     * estar em formato HTML. Aqui as decisões sobre ocultar mensagem, arquivo,
     * linha e histórico já foram tomadas por `printError()`.
     *
     * Cada campo ocupa sua própria linha. Quando o valor é um array, como
     * acontece com os dados de `trace`, usamos `var_export()` para
     * transformá-lo em um texto que mostre sua estrutura.
     *
     * O conteúdo passa por `htmlspecialchars()` antes de ser exibido. Se uma
     * mensagem contiver <b>Teste</b>, por exemplo, queremos enxergar essas tags
     * escritas, sem que elas alterem a apresentação da página.
     *
     *
     * ---
     * @param array $data  
     * :: Dados já preparados para a resposta.
     *
     * @return void
     */
    
    private static function printErrorView(array $data): void
    {
        /**
         * Adicionamos uma tag `<pre>` para mantém os espaços e as quebras de
         * linha. O estilo `pre-wrap` também permite que linhas longas sejam
         * quebradas para caber no espaço disponível.
         */
        
        echo '<pre style="white-space: pre-wrap;">';



        /**
         * Percorremos os campos da resposta. Cada passagem prepara e exibe o
         * nome do campo junto de seu valor.
         */
        
        foreach ($data as $key => $value) {
            
            /**
             * Um array não pode ser impresso diretamente ao texto da linha. O
             * `var_export()` devolve uma representação escrita de suas chaves e
             * valores.
             */
            
            if (Tools::typeOf($value) === 'array') {
                $value = var_export($value, true);
            }



            /**
             * Queremos apresentar o texto em formato tabular. O `str_pad()`
             * reserva pelo menos dez posições para o nome do campo, alinhando
             * os dois-pontos dos nomes mais curtos.
             *
             * Fazemos a impressão do resultado na tela usando
             * `htmlspecialchars()` que prepara a linha para aparecer como texto
             * no HTML.
             * 
             * Ativamos algumas opções para ajustar a saída usando as
             * constantes do PHP dentro do `htmlspecialchars`:
             * 
             * ┌──────────────────┬────────────────────────────────────────────┐
             * │ `ENT_QUOTES`     │ Inclui as aspas na conversão.              │
             * ├──────────────────┼────────────────────────────────────────────┤
             * │ `ENT_SUBSTITUTE` │ Substitui trechos inválidos em UTF-8.      │
             * └──────────────────┴────────────────────────────────────────────┘
             */
            
            $keyName = str_pad($key, 10);
            $output = $keyName.': '.$value."\n";
            
            echo htmlspecialchars(
                $output, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'
            );
        }



        /**
         * Fechamos o bloco depois de exibir todos os campos.
         */
        
        echo '</pre>';
    }



    /**
     * 🟢 ENVIANDO O ERRO EM IMAGEM / FILE
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Quando a saída configurada nas rotas é um `file`, os dados do erro devem
     * estar em formato de arquivo. Escolhemos um arquivo de imagem, que permite
     * exibir os erros diretamente nela. Aqui as decisões sobre ocultar
     * mensagem, arquivo, linha e histórico já foram tomadas por `printError()`.
     *
     * A imagem é criada em memória e seu conteúdo é enviado diretamente na
     * resposta em formato PNG.
     *
     * Transformamos os campos em linhas de texto, calculamos a altura
     * necessária e desenhamos esse conteúdo sobre um fundo escuro.
     *
     * ℹ️ OBSERVAÇÃO
     * ┌──────────────────────────────────────────────────────────────────┐
     * │ A criação da imagem depende da extensão `GD` com suporte à fonte │
     * │ TrueType do PHP, além dos arquivos de fonte e logotipo definidos │
     * │ abaixo. O código tenta carregar esses arquivos diretamente.      │
     * └──────────────────────────────────────────────────────────────────┘
     *
     *
     * ---
     * @param array $data  
     * :: Dados já preparados para a resposta.
     *
     * @return void
     */
    
    private static function printErrorFile(array $data): void
    {
        
        /**
         * Definimos as medidas da apresentação:
         * - Largura da imagem
         * - Distância entre as linhas
         * - Margem
         * - Tamanho reservado para o logotipo.
         *
         * Os valores estão em pixels, exceto o tamanho da fonte usado adiante.
         */
        
        $width = 600;
        $lineHeight = 18;
        $margin = 15;
        $logoWidth = 40;
        $logoHeight = 40;



        /**
         * Definimos os arquivos usados para desenhar as letras e carregar o
         * logotipo. A fonte TrueType permite escrever o texto com
         * `imagettftext()`.
         */
        
        $fontPath = PROJECT_DIR.'/galastri/misc/error-handler-font.ttf';
        $logoPath = PROJECT_DIR.'/galastri/misc/error-handler-logo.png';
        $fontSize = 11;



        /**
         * Preparamos a lista de linhas que serão desenhadas. Precisamos
         * conhecê-la antes de criar a imagem, pois a altura depende de quantas
         * linhas houver.
         */
        
        $lines = [];



        /**
         * Para cada dado, precisamos fazer uma formatação, ajustando
         * espaçamento e reconhecimento de valores.
         */
        
        foreach ($data as $key => $value) {
            
            /**
             * Queremos apresentar o texto em formato tabular. O `str_pad()`
             * reserva pelo menos dez posições para o nome do campo, alinhando
             * os dois-pontos dos nomes mais curtos.
             */
            
            $fieldName = str_pad($key, 10);
            
            
            
            /**
             * Transformamos cada campo em texto através do loop. O
             * `var_export()` preserva uma representação legível dos valores,
             * inclusive `arrays`, `null` e `booleanos`.
             */ 
            
            $fieldValue = var_export($value, true);
            $text = $fieldName.': '.$fieldValue;

            
            
            /**
             * O `wordwrap()` procura pontos para quebrar o texto na largura
             * informada. Como não ativamos a quebra forçada de palavras, um
             * trecho longo sem espaços pode ultrapassar esse limite.
             */
            
            $wrappedText = wordwrap($text, 64);
            
            
            
            /**
             * O `explode()` separa o texto nas quebras de linha, criando um
             * array em que cada elemento contém uma linha a ser desenhada.
             */
            
            $textLines = explode("\n", $wrappedText);

            
            
            /**
             * O `array_merge()` acrescenta as linhas à lista.
             */
            
            $lines = array_merge($lines, $textLines);

            
            
            /**
             * Depois dos campos line e message, incluímos uma linha vazia para
             * separar visualmente as partes do erro.
             */
            
            if (in_array($key, ['line', 'message'])) {
                $lines[] = '';
            }
        }



        /**
         * A altura da imagem é definida agora. Colocamos uma margem no topo e
         * na base, além do espaço necessário para caberem todas as linhas.
         * 
         * Assim, mensagens com mais linhas produzem uma imagem mais alta.
         */
        
        $height = $margin * 2 + count($lines) * $lineHeight;



        /**
         * Criamos a imagem em memória, armazenada em $canvas. Reservamos as
         * cores do fundo e do texto através do padrão RGB.
         */
        
        $canvas = imagecreatetruecolor($width, $height);
        $bgColor = imagecolorallocate($canvas, 60, 61, 66);
        $textColor = imagecolorallocate($canvas, 255, 255, 255);
        
        
        
        /**
         * Preenchemos toda a área com o fundo escuro.
         */
        
        imagefilledrectangle($canvas, 0, 0, $width, $height, $bgColor);



        /**
         * Agora começamos a desenhar o texto na imagem.
         *
         * A primeira linha começa depois da margem do topo, levando em conta o
         * tamanho da fonte.
         *
         * `$y` armazena a posição atual onde a linha será desenhada. Conforme
         * vamos desenhando cada linha, aumentamos `$y` pela altura reservada
         * para ir posicionando uma linha abaixo da outra.
         *
         * A função `imagettftext()` faz o desenho levando em conta todas as
         * configurações preparadas até aqui.
         */
        
        $y = $margin + $fontSize;
        
        foreach ($lines as $line) {
            imagettftext(
                $canvas, $fontSize, 0, $margin, $y, $textColor, $fontPath, $line
            );
            
            $y += $lineHeight;
        }



        /**
         * Carregamos o logotipo e calculamos sua posição a partir do canto
         * inferior direito, mantendo a margem definida no início.
         *
         * A função `imagecopyresampled()` copia a imagem original para o
         * tamanho escolhido. Depois, podemos remover a referência ao logotipo
         * que não será mais utilizado.
         */
        
        $logoImage = imagecreatefrompng($logoPath);
        
        $xPos = $width - $logoWidth - $margin;
        $yPos = $height - $logoHeight - $margin;
        
        imagecopyresampled($canvas, $logoImage, $xPos, $yPos, 0, 0, $logoWidth, $logoHeight, imagesx($logoImage), imagesy($logoImage));
        unset($logoImage);



        /**
         * Por fim, informamos na cabeçalho da resposta que o conteúdo será um
         * PNG e enviamos a imagem criada. Como `imagepng()` não recebe um
         * caminho de destino, sua saída vai para a resposta, sendo renderizada
         * no navegador.
         *
         * Finalizamos removendo a referência à imagem que não será mais
         * utilizada.
         */
        
        header('Content-type: image/png');
        imagepng($canvas);
        unset($canvas);
    }



    /**
     * 🟢 REGISTRANDO OS DETALHES EM LOG
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método armazena os detalhes da falha no arquivo de log. Isso mantém
     * um registro que nos permite consultar o que aconteceu mesmo quando a
     * resposta mostrou apenas uma mensagem genérica.
     *
     * Recebemos os dados originais de printError(), incluindo a mensagem, o
     * caminho do arquivo, a linha e o histórico, ou seja, sem dados ocultos.
     *
     * Cada dia gera um arquivo de log sempre que for necessário. Cada novo
     * registro ocorrido no mesmo dia entra no início do arquivo já existente,
     * deixando as falhas mais recentes antes das anteriores.
     *
     * 
     * ---
     * @param int|string $code  
     * :: Código original da falha.
     *
     * @param string $message  
     * :: Mensagem original.
     *
     * @param string $file  
     * :: Caminho original do arquivo.
     *
     * @param int $line  
     * :: Linha original.
     *
     * @param array $trace  
     * :: Histórico disponível para o registro.
     *
     * @param string $currentDate  
     * :: Data usada no conteúdo e no nome do arquivo.
     *
     * @param string $currentTime  
     * :: Horário usado no conteúdo do registro.
     *
     * @return void
     */
    
    private static function createLogFile(int|string $code, string $message,
        string $file, int $line, array $trace,
        string $currentDate, string $currentTime): void
    {
        
        /**
         * Precisamos garantir que a pasta de logs exista. Se ela não existir,
         * ela será criada agora com nível de permissão 0700.
         *
         * ℹ️ INFORMAÇÃO
         * ┌───────────────────────────────────────────────────────────────────┐
         * │ `Tools::createDir()` não altera as permissões se a pasta já       │
         * │ existir.                                                          │
         * └───────────────────────────────────────────────────────────────────┘
         */
        
        Tools::createDir(PROJECT_DIR.'/logs', 0700);



        /**
         * Em seguida executamos a ferramenta de criação de arquivos e
         * informamos o caminho do arquivo + os dados a serem inseridos.
         *
         * 
         * 
         * var_export() representa os valores em texto. Uma mensagem com aspas,
         * por exemplo, permanece identificável dentro do registro.
         *
         * INSERT_CONTENT_AT_START coloca o novo registro antes do conteúdo
         * existente. O valor 0600 define a permissão aplicada por Tools ao
         * criar o arquivo, sem alterar por aqui as permissões de um arquivo que
         * já existia.
         */
        
        Tools::writeFile(
            /**
             * O caminho possui placeholder {{}}. Substituímos ele pela data
             * atual.
             */
            Tools::replacePlaceholders(self::PATH_LOG, [$currentDate]),
            
            /**
             * O conteúdo reúne todos os dados reunidos pelo `printError()`.
             * Colocamos cada dado em uma linha.
             *
             * O `implode()` aqui acrescenta as quebras `\n` sem a necessidade
             * de ficar declarando elas em cada campo.
             */
            implode("\n", [
                'date     : '.$currentDate,
                'time     : '.$currentTime,
                'code     : '.var_export($code, true),
                'origin   : '.var_export($file, true),
                'line     : '.var_export($line, true),
                'message  : '.var_export($message, true),
                '',
                'trace    : '.var_export($trace, true),
                "\n\n----------------------------------------\n\n\n",
            ]),
            
            /**
             * INSERT_CONTENT_AT_START é uma constante que orienta o método
             * writeFile() a colocar o novo registro no topo do arquivo.
             *
             * O valor 0600 define a permissão de acesso UNIX caso se tenha que
             * criar o arquivo. Se ele já existir, ele não altera essas
             * permissões.
             */
            INSERT_CONTENT_AT_START,
            0600
        );
    }



    /**
     * 🟢 ESCOLHENDO O STATUS HTTP DA FALHA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Aqui escolhemos o status HTTP que acompanhará a resposta de erro, baseado
     * na origem recebida de `$errorSource`.
     *
     * Se a origem estiver ausente ou não for reconhecida, consultamos o status
     * já definido para a resposta. Caso esteja entre 400 e 599, mantemos esse
     * valor para preservar uma decisão anterior, como uma resposta 404.
     *
     * Sem uma origem reconhecida e sem um status de erro anterior, usamos 500
     * como padrão para a falha.
     *
     * ⚠️ IMPORTANTE
     * ┌───────────────────────────────────────────────────────────────────────┐
     * │ A escolha do status HTTP não decide se dados podem aparecer ou não    │
     * │ nas informações da falha, nem se a execução pode prosseguir. Essas    │
     * │ verificações são feitas separadamente.                                │
     * └───────────────────────────────────────────────────────────────────────┘
     *
     *
     * ---
     * @param string|null $errorSource  
     * :: Classificação da falha: 'validation', 'authentication', 'permission'
     *    ou null quando não houver uma classificação específica.
     *
     * @return int Status HTTP que deve acompanhar a resposta.
     */

    private static function getHttpStatusCode(?string $errorSource): int
    {
        /**
         * Primeiro verificamos se a falha possui uma origem conhecida. Essas
         * origens têm prioridade sobre um status já definido para a resposta.
         */

        switch ($errorSource) {
            case 'validation':
                return 422;

            case 'authentication':
            case 'permission':
                return 403;
        }



        /**
         * Sem uma origem reconhecida, consultamos o status que já está definido
         * para esta resposta, sem modificá-lo nesta chamada.
         *
         * Se já houver um status de erro entre 400 e 599, devolvemos esse mesmo
         * valor. Assim, preservamos uma decisão anterior, como um status 404.
         */

        $currentStatus = http_response_code();

        if ($currentStatus !== false && $currentStatus >= 400 && $currentStatus <= 599) {
            return $currentStatus;
        }



        /**
         * Sem uma classificação reconhecida e sem um status de erro anterior,
         * usamos 500 como status padrão da falha.
         */

        return 500;
    }
}



// -----------------------------------------------------------------------------
// ◀️ VOLTAR A UM TÓPICO ANTERIOR:
// 🔗 LINK: /galastri/bootstrap/bootstrap.php
// 🔗 LINK: /galastri/core/Galastri.php
