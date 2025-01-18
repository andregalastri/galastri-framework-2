<?php
/**
 * Documentação da classe:
 * https://github.com/andregalastri/galastri-framework-2/wiki/Classe-ErrorHandler
 */

namespace galastri\core;

use galastri\core\config\Config;
use galastri\language\Message;
use galastri\modules\Tools;

final class ErrorHandler
{

    const PATH_LOG = PROJECT_DIR.'/logs/ErrorHandler-%s.log';

    private static string $displayErrorsType = 'view';

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
            E_STRICT => 'E_STRICT',
        ];

        return $errorMap[$errorLevel] ?? $default;
    }

    public static function setDisplayErrorsType(string $type): void
    {
        if (in_array($type, ['json', 'view', 'file'])) {
            self::$displayErrorsType = $type;
        }
    }

    private static function getDisplayErrorsType(): string
    {
        return self::$displayErrorsType;
    }

    private static function getMessage(string $message): string
    {
        return Config::get('displayErrors', true) ? $message : Message::get("ERROR_MESSAGE");
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
        if (Config::get('displayErrors', true) and Config::get('showTrace', false)) {
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

        switch (self::getDisplayErrorsType()) {
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

        if (Config::get('stopOnWarnings', true) or (Config::get('stopOnWarnings', true) and !in_array($code, ['E_WARNING', 'E_CORE_WARNING', 'E_COMPILE_WARNING', 'E_USER_WARNING']))) {
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
        $lineHeight = 25;

        $canvas = imagecreate(600, 150);
        imagecolorallocate($canvas, 60, 61, 66);

        imagestring($canvas, 3, 10, 10, 'ERROR', imagecolorallocate($canvas, 218, 200, 182));

        foreach ($data as $key => $value) {
            imagestring($canvas, 3, 10, $lineHeight, (str_pad($key, 10).': '.var_export($value, true)), imagecolorallocate($canvas, 218, 200, 182));
            $lineHeight = $lineHeight + 15;
        }

        header('Content-type: image/png');
        imagepng($canvas);
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
}
