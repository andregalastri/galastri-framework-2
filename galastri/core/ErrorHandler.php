<?php
/**
 * -----------------------------------------------------------------------------
 * 📂
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                   ARQUIVO /galastri/core/ErrorHandler.php                ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 * Este arquivo define o gerenciador de erros do framework. Ele recebe as
 * informações de uma falha, prepara o que pode ser mostrado na resposta e,
 * quando configurado, guarda os detalhes em um arquivo de log.
 *
 * O gerenciador é ativado no bootstrap, antes do bootstrap da aplicação e
 * do processamento da requisição por Galastri::run().
 *
 * 🔗 LINK: /galastri/bootstrap/bootstrap.php
 * ─────────────────────────────────────────────────────────────────────────────
 */



/**
 * 🟢 NAMESPACE DA CLASSE
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * ErrorHandler pertence ao núcleo do framework. Por isso, seu namespace segue
 * a pasta galastri/core, onde ficam as classes responsáveis por coordenar o
 * funcionamento da aplicação.
 */

namespace galastri\core;



/**
 * 🟢 REFERENCIAÇÃO AOS NOMES DAS CLASSES USADOS
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * As declarações use permitem escrever os nomes curtos das classes usadas
 * neste arquivo.
 *
 * Config consulta as configurações; Json participa da resposta em JSON;
 * Message fornece as mensagens; Tools reúne os recursos para trabalhar com
 * arquivos. Exception é a exceção própria do framework, que pode autorizar a
 * exibição de uma mensagem específica.
 *
 * Essas declarações apenas referenciam os nomes. O carregamento dos arquivos
 * continua sendo responsabilidade do autoloader.
 */

use galastri\core\config\Config;
use galastri\core\output\Json;
use galastri\language\Message;
use galastri\modules\Tools;
use galastri\extensions\Exception;



/**
 * 🟢 CLASSE ErrorHandler
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Quando acontece uma falha, queremos apresentar uma resposta que combine com
 * a saída da requisição e registrar informações que ajudem a descobrir o que
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
 * Chamar run() prepara esses caminhos. As funções registradas por ele só
 * entram em ação quando o PHP comunica um erro, uma exceção não capturada ou o
 * encerramento da execução.
 */

final class ErrorHandler
{
    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                              CONSTANTES                               ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     */



    /**
     * 🟢 CAMINHO DO ARQUIVO DE LOG
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Define onde os registros de erro serão guardados. O trecho {{}} é um
     * espaço reservado que createLogFile() troca pela data atual.
     *
     * ✔️ EXEMPLO
     * ┌───────────────────────────────────────────────────────────────────────┐
     * │ Uma falha ocorrida em 2026-10-26 entra neste arquivo:                 │
     * │                                                                       │
     * │ logs/ErrorHandler-2026-10-26.log                                      │
     * │                                                                       │
     * │ As falhas do mesmo dia compartilham o arquivo. Os registros mais      │
     * │ recentes ficam no início.                                             │
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
     */



    /**
     * 🟢 CLASSE ESTÁTICA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Os recursos desta classe são usados diretamente, como em
     * ErrorHandler::run(), sem precisar criar um objeto.
     *
     * Deixamos o construtor privado para impedir que a classe seja instanciada
     * com new ErrorHandler().
     */
    
    private function __construct() {}



    /**
     * 🟢 ATIVANDO O GERENCIADOR DE ERROS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Prepara o PHP para encaminhar as falhas aos métodos desta classe.
     *
     * Registramos uma função para os erros que o PHP permite tratar durante a
     * execução, outra para exceções não capturadas e uma terceira para
     * conferir o último erro no encerramento.
     *
     * Essas funções ficam aguardando os acontecimentos correspondentes para
     * executar o tratamento registrado.
     *
     * O bootstrap faz essa ativação antes de carregar o bootstrap da
     * aplicação. Uma falha que impeça o código de chegar até aqui ainda não
     * terá esses tratamentos disponíveis.
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
     * por set_error_handler(), como um aviso E_WARNING.
     *
     * O PHP entrega o nível, a mensagem, o arquivo e a linha do erro. Primeiro
     * conferimos se esse nível está habilitado em error_reporting(). Depois,
     * encaminhamos as informações para printError().
     *
     * Alguns erros fatais não passam por esse mecanismo. Por isso, run()
     * também registra uma verificação no encerramento da execução.
     *
     *
     * ---
     * @return void
     */
    
