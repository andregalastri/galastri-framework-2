<?php
/**
 * Documentação:
 * https://github.com/andregalastri/galastri-framework-2/wiki/Classe-File
 */

namespace galastri\core\output;

use galastri\core\Router;
use galastri\core\Galastri;
use galastri\core\config\Config;
use galastri\extensions\Exception;
use galastri\language\Message;
use galastri\modules\Tools;

final class File
{
    use traits\BrowserCache;

    private static array $contents = [];
    private static string $folder = '';
    private static string $path = '';
    private static string $extension = '';
    private static string $mimeType = '';

    private function __construct() {}

    public static function run(): void
    {
        self::getControllerFileContents();

        if (self::$contents == []) {
            self::checkFolderExists();
            self::checkFileExists();
            self::checkExtension();
            self::checkMimeType();
            self::setFileContents();
        }

        if (Config::get('isDownloadableFile')) {
            self::download();
        } else {
            self::render();
        }
    }

    private static function getControllerFileContents(): void
    {
        if (Galastri::controllerExists()) {
            self::$contents = Galastri::getControllerFileContents();
        }
    }

    private static function checkFolderExists(): void
    {
        $folder = ltrim(Config::get('fileFolder'), '/');

        if ($folder == '') {
            throw new Exception(
                Message::get('OUTPUT_UNDEFINED_FILE_FOLDER')
            );
        }

        self::$folder = $folder;
    }

    private static function checkFileExists(): void
    {
        $urlParts = implode('/', Router::getUrlParts());
        $path = PROJECT_DIR.'/'.self::$folder.'/'.$urlParts;

        if ($urlParts == '' || !file_exists($path)) {
            Galastri::notFound();
        }

        self::$path = $path;
    }

    private static function checkExtension(): void
    {
        $fileInfo = pathinfo(self::$path);

        $extension = $fileInfo['extension'] ?? '';
        $allowedExtensions = Config::get('allowedFileExtensions');

        if ($allowedExtensions != [] and !in_array($extension, $allowedExtensions)) {
            throw new Exception(
                Message::get('OUTPUT_INVALID_FILE_EXTENSION'), [
                    $extension,
                    Tools::readableImplode(', ', ' ou ', $allowedExtensions)
                ]
            );
        }

        self::$extension = $extension;
    }
    
    private static function checkMimeType(): void
    {
        $extension = self::$extension;
        $forceMimeType = Config::get('forceMimeType');

        if (!empty($forceMimeType) && isset($forceMimeType[$extension])) {
            $mimeType = $forceMimeType[$extension];
        } else {
            $mimeType = mime_content_type(self::$path);
        }
        
        if (Config::get('validateMimeType')) {
            if (!isset(MIME_TYPE_LIST[$extension])) {
                throw new Exception(
                    Message::get('OUTPUT_UNDEFINED_FILE_MIME_EXTENSION'), [
                        $extension
                    ]
                );
            }

            
            if (!in_array($mimeType, MIME_TYPE_LIST[$extension])) {
                throw new Exception(
                    Message::get('OUTPUT_UNLISTED_FILE_MIME_TYPE'), [
                        $mimeType,
                        $extension,
                    ]
                );
            }
        }

        self::$mimeType = $mimeType;
    }

    private static function setFileContents(): void
    {
        self::$contents = [file_get_contents(self::$path), pathinfo(self::$path)['basename'], self::$mimeType];
    }

    private static function render(): void
    {
        if (self::checkBrowserCache((string) self::$contents[0].self::$contents[1])) {
            return;
        }

        header('Content-type: '.self::$contents[2]);
        @print(self::$contents[0]);
    }

    private static function download(): void
    {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.self::$contents[1].'"');
        header('Expires: 0');
        header('Content-Length: '.mb_strlen(self::$contents[0], '8bit'));
        flush();
        ob_start();
        @print(self::$contents[0]);
        ob_end_flush();
        flush();
    }


    public static function requiresController(): bool
    {
        return false;
    }
}
