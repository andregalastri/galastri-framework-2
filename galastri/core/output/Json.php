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
 * └─────────────┴─────────────────────────────────────────────────────────────┘
 */

use galastri\core\Galastri;
use galastri\extensions\Exception;



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

    private const JSON_FLAGS = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR;
    
    
    
    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                             PROPRIEDADES                              ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     */
    
    private static bool $streaming = false;
    
    
    
    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                                MÉTODOS                                ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     */
    
    private function __construct() {}
    
    public static function run(): void
    {
        $json = json_encode(Galastri::getControllerResponse(), self::JSON_FLAGS);
        self::applyAuthenticationCachePolicy();
        
        if (!headers_sent()) {
            $contentType = self::$streaming ? 'application/x-ndjson; charset=utf-8' : 'application/json';
            header('Content-Type: '.$contentType);
        }
        
        echo $json;

        if (self::isStreaming()) {
            echo "\n";
            flush();
        }
    }
    
    public static function stream(array $data): void
    {
        $json = json_encode($data, self::JSON_FLAGS);
        
        if (!self::$streaming) {
            self::applyAuthenticationCachePolicy();

            if (!headers_sent()) {
                header('Content-Type: application/x-ndjson; charset=utf-8');
                header('X-Accel-Buffering: no');
            }

            while (ob_get_level() > 0) {
                $buffer = ob_get_status();

                if (($buffer['flags'] & PHP_OUTPUT_HANDLER_REMOVABLE) === 0) {
                    throw new Exception(
                        'Não foi possível iniciar o streaming: existe um buffer que não permite ser encerrado.'
                    );
                }

                if (!ob_end_flush()) {
                    throw new Exception(
                        'Não foi possível liberar e encerrar um buffer de saída.'
                    );
                }
            }

            self::$streaming = true;
        }

        echo $json."\n";

        flush();
    }
    
    public static function isStreaming(): bool
    {
        return self::$streaming;
    }

    public static function requiresController(): bool
    {
        return true;
    }
}