    private static function setErrorHandler(): void
    {
        /**
         * A função sem nome passada a set_error_handler() será chamada pelo
         * PHP quando houver um erro que esse mecanismo possa tratar. Seus
         * parâmetros recebem as informações da falha.
         */
        
        set_error_handler(
            function (int $level, string $message, string $file, int $line): void
            {
                /**
                 * O PHP pode chamar nosso tratamento mesmo para um nível que
                 * não está habilitado em error_reporting(). Por isso, fazemos
                 * essa conferência antes de preparar a resposta.
                 *
                 * O operador & verifica se o nível recebido está entre os
                 * níveis ativos. Aqui ele não cria uma referência entre
                 * variáveis. Se o resultado for zero, não encaminhamos esse
                 * erro para printError().
                 */
                
                if ((error_reporting() & $level) === 0) {
                    return;
                }



                /**
                 * Transformamos o nível em um nome. Se ele não estiver na
                 * lista de getErrorCode(), usamos E_WARNING como alternativa.
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
                
                self::printError(self::getErrorCode($level, 'E_WARNING'), $message, $file, $line, debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS));
            }
        );
    }



    /**
     * 🟢 RECEBENDO EXCEÇÕES NÃO CAPTURADAS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Registra o tratamento que será usado quando uma exceção chegar ao PHP
     * sem ter sido capturada por um catch da aplicação. Esse caminho também
     * pode receber erros representados por objetos, como TypeError.
     *
     * O objeto recebido informa a mensagem, o código, a origem e o histórico
     * de chamadas. Retiramos os argumentos desse histórico antes de
     * encaminhá-lo para printError(), para não incluir os valores passados às
     * funções nessa lista.
     *
     * Se o objeto for uma Exception do framework, sua propriedade displayError
     * também participa da decisão de mostrar a mensagem original. Essa
     * autorização vale para a mensagem; arquivo, linha e histórico continuam
     * seguindo suas próprias verificações.
     *
     * Esse tratamento global não devolve a execução ao trecho que lançou a
     * exceção. O PHP encerra a execução depois de chamar a função registrada.
     *
     *
     * ---
     * @return void
     */
    
    private static function setExceptionHandler(): void
    {
        /**
         * Entregamos ao PHP a função que receberá uma exceção não capturada. O
         * parâmetro $e contém o objeto da falha, e não apenas seu texto.
         */
        
        set_exception_handler(
            function (object $e): void
            {
                /**
                 * Começamos com uma lista vazia para montar a versão do
                 * histórico que será enviada ao gerenciador.
                 */
                
                $trace = [];



                /**
                 * getTrace() devolve uma lista de chamadas. Cada entrada reúne
                 * informações como função, classe, arquivo, linha e, quando
                 * disponíveis, os argumentos recebidos.
                 *
                 * Percorremos essas entradas para retirar o campo args antes
                 * de guardar cada uma em $trace.
                 */
                
                foreach ($e->getTrace() as $key => $value) {
                    /**
                     * Esta conferência ignora uma entrada externa chamada
                     * args, se houver. Na lista normalmente devolvida por
                     * getTrace(), as chaves externas são números; o campo args
                     * fica dentro de cada entrada e é retirado na etapa
                     * seguinte.
                     */
                    
                    if ($key === 'args') {
                        continue;
                    }



                    /**
                     * ARRAY_FILTER_USE_KEY faz a função de filtragem receber o
                     * nome do campo, em vez de seu valor. Mantemos todos os
                     * campos cuja chave seja diferente de args.
                     *
                     * Guardamos a entrada filtrada em $trace, preservando a
                     * ordem das chamadas.
                     */
                    
                    $trace[] = array_filter($value, function ($key){
                        return $key !== 'args';
                    }, ARRAY_FILTER_USE_KEY);
                }



                /**
                 * Retiramos do objeto o código, a mensagem, o arquivo e a
                 * linha. Enviamos também o histórico que acabamos de preparar.
                 *
                 * A última condição só será verdadeira se $e for uma Exception
                 * do framework com displayError habilitado. O operador && só
                 * consulta essa propriedade depois de confirmar o tipo do
                 * objeto.
                 */
                
                self::printError($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine(), $trace, $e instanceof Exception && $e->displayError);
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
     * Consultamos o último erro registrado pelo PHP e só encaminhamos os tipos
     * fatais listados neste método. Se não houver erro ou se ele for de outro
     * tipo, não fazemos nada.
     *
     * Essa verificação complementa o manipulador de erros. Ela depende de já
     * ter sido registrada: não recupera uma falha que impediu o próprio
     * bootstrap de executar.
     *
     *
     * ---
     * @return void
     */
    
    private static function registerShutdownFunction(): void
    {
        /**
         * A função abaixo fica registrada para o encerramento da execução.
         * Como esse momento também acontece sem falhas, precisamos conferir o
         * que realmente ocorreu.
         */
        
        register_shutdown_function(
            function (): void
            {
                /**
                 * Consultamos o último erro registrado pelo PHP. Se não
                 * existir nenhum, error_get_last() devolve null e encerramos
                 * esta verificação.
                 */
                
                $error = error_get_last();

                if ($error === null) {
                    return;
                }



                /**
                 * Ter um último erro não significa que ele causou o
                 * encerramento. Conferimos se seu tipo pertence à lista de
                 * erros fatais que este método trata.
                 *
                 * O último argumento true exige uma comparação estrita entre o
                 * tipo recebido e as constantes da lista.
                 */
                
                $isFatalError = in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR], true);



                /**
                 * Um aviso ou outro nível fora dessa lista não deve gerar uma
                 * nova resposta de erro só porque a execução chegou ao fim.
                 */
                
                if (!$isFatalError) {
                    return;
                }



                /**
                 * Encaminhamos os dados do erro fatal para o mesmo tratamento
                 * usado pelos outros caminhos.
                 *
                 * O histórico coletado aqui pertence ao momento do
                 * encerramento. Ele não reconstrói a pilha original da falha;
                 * o arquivo e a linha do erro vêm dos campos guardados em
                 * $error.
                 */
                
                self::printError(self::getErrorCode($error['type']), $error['message'], $error['file'], $error['line'], debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS));
            }
        );
    }



    /**
     * 🟢 CONVERTENDO O NÍVEL DO ERRO EM UM NOME
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * O PHP identifica os níveis de erro com números, usando constantes como
     * E_WARNING e E_ERROR. Para a resposta e o log ficarem mais fáceis de ler,
     * procuramos aqui o nome correspondente ao número recebido.
     *
     * ✔️ EXEMPLO
     * ┌───────────────────────────────────────────────────────────────────────┐
     * │ A constante E_WARNING representa um nível numérico do PHP. Ao receber │
     * │ esse número, devolvemos o texto 'E_WARNING', que fica mais fácil de   │
     * │ identificar na resposta e no log.                                     │
     * └───────────────────────────────────────────────────────────────────────┘
     *
     * A lista não contém todos os níveis existentes no PHP. Quando não
     * encontramos o nível recebido, devolvemos o valor padrão informado por
     * quem chamou. Se nenhum padrão foi passado, esse retorno será false.
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
    
    private static function getErrorCode(bool|int $errorLevel, mixed $default = false): mixed
    {
        /**
         * Associamos os valores numéricos das constantes aos nomes que
         * queremos escrever na resposta e no log.
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
        ];



        /**
         * Procuramos o nível na lista. O operador ?? devolve $default quando
         * não há uma entrada para esse valor.
         */
        
        return $errorMap[$errorLevel] ?? $default;
    }



    /**
     * 🟢 ESCOLHENDO A MENSAGEM QUE PODE SER EXIBIDA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Decide se a resposta vai mostrar a mensagem original ou uma mensagem
     * genérica.
     *
     * Durante o desenvolvimento, displayErrors pode permitir os detalhes da
     * falha. Quando essa configuração está desativada, ainda podemos mostrar
     * uma mensagem específica se quem chamou passar $displayError como true.
     *
     * ℹ️ OBSERVAÇÃO
     * ┌───────────────────────────────────────────────────────────────────────┐
     * │ A autorização em $displayError permite mostrar esta mensagem. Ela não │
     * │ libera o arquivo, a linha ou o histórico da falha. Esses campos       │
     * │ continuam dependendo de suas próprias verificações.                   │
     * └───────────────────────────────────────────────────────────────────────┘
     *
     * Se nenhuma dessas condições permitir a mensagem original, buscamos
     * DEFAULT_ERROR_MESSAGE na classe Message. O log continua recebendo a
     * mensagem original por outro caminho.
     *
     *
     * ---
     * @param string $message
     * :: Mensagem original da falha.
     *
     * @param bool $displayError
     * :: Autoriza mostrar esta mensagem mesmo sem displayErrors.
     *
     * @return string Mensagem original ou a mensagem genérica.
     */
    
    private static function getMessage(string $message, bool $displayError = false): string
    {
        /**
         * Consultamos displayErrors usando false como alternativa caso a
         * configuração ainda não esteja disponível.
         *
         * Basta essa configuração ou a autorização recebida em $displayError
         * ser verdadeira para devolver a mensagem original.
         */
        
        if (Config::get('displayErrors', false) || $displayError) {
            return $message;
        }



        /**
         * Sem autorização para exibir os detalhes, buscamos a mensagem
         * genérica. Message::get() devolve um array; a posição zero contém o
         * texto que será apresentado.
         */
        
        return Message::get('DEFAULT_ERROR_MESSAGE')[0];
    }



    /**
     * 🟢 DECIDINDO SE A LINHA PODE APARECER
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * A linha ajuda a localizar o problema no código, mas também é um detalhe
     * interno da aplicação.
     *
     * Quando displayErrors está habilitado, devolvemos a linha recebida. Caso
     * contrário, colocamos zero na resposta. A linha original continua
     * disponível para o registro em log.
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
         * A condição escolhe entre a linha original e zero. Mesmo que uma
         * exceção tenha autorizado mostrar sua mensagem, esta decisão continua
         * dependendo de displayErrors.
         */
        
        return Config::get('displayErrors', false) ? $line : 0;
    }



    /**
     * 🟢 PREPARANDO O CAMINHO DO ARQUIVO PARA A RESPOSTA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Prepara o caminho que identifica o arquivo da falha na resposta.
     *
     * Retiramos o caminho da raiz do projeto da parte que representa a pasta e
     * acrescentamos um ponto no início. Para um arquivo dentro do projeto, o
     * resultado fica parecido com ./app/controllers/Index.php.
     *
     * Esse caminho só é devolvido quando displayErrors está habilitado. Caso
     * contrário, a resposta recebe null. O caminho original é mantido para o
     * log.
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
         * pathinfo() separa a pasta do nome do arquivo. Na pasta, retiramos o
         * caminho definido em PROJECT_DIR para não repetir a raiz do projeto
         * na apresentação.
         */
        
        $directory = str_replace(PROJECT_DIR, '', pathinfo($file)['dirname']);



        /**
         * Juntamos novamente a pasta e o nome do arquivo, colocando um ponto
         * no início. Por exemplo, /app/controllers mais Index.php formam
         * ./app/controllers/Index.php.
         */
        
        $path = '.'.$directory.'/'.pathinfo($file)['basename'];



        /**
         * Devolvemos o caminho preparado somente quando a exibição de detalhes
         * está habilitada. Caso contrário, este campo fica como null.
         */
        
        return Config::get('displayErrors', false) ? $path : null;
    }



