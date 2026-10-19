<?php
/**
 * Documentação:
 * https://github.com/andregalastri/galastri-framework-2/wiki/Classe-Json
 */

namespace galastri\core\output;

use galastri\core\Galastri;

final class Json
{
    use traits\BrowserCache;

    private const JSON_FLAGS = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR;
    
    private static bool $streaming = false;
    
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
