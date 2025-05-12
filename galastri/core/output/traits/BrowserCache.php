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
    private static function checkBrowserCache(string $currentContent): bool
    {
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

            $ifNoneMatch = $_SERVER['HTTP_IF_NONE_MATCH'] ?? '';
            $clientEtag = trim(stripslashes($ifNoneMatch), '"');

            if ($clientEtag === $etag) {
                header('HTTP/1.1 304 Not Modified');
                return true;
            }
        }

        return false;
    }
}
