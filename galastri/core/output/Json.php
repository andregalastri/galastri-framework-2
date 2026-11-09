<?php
/**
 * -----------------------------------------------------------------------------
 * 📂
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                  ARQUIVO /galastri/core/output/Json.php                   ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 * Quando a configuração `output` é `json`, esta classe converte o array com os
 * dados reunidos pelo controller em JSON e envia o resultado pra quem fez a
 * requisição.
 *
 * A aplicação também pode usar esta classe para enviar dados antes de terminar
 * o processamento. Nesse caso, a resposta usa NDJSON. Cada envio ocupa uma
 * linha com seu próprio JSON, e os dados reunidos pelo controller são enviados
 * na última linha.
 * ─────────────────────────────────────────────────────────────────────────────
 */



/**
 * 🟢 NAMESPACE DA CLASSE
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * A classe `Json` é um dos formatos de saída do núcleo do framework.
 */

namespace galastri\core\output;



/**
 * 🟢 CLASSES UTILIZADAS
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * ┌─────────────┬─────────────────────────────────────────────────────────────┐
 * │ Classe      │ Uso                                                         │
 * ├─────────────┼─────────────────────────────────────────────────────────────┤
 * │ `Galastri`  │ Fornece os dados que o controller reuniu para a resposta.   │
 * ├─────────────┼─────────────────────────────────────────────────────────────┤
 * │ `Exception` │ Usada se `stream()` não conseguir encerrar um buffer de     │
 * │             │ saída ao iniciar o streaming.                               │
 * ├─────────────┼─────────────────────────────────────────────────────────────┤
 * │ `Message`   │ Fornece as mensagens usadas nessas exceções.                │
 * └─────────────┴─────────────────────────────────────────────────────────────┘
 */

use galastri\core\Galastri;
use galastri\extensions\Exception;
use galastri\language\Message;



/**
 * 🟢 CLASSE Json
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * A classe `Json` cuida da resposta HTTP das rotas que usam `output => 'json'`.
 * Quando o controller termina, ela converte em JSON os dados que ele reuniu e
 * envia o resultado pra quem fez a requisição. Ela também permite enviar dados
 * enquanto o controller ainda está rodando.
 *
 * Imagine uma tela que mostra o andamento da importação de uma planilha. A cada
 * lote, o controller pode chamar `ResponseStream::send()` pra informar quantos
 * registros já foram importados. A classe `Json` envia cada atualização em uma
 * linha e, quando a importação termina, envia o resultado na última linha da
 * mesma resposta. Cada linha contém um JSON completo. Esse formato se chama
 * NDJSON.
 */

final class Json
{
    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                                TRAITS                                 ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     */
    
    /**
     * 🟢 CACHE DO NAVEGADOR
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * O trait `BrowserCache` fornece o método
     * `applyAuthenticationCachePolicy()`. Em `run()` e no primeiro envio de
     * `stream()`, chamamos esse método pra impedir que o navegador guarde em
     * cache a resposta de uma rota com `authTag` configurada.
     */
    
    use traits\BrowserCache;
    
    
    
    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                              CONSTANTES                               ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     */

    /**
     * 🟢 OPÇÕES DE CODIFICAÇÃO DO JSON
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * `run()` usa estas opções pra converter em JSON os dados que o controller
     * reuniu para a resposta.
     * 
     * `stream()` usa as mesmas opções pra converter os dados de cada envio
     * durante o processamento.
     *
     * - `JSON_UNESCAPED_SLASHES` mantém as barras `/` de URLs e caminhos.
     * - `JSON_UNESCAPED_UNICODE` mantém legíveis caracteres como `ç` e `ã`.
     * - `JSON_INVALID_UTF8_SUBSTITUTE` troca sequências inválidas de UTF-8 pelo
     *   caractere de substituição `�`.
     * - `JSON_THROW_ON_ERROR` faz `json_encode()` lançar uma `\JsonException`
     *   se a conversão falhar.
     * 
     * 
     * _________________________________
     * @var int
     */
    