    /**
     * 🟢 DECIDINDO SE O HISTÓRICO PODE APARECER
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * O histórico de chamadas, também chamado de trace, mostra o caminho
     * percorrido entre funções e métodos até o ponto em que foi coletado. Isso
     * pode ajudar a entender como chegamos a uma falha.
     *
     * Só devolvemos esse histórico quando displayErrors e showTrace estão
     * habilitados ao mesmo tempo. Assim, permitir a mensagem detalhada não
     * obriga a resposta a incluir toda a lista de chamadas.
     *
     * Se uma dessas configurações estiver desativada, devolvemos um array
     * vazio. O histórico recebido ainda pode ser guardado no log.
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
         * As duas condições precisam ser verdadeiras. showTrace permite o
         * histórico, enquanto displayErrors permite os detalhes da falha. Se
         * alguma configuração não estiver disponível, usamos false.
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
     * Reúne o trabalho que é comum aos diferentes caminhos de erro. Quando
     * chegamos aqui, já recebemos a mensagem, o código, a origem e o histórico
     * disponíveis para aquela falha.
     *
     * Primeiro montamos os dados da resposta, consultando os métodos que
     * decidem quais detalhes podem aparecer. Depois, ajustamos o status HTTP,
     * quando ainda é possível, e escolhemos como apresentar o resultado.
     *
     * A configuração output define essa apresentação: json envia JSON, file
     * gera uma imagem PNG e os demais valores usam a apresentação em HTML.
     *
     * Em seguida, gravamos o log se createLogsOnError permitir. Para esse
     * registro usamos os dados originais recebidos, e não as versões ocultadas
     * na resposta.
     *
     * Por fim, conferimos se a execução deve parar. Só os códigos listados
     * como avisos neste método podem passar sem exit, e apenas quando
     * stopOnWarnings está desativado. Nos demais casos, encerramos a execução.
     *
     * ℹ️ OBSERVAÇÃO
     * ┌───────────────────────────────────────────────────────────────────────┐
     * │ Este método não faz uma limpeza geral do buffer de saída. Um conteúdo │
     * │ produzido antes da falha pode aparecer junto da resposta de erro.     │
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
     * @param bool $displayError
     * :: Autoriza a exibição da mensagem original.
     *
     * @return void
     */
    
    private static function printError(int|string $code, string $message, string $file, int $line, array $trace, bool $displayError = false): void
    {
        /**
         * Guardamos a data e o horário desta passagem. Os mesmos valores serão
         * usados na resposta e no registro em log, para que ambos identifiquem
         * o mesmo atendimento da falha.
         */
        
        $currentDate = date('Y-m-d');
        $currentTime = date('H:i:s');



        /**
         * Montamos os campos que serão enviados na resposta. Arquivo, linha,
         * mensagem e histórico passam pelas verificações de exibição antes de
         * entrar nesse array.
         *
         * Os campos warning e error ficam como true nesta saída de falha. Eles
         * são indicadores da resposta, não a classificação usada para decidir
         * se a execução pode continuar.
         */
        
        $data = [
            'date' => $currentDate,
            'time' => $currentTime,
            'code' => $code,
            'origin' => self::getFile($file),
            'line' => self::getLine($line),
            'message' => self::getMessage($message, $displayError),
            'trace' => self::getTrace($trace),
            'warning' => true,
            'error' => true,
        ];



        /**
         * O status HTTP faz parte dos cabeçalhos da resposta. Só tentamos
         * ajustá-lo se eles ainda não foram enviados.
         *
         * Se um streaming já enviou conteúdo, por exemplo, o status anterior
         * permanece. A falha ainda pode ser comunicada no conteúdo da
         * resposta.
         */
        
        if (!headers_sent()) {
            http_response_code(self::getHttpStatusCode($code));
        }



        /**
         * Escolhemos a apresentação conforme output. Se a configuração ainda
         * não estiver disponível, usamos view, que mostra os campos como texto
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



        /**
         * Depois da apresentação, conferimos se devemos registrar a falha.
         * Usamos true como alternativa se createLogsOnError ainda não estiver
         * disponível.
         *
         * Passamos os parâmetros originais para o log. Assim, ocultar um
         * detalhe na resposta não elimina a informação que será usada para
         * investigar o problema.
         */
        
        if (Config::get('createLogsOnError', true)) {
            self::createLogFile($code, $message, $file, $line, $trace, $currentDate, $currentTime);
        }



        /**
         * Identificamos os quatro códigos que esta classe considera avisos
         * para a decisão de continuar. A comparação é estrita: o código
         * precisa corresponder a um dos nomes da lista.
         */
        
        $isWarning = in_array($code, ['E_WARNING', 'E_CORE_WARNING', 'E_COMPILE_WARNING', 'E_USER_WARNING'], true);



        /**
         * Se stopOnWarnings estiver habilitado, encerramos mesmo nos avisos.
         * Se estiver desabilitado, só os avisos da lista anterior podem seguir
         * sem exit.
         *
         * Um E_NOTICE, por exemplo, não está nessa lista e também encerra a
         * execução. Além disso, sair deste método não retoma um código que o
         * próprio PHP já interrompeu, como uma exceção não capturada.
         */
        
        if (Config::get('stopOnWarnings', true) || !$isWarning) {
            exit;
        }
    }



