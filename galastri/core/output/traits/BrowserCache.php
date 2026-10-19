<?php
/**
 * Documentação:
 * https://github.com/andregalastri/galastri-framework-2/wiki/Trait-BrowserCache
 */

namespace galastri\core\output\traits;

use galastri\core\config\Config;
use galastri\extensions\Exception;
use galastri\language\Message;

trait BrowserCache
{
    private static function applyAuthenticationCachePolicy(): bool
    {
        if (Config::get('authTag') === '') {
            return false;
        }

        if (!headers_sent()) {
            header('Cache-Control: private, no-store', true);
            header_remove('Expires');
            header_remove('ETag');
            header_remove('Last-Modified');
        }

        return true;
    }

    private static function checkBrowserCache(string $currentContent): bool
    {
        if (self::applyAuthenticationCachePolicy()) {
            return false;
        }

        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if ($requestMethod !== 'GET' && $requestMethod !== 'HEAD') {
            return false;
        }

        $status = http_response_code();

        if ($status !== false && $status !== 200) {
            return false;
        }

        $browserCache = Config::get('browserCache');

        if (is_array($browserCache) && !empty($browserCache)) {
            $cacheDuration = $browserCache[0] ?? 0;
            $cacheControl = $browserCache[1] ?? 'public';

            if (!is_int($cacheDuration) || $cacheDuration < 0) {
                throw new Exception(
                    Message::get('CACHE_INVALID_DURATION')
                );
            }

            if (!is_string($cacheControl)) {
                throw new Exception(
                    Message::get('CACHE_INVALID_CACHE_CONTROL')
                );
            }

            $etag = md5($currentContent);
            $now = time();

            header('Cache-Control: ' . $cacheControl . ', max-age=' . $cacheDuration, true);
            header('Expires: ' . gmdate('D, d M Y H:i:s', $now + $cacheDuration) . ' GMT', true);
            header('ETag: "' . $etag . '"', true);

            header_remove('Last-Modified');

            $ifNoneMatch = trim($_SERVER['HTTP_IF_NONE_MATCH'] ?? '');
            $matched = $ifNoneMatch === '*';

            if (!$matched) {
                $remaining = $ifNoneMatch;

                while ($remaining !== '') {
                    $remaining = ltrim($remaining, " \t,");

                    if ($remaining === '') {
                        break;
                    }

                    if (preg_match('~\A(?:W/)?"([\x21\x23-\x7E\x80-\xFF]*)"~', $remaining, $match) !== 1) {
                        $matched = false;
                        break;
                    }

                    if ($match[1] === $etag) {
                        $matched = true;
                    }

                    $remaining = substr($remaining, strlen($match[0]));
                    $remaining = ltrim($remaining, " \t");

                    if ($remaining !== '' && $remaining[0] !== ',') {
                        $matched = false;
                        break;
                    }
                }
            }

            if ($matched) {
                http_response_code(304);
                return true;
            }
        }

        return false;
    }
}
