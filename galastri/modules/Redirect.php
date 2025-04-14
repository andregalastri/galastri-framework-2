<?php
/**
 * Documentação:
 * https://github.com/andregalastri/galastri-framework-2/wiki/Classe-Redirect
 */

namespace galastri\modules;

use galastri\core\Router;
use galastri\extensions\Exception;
use galastri\language\Message;
use galastri\modules\Tools;

final class Redirect
{
    const PROTOCOLS_REGEX = '/^(https?|ftp|ftps|sftp|ssh|rdp|irc|ircs|file|urn|mailto|tel|data|ws|wss|ldap|ldaps|magnet):/i';
    const VALID_HTTP_REDIRECT_CODES = [300, 301, 302, 303, 307, 308];

    private static bool $ignoreRootDir = false;
    private static int $statusCode = 302;

    private function __construct() {}

    public static function to(string $location, string ...$printfData): void
    {
        $location = self::cleanLocation($location);

        if ($location == '') {
            $location = '/';
        }
        
        preg_match(self::PROTOCOLS_REGEX, $location, $match);

        if (empty($match)) {
            $location = '/'.$location;

            if (!self::$ignoreRootDir) {
                $location = '/'.self::cleanLocation(Router::getRootDir().$location);
            }
        }

        exit(header('Location: '.vsprintf($location, $printfData), true, self::$statusCode));
    }

    public static function statusCode(int $statusCode): string
    {
        if (!in_array($statusCode, self::VALID_HTTP_REDIRECT_CODES, true)) {
            throw new Exception(
                Message::get('REDIRECT_INVALID_STATUS_CODE'),
                [
                    $statusCode,
                    Tools::readableImplode(', ', ' e ', self::VALID_HTTP_REDIRECT_CODES),
                ]
            );
        }

        self::$statusCode = $statusCode;

        return __CLASS__;
    }

    public static function ignoreRootDir(): string
    {
        self::$ignoreRootDir = true;
        return __CLASS__;
    }

    private static function cleanLocation(string $string): string
    {
        return trim($string, '/?:;<>,.[]{}!@#$%&*()_+-=\\|');
    }
}
