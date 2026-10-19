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

    public static function to(string $location, array $replacements = []): void
    {
        $location = Tools::replacePlaceholders($location, $replacements);

        if ($location === '') {
            $location = '/';
        }

        if (!preg_match(self::PROTOCOLS_REGEX, $location)) {
            $location = '/'.ltrim($location, '/');

            if (!self::$ignoreRootDir) {
                $location = rtrim(Router::getRootDir(), '/').$location;
            }
        }

        header('Location: '.$location, true, self::$statusCode);
        exit();
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
}