    /**
     * 🟢 ENVIANDO O ERRO EM JSON
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Envia os dados do erro em JSON. Aqui as decisões sobre ocultar mensagem,
     * arquivo, linha e histórico já foram tomadas por printError().
     *
     * Se a resposta já estiver em streaming, usamos Json::stream() para enviar
     * mais uma linha no mesmo formato das etapas anteriores. Isso permite que
     * quem está recebendo acompanhe também a falha, sem mudar o formato no
     * meio da resposta.
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
         * Se já estamos enviando etapas por streaming, acrescentamos o erro
         * como uma nova linha JSON. O return impede que os mesmos dados sejam
         * enviados novamente pelo caminho de JSON comum.
         */
        
        if (Json::isStreaming()) {
            Json::stream($data);
            return;
        }



        /**
         * Para uma resposta JSON comum, informamos o tipo do conteúdo quando
         * ainda podemos modificar os cabeçalhos.
         */
        
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }



        /**
         * Convertemos o array em JSON e enviamos o texto. JSON_PRETTY_PRINT
         * organiza os recuos; as opções UNESCAPED deixam barras e caracteres
         * Unicode sem escapes desnecessários.
         *
         * JSON_INVALID_UTF8_SUBSTITUTE substitui sequências inválidas em
         * UTF-8, permitindo representar o restante do texto.
         */
        
        echo json_encode(
            $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
        );
    }



    /**
     * 🟢 EXIBINDO O ERRO COMO TEXTO NO NAVEGADOR
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Apresenta os dados do erro como texto dentro de um bloco HTML.
     *
     * Cada campo ocupa sua própria linha. Quando o valor é um array, como
     * acontece com trace, usamos var_export() para transformá-lo em um texto
     * que mostre sua estrutura.
     *
     * O conteúdo passa por htmlspecialchars() antes de ser exibido. Se uma
     * mensagem contiver <b>Teste</b>, por exemplo, queremos enxergar essas
     * tags escritas, sem que elas alterem a apresentação da página.
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
         * A tag pre mantém os espaços e as quebras de linha. O estilo pre-wrap
         * também permite que linhas longas sejam quebradas para caber no
         * espaço disponível.
         */
        
        echo '<pre style="white-space: pre-wrap;">';



        /**
         * Percorremos os campos da resposta. Cada passagem prepara e exibe o
         * nome do campo junto de seu valor.
         */
        
        foreach ($data as $key => $value) {
            /**
             * Um array não pode ser juntado diretamente ao texto da linha.
             * var_export() devolve uma representação escrita de suas chaves e
             * valores.
             */
            
            if (Tools::typeOf($value) === 'array') {
                $value = var_export($value, true);
            }



            /**
             * str_pad() reserva pelo menos dez posições para o nome do campo,
             * alinhando os dois-pontos dos nomes mais curtos.
             *
             * Depois, htmlspecialchars() prepara a linha para aparecer como
             * texto no HTML. ENT_QUOTES inclui as aspas na conversão, e
             * ENT_SUBSTITUTE substitui trechos inválidos em UTF-8.
             */
            
            echo htmlspecialchars(str_pad($key, 10).': '.$value."\n", ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }



        /**
         * Fechamos o bloco depois de exibir todos os campos.
         */
        
        echo '</pre>';
    }



    /**
     * 🟢 DESENHANDO O ERRO EM UMA IMAGEM PNG
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Produz uma imagem PNG contendo os dados do erro. Essa apresentação é
     * usada quando output está definido como file.
     *
     * A imagem é criada em memória e seu conteúdo é enviado diretamente na
     * resposta.
     *
     * Transformamos os campos em linhas de texto, calculamos a altura
     * necessária e desenhamos esse conteúdo sobre um fundo escuro. Depois,
     * acrescentamos o logotipo no canto inferior direito.
     *
     * ℹ️ OBSERVAÇÃO
     * ┌───────────────────────────────────────────────────────────────────────┐
     * │ A criação da imagem depende da extensão GD com suporte à fonte        │
     * │ TrueType, além dos arquivos de fonte e logotipo definidos abaixo. O   │
     * │ código tenta carregar esses arquivos diretamente.                     │
     * └───────────────────────────────────────────────────────────────────────┘
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
         * Definimos as medidas da apresentação: largura da imagem, distância
         * entre as linhas, margem e tamanho reservado para o logotipo. Os
         * valores estão em pixels, exceto o tamanho da fonte usado adiante.
         */
        
        $width = 600;
        $lineHeight = 18;
        $margin = 15;
        $logoWidth = 40;
        $logoHeight = 40;



        /**
         * Definimos os arquivos usados para desenhar as letras e carregar o
         * logotipo. A fonte TrueType permite escrever o texto com
         * imagettftext().
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
         * Transformamos cada campo em texto. var_export() preserva uma
         * representação legível dos valores, inclusive arrays, null e
         * booleanos.
         *
         * wordwrap() procura pontos para quebrar o texto na largura informada.
         * Como não ativamos a quebra forçada de palavras, um trecho longo sem
         * espaços pode ultrapassar esse limite. explode() separa as quebras, e
         * array_merge() acrescenta as linhas à lista.
         */
        
        foreach ($data as $key => $value) {
            $text = str_pad($key, 10) . ': ' . var_export($value, true);
            $lines = array_merge($lines, explode("\n", wordwrap($text, 64)));



            /**
             * Depois dos campos line e message, incluímos uma linha vazia para
             * separar visualmente as partes do erro.
             */
            
            if (in_array($key, ['line', 'message'])) {
                $lines[] = '';
            }
        }



        /**
         * A altura recebe uma margem no topo, outra embaixo e o espaço
         * necessário para todas as linhas. Assim, mensagens com mais linhas
         * produzem uma imagem mais alta.
         */
        
        $height = $margin * 2 + count($lines) * $lineHeight;



        /**
         * Criamos a imagem em memória, reservamos as cores do fundo e do texto
         * e preenchemos toda a área com o fundo escuro.
         */
        
        $canvas = imagecreatetruecolor($width, $height);
        $bgColor = imagecolorallocate($canvas, 60, 61, 66);
        $textColor = imagecolorallocate($canvas, 255, 255, 255);
        imagefilledrectangle($canvas, 0, 0, $width, $height, $bgColor);



        /**
         * A primeira linha começa depois da margem superior, levando em conta
         * o tamanho da fonte. imagettftext() usa $y como a linha de base das
         * letras, e não como o topo delas.
         *
         * Depois de desenhar cada linha, aumentamos $y pela altura reservada
         * entre linhas. O ângulo zero mantém o texto na horizontal.
         */
        
        $y = $margin + $fontSize;
        foreach ($lines as $line) {
            imagettftext($canvas, $fontSize, 0, $margin, $y, $textColor, $fontPath, $line);
            $y += $lineHeight;
        }



        /**
         * Carregamos o logotipo e calculamos sua posição a partir do canto
         * inferior direito, mantendo a margem definida no início.
         *
         * imagecopyresampled() copia a imagem original para o tamanho
         * escolhido. Depois, liberamos o recurso do logotipo, pois sua cópia
         * já está no desenho principal.
         */
        
        $logoImage = imagecreatefrompng($logoPath);
        $xPos = $width - $logoWidth - $margin;
        $yPos = $height - $logoHeight - $margin;
        imagecopyresampled($canvas, $logoImage, $xPos, $yPos, 0, 0, $logoWidth, $logoHeight, imagesx($logoImage), imagesy($logoImage));
        imagedestroy($logoImage);



        /**
         * Informamos que a resposta contém um PNG e enviamos a imagem criada
         * em memória. Como imagepng() não recebe um caminho de destino, sua
         * saída vai para a resposta.
         *
         * Por fim, liberamos o recurso da imagem principal.
         */
        
        header('Content-type: image/png');
        imagepng($canvas);
        imagedestroy($canvas);
    }



    /**
     * 🟢 REGISTRANDO OS DETALHES EM LOG
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Guarda os detalhes da falha no arquivo de log correspondente à data
     * recebida. Assim, podemos consultar o que aconteceu mesmo quando a
     * resposta mostrou apenas uma mensagem genérica.
     *
     * Recebemos os dados originais de printError(), incluindo a mensagem, o
     * caminho do arquivo, a linha e o histórico. As configurações que ocultam
     * esses detalhes na resposta não retiram essas informações do registro.
     *
     * A data completa o caminho definido em PATH_LOG. Cada novo registro entra
     * no início do arquivo, deixando as falhas mais recentes antes das
     * anteriores.
     *
     * Solicitamos a criação da pasta com permissão 0700 e do arquivo com
     * permissão 0600. Em ambientes que usam permissões Unix, esses valores
     * reservam o acesso à pasta e a leitura e escrita do arquivo ao seu
     * proprietário. A criação da pasta também depende das permissões do
     * processo e da configuração do ambiente.
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
    
    private static function createLogFile(int|string $code, string $message, string $file, int $line, array $trace, string $currentDate, string $currentTime): void
    {
        /**
         * Garantimos que a pasta de logs exista, solicitando 0700 quando for
         * necessário criá-la. Tools::createDir() não altera as permissões de
         * uma pasta que já existe.
         */
        
        Tools::createDir(PROJECT_DIR.'/logs', 0700);



        /**
         * Montamos o caminho substituindo {{}} pela data recebida. O conteúdo
         * reúne data, horário, código, origem, linha, mensagem e histórico,
         * separados por quebras de linha.
         *
         * var_export() representa os valores em texto. Uma mensagem com aspas,
         * por exemplo, permanece identificável dentro do registro.
         *
         * INSERT_CONTENT_AT_START coloca o novo registro antes do conteúdo
         * existente. O valor 0600 define a permissão aplicada por Tools ao
         * criar o arquivo, sem alterar por aqui as permissões de um arquivo
         * que já existia.
         */
        
        Tools::writeFile(
            Tools::replacePlaceholders(self::PATH_LOG, [$currentDate]),
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
            INSERT_CONTENT_AT_START,
            0600
        );
    }



    /**
     * 🟢 ESCOLHENDO O STATUS HTTP DA FALHA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Escolhe o status HTTP que acompanha a resposta de erro. Esse número
     * informa a quem fez a requisição se o servidor respondeu com sucesso ou
     * encontrou um problema.
     *
     * Antes de escolher outro status, conferimos se já existe um código entre
     * 400 e 599. Se existir, mantemos esse valor para preservar uma decisão
     * que já foi tomada, como uma resposta 404.
     *
     * Quando ainda precisamos escolher, os códigos E_WARNING, E_USER_WARNING,
     * E_NOTICE e E_USER_NOTICE recebem 400. Os demais recebem 500.
     *
     * Essas regras usam a lista deste método. A lista de avisos que podem
     * continuar a execução fica em printError() e é uma verificação separada.
     *
     *
     * ---
     * @param int|string $code
     * :: Código que identifica a falha.
     *
     * @return int Status HTTP que deve acompanhar a resposta.
     */
    
    private static function getHttpStatusCode(int|string $code): int
    {
        /**
         * Consultamos o status que já está definido para esta resposta, sem
         * tentar modificá-lo nesta chamada.
         */
        
        $currentStatus = http_response_code();



        /**
         * Se já houver um status de erro entre 400 e 599, devolvemos esse
         * mesmo valor. Um status 404 definido antes da falha, por exemplo, não
         * será trocado por 500.
         */
        
        if ($currentStatus !== false && $currentStatus >= 400 && $currentStatus <= 599) {
            return $currentStatus;
        }



        /**
         * Quando ainda não há um status de erro a preservar, estes quatro
         * códigos recebem 400. Os nomes são comparados exatamente com as
         * entradas da lista.
         */
        
        if (in_array($code, ['E_WARNING', 'E_USER_WARNING', 'E_NOTICE', 'E_USER_NOTICE'], true)) {
            return 400;
        }



        /**
         * Para qualquer outro código, usamos 500 como status padrão da falha.
         */
        
        return 500;
    }
}

// -----------------------------------------------------------------------------
// ◀️ VOLTAR AO TÓPICO ANTERIOR:
// 🔗 LINK: /galastri/bootstrap/bootstrap.php