    private const JSON_FLAGS = 
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE |
        JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR;
    
    
    
    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                             PROPRIEDADES                              ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     */
    
    /**
     * 🟢 ESTADO DO STREAMING
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Indica se a resposta está sendo enviada em NDJSON. Começa como `false` e
     * passa a `true` quando `stream()` termina de preparar o primeiro envio. As
     * próximas chamadas de `stream()` usam esse valor pra pular a preparação e
     * enviar os dados direto.
     *
     * Quando o controller termina, `run()` consulta esse valor pra saber se
     * deve enviar os dados finais do controller em mais uma linha do NDJSON.
     * 
     * 
     * _________________________________
     * @var bool
     */
    
    private static bool $streaming = false;
    
    
    
    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                                MÉTODOS                                ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     */
    
    /**
     * 🟢 CLASSE ESTÁTICA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Os recursos desta classe são acessados diretamente por chamadas como
     * `Json::run()`, sem precisar criar um objeto.
     *
     * Por isso, deixamos o construtor privado para impedir que a classe seja
     * instanciada com `new Json()`.
     */
    
    private function __construct() {}
    
    
    
    /**
     * 🟢 ENVIANDO A RESPOSTA FINAL
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este é o método que `Galastri` chama quando a saída da rota é `json`.
     * 
     * Quando o controller termina, o framework chama `run()` pra enviar os
     * dados que ele reuniu para a resposta. Buscamos o array com esses dados em
     * `Galastri::getControllerResponse()` e convertemos ele em JSON.
     *
     * Se os cabeçalhos ainda puderem ser alterados, informamos o formato da
     * resposta em `Content-Type` e, se a rota tiver `authTag` configurada,
     * impedimos que o navegador guarde a resposta em cache.
     *
     * Se um streaming já estiver ativo, esse JSON vai na última linha da
     * resposta NDJSON. Acrescentamos uma quebra de linha e chamamos `flush()`
     * pra liberar a saída do PHP. Sem streaming, o JSON é enviado como o corpo
     * inteiro da resposta.
     *
     * 
     * _________________________________
     * @return void
     */
    
    public static function run(): void
    {
        /**
         * Buscamos em `Galastri` o array com os dados que o controller reuniu
         * para a resposta e convertemos esse array em JSON.
         */
        
        $json = json_encode(Galastri::getControllerResponse(), self::JSON_FLAGS);
        
        
        
        /**
         * Se a rota tiver `authTag` configurada, o trait `BrowserCache`
         * impede que o navegador guarde a resposta em cache enquanto
         * ainda for possível alterar os cabeçalhos.
         */

        self::applyAuthenticationCachePolicy();
        
        
        
        /**
         * Se ainda pudermos alterar os cabeçalhos, usamos `Content-Type`
         * pra indicar o formato da resposta. Com o streaming ativo, usamos
         * NDJSON. Nos demais casos, usamos JSON.
         */
        
        if (!headers_sent()) {
            $contentType = self::$streaming
                ? 'application/x-ndjson; charset=utf-8'
                : 'application/json';
                
            header('Content-Type: '.$contentType);
        }
        
        echo $json;
        
        
        
        /**
         * Se o streaming estiver ativo, esse JSON acima ocupará a última linha
         * do NDJSON. Por isso, acrescentamos a quebra de linha que encerra essa
         * linha e chamamos `flush()` pra pedir ao PHP que libere a saída.
         */

        if (self::isStreaming()) {
            echo "\n";
            flush();
        }
    }
    
    
    
