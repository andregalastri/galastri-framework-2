<?php
/**
 * Documentação:
 * https://github.com/andregalastri/galastri-framework-2/wiki/Classe-ErrorHandler
 */

namespace galastri\core;

use galastri\core\config\Config;
use galastri\language\Message;
use galastri\modules\Tools;
use galastri\modules\Validation;

final class ErrorHandler
{
    const PATH_LOG = PROJECT_DIR.'/logs/ErrorHandler-%s.log';

    private function __construct() {}

    public static function run(): void
    {
        self::setErrorHandler();
        self::setExceptionHandler();
        self::registerShutdownFunction();
    }

    private static function setErrorHandler(): void
    {
        set_error_handler(
            function (int $level, string $message, string $file, int $line): void
            {
                self::printError(self::getErrorCode($level, 'E_WARNING'), $message, $file, $line, debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS));
            }
        );
    }

    private static function setExceptionHandler(): void
    {
        set_exception_handler(
            function (object $e): void
            {
                $trace = [];

                foreach ($e->getTrace() as $key => $value) {
                    if ($key === 'args') {
                        continue;
                    }

                    $trace[] = array_filter($value, function ($key){
                        return $key !== 'args';
                    }, ARRAY_FILTER_USE_KEY);
                }

                self::printError($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine(), $trace);
            }
        );
    }

    private static function registerShutdownFunction(): void
    {
        register_shutdown_function(
            function (): void
            {
                $error = error_get_last();

                try {
                    $code = self::getErrorCode($error['type'] ?? false);

                    if ($code) {
                        throw new \Exception();
                    }

                } catch (\Exception $e) {
                    self::printError($code, $error['message'], $error['file'], $error['line'], debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS));
                }
            }
        );
    }

    private static function getErrorCode(bool|int $errorLevel, mixed $default = false): mixed
    {
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

        return $errorMap[$errorLevel] ?? $default;
    }

    private static function getMessage(string $message): string
    {
        return Config::get('displayErrors', true) || Validation::$displayError ? $message : Message::get("DEFAULT_ERROR_MESSAGE")[0];
    }

    private static function getLine(int $line): int
    {
        return Config::get('displayErrors', true) ? $line : 0;
    }

    private static function getFile(string $file): ?string
    {
        $directory = str_replace(PROJECT_DIR, '', pathinfo($file)['dirname']);
        $path = '.'.$directory.'/'.pathinfo($file)['basename'];

        return Config::get('displayErrors', true) ? $path : null;
    }

    private static function getTrace(array $trace): array
    {
        if (Config::get('displayErrors', true) && Config::get('showTrace', false)) {
            return $trace;
        }

        return [];
    }

    private static function printError(int|string $code, string $message, string $file, int $line, array $trace): void
    {
        $currentDate = date('Y-m-d');
        $currentTime = date('H:i:s');

        $data = [
            'date' => $currentDate,
            'time' => $currentTime,
            'code' => $code,
            'origin' => self::getFile($file),
            'line' => self::getLine($line),
            'message' => self::getMessage($message),
            'trace' => self::getTrace($trace),
            'warning' => true,
            'error' => true,
        ];

        http_response_code(self::getHttpStatusCode($code));

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

        if (Config::get('createLogsOnError', true)) {
            self::createLogFile($code, $message, $file, $line, $trace, $currentDate, $currentTime);
        }

        if (Config::get('stopOnWarnings', true) || (Config::get('stopOnWarnings', true) && !in_array($code, ['E_WARNING', 'E_CORE_WARNING', 'E_COMPILE_WARNING', 'E_USER_WARNING']))) {
            exit;
        }
    }

    private static function printErrorJson(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }


    private static function printErrorView(array $data): void
    {
        echo '<pre style="white-space: pre-wrap;">';
        foreach ($data as $key => $value) {
            if (Tools::typeOf($value) === 'array') {
                $value = var_export($value, true);
            }

            echo str_pad($key, 10).': '.$value."\n";
        }
        echo '</pre>';
    }

    private static function printErrorFile(array $data): void
    {
        $width = 600;
        $lineHeight = 18;
        $margin = 15;
        $logoWidth = 40;
        $logoHeight = 40;
        
        $fontPath = PROJECT_DIR.'/galastri/misc/error-handler-font.ttf';
        $logoPath = PROJECT_DIR.'/galastri/misc/error-handler-logo.png';
        $fontSize = 11;
    
        $lines = [];
    
        // Quebrando o texto em múltiplas linhas
        foreach ($data as $key => $value) {
            $text = str_pad($key, 10) . ': ' . var_export($value, true);
            $lines = array_merge($lines, explode("\n", wordwrap($text, 64))); // Ajusta quebras de linha
    
            if (in_array($key, ['line', 'message'])) {
                $lines[] = '';
            }
        }
    
        // Calculando a altura dinâmica
        $height = $margin * 2 + count($lines) * $lineHeight;
    
        // Criando a imagem principal
        $canvas = imagecreatetruecolor($width, $height);
        $bgColor = imagecolorallocate($canvas, 60, 61, 66); // Cor de fundo
        $textColor = imagecolorallocate($canvas, 255, 255, 255); // Cor do texto
        imagefilledrectangle($canvas, 0, 0, $width, $height, $bgColor);
    
        // Desenhando o texto na imagem
        $y = $margin + $fontSize;
        foreach ($lines as $line) {
            imagettftext($canvas, $fontSize, 0, $margin, $y, $textColor, $fontPath, $line);
            $y += $lineHeight;
        }
    
        // Inserindo o logotipo na parte inferior direita, se o arquivo existir
        $logoImage = imagecreatefrompng($logoPath);
        $xPos = $width - $logoWidth - $margin;
        $yPos = $height - $logoHeight - $margin;
        imagecopyresampled($canvas, $logoImage, $xPos, $yPos, 0, 0, $logoWidth, $logoHeight, imagesx($logoImage), imagesy($logoImage));
        imagedestroy($logoImage);
    
        
        header('Content-type: image/png');
        imagepng($canvas);
        imagedestroy($canvas);
    }

    private static function createLogFile(int|string $code, string $message, string $file, int $line, array $trace, string $currentDate, string $currentTime): void
    {
        Tools::writeFile(
            Tools::flagReplace(self::PATH_LOG, [$currentDate]),
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
            INSERT_CONTENT_AT_START
        );
    }

    private static function getHttpStatusCode(int|string $code): int
    {
        return match ($code) {
            'E_WARNING', 'E_USER_WARNING', 'E_NOTICE', 'E_USER_NOTICE' => 400,
            default => 500,
        };
    }
}