    /**
     * 🟢 ENVIANDO DADOS DURANTE O PROCESSAMENTO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Quando o controller chama `ResponseStream::send()`, é este método que
     * prepara e escreve os dados na resposta HTTP. Assim, quem fez a requisição
     * pode receber os dados enquanto o controller ainda estiver executando.
     *
     * Dá uma olhada em `ResponseStream.php` pra ver como os dados que o
     * controller passa pra `send()` chegam até aqui. Depois volte pra
     * acompanhar como `stream()` envia esses dados em NDJSON.
     *
     * 🔗 LINK: /galastri/modules/ResponseStream.php
     * 
     * 
     * _________________________________
     * @param array $data
     *    Dados que o controller quer enviar neste ponto do processamento.
     * _________________________________
     * @return void
     */
    
    public static function stream(array $data): void
    {
        /**
         * Convertemos os dados em JSON primeiro. Assim, se `json_encode()`
         * lançar uma exceção, isso acontece antes de mexermos nos cabeçalhos ou
         * nos buffers de saída.
         */

        $json = json_encode($data, self::JSON_FLAGS);
        
        if (!self::$streaming) {
            
        
        
            /**
             * No primeiro envio, aplicamos a política de cache das rotas
             * com `authTag`. Se ainda der pra alterar os cabeçalhos,
             * indicamos que a resposta usa NDJSON e pedimos ao Nginx,
             * via `X-Accel-Buffering`, que desative o buffer da resposta.
             */
            
            self::applyAuthenticationCachePolicy();

            if (!headers_sent()) {
                header('Content-Type: application/x-ndjson; charset=utf-8');
                header('X-Accel-Buffering: no');
            }

            
            
            /**
             * Os buffers de saída do PHP podem segurar as linhas que vamos
             * enviar. Por isso, encerramos um de cada vez, começando pelo mais
             * interno. `ob_end_flush()` libera o conteúdo de cada buffer antes
             * de encerrá-lo.
             *
             * Se algum buffer não puder ser removido ou se `ob_end_flush()`
             * falhar, interrompemos a preparação com uma exceção.
             */
            
            while (ob_get_level() > 0) {
                $buffer = ob_get_status();

                if (($buffer['flags'] & PHP_OUTPUT_HANDLER_REMOVABLE) === 0) {
                    throw new Exception(
                        Message::get('OUTPUT_STREAM_BUFFER_NOT_REMOVABLE')
                    );
                }

                if (!ob_end_flush()) {
                    throw new Exception(
                        Message::get('OUTPUT_STREAM_BUFFER_FLUSH_FAILED')
                    );
                }
            }
            
            /**
             * Com a primeira saída preparada, marcamos o streaming como ativo.
             * As próximas chamadas já podem seguir direto pro envio.
             */

            self::$streaming = true;
        }

        
        
        /**
         * Escrevemos o JSON seguido de uma quebra de linha, que marca o fim
         * deste envio no NDJSON. Em seguida, chamamos `flush()` pra pedir ao
         * PHP que libere a saída.
         */
        
        echo $json."\n";

        flush();
    }
    
    
    /**
     * 🟢 VERIFICANDO SE O STREAMING ESTÁ ATIVO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * @return bool  
     *    Retorna `true` se `stream()` já preparou o primeiro envio em NDJSON
     *    nesta requisição ou, caso contrário, retorna `false`.
     */
    
    public static function isStreaming(): bool
    {
        return self::$streaming;
    }

    
    /**
     * 🟢 EXIGÊNCIA DE CONTROLLER
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Cada classe de saída informa, por esse método, se precisa de um
     * controller. `Galastri` consulta a saída escolhida antes de executá-la.
     *
     * Para `Json`, esse retorno faz `Galastri` chamar `notFound()` se a
     * rota for inválida ou lançar uma exceção se a classe do controller
     * não for encontrada.
     *
     * 
     * _________________________________
     * @return bool  
     *    Retorna sempre `true`, pois a saída `Json` exige um controller.
     */
    
    public static function requiresController(): bool
    {
        return true;
    }
}


/**
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * ▶️ PRÓXIMO TÓPICO:
 * 🔗 LINK: /galastri/core/output/File.php
 */