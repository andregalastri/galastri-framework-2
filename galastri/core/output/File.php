<?php
namespace galastri\core\output;

use galastri\core\Router;
use galastri\core\Galastri;
use galastri\core\config\Config;
use galastri\extensions\Exception;
use galastri\language\Message;
use galastri\modules\Tools;

final class File
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
     * 
     * Esta classe não possui constantes.
     */
    
    
    
    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                             PROPRIEDADES                              ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     */

    private static array $contents = [];
    private static string $folder = '';
    private static string $path = '';
    private static string $extension = '';
    private static string $mimeType = '';

    
    
    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                                MÉTODOS                                ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     */
    
    /**
     * 🟢 CLASSE ESTÁTICA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Os recursos desta classe são acessados diretamente por chamadas como
     * `File::run()`, sem precisar criar um objeto.
     *
     * Por isso, deixamos o construtor privado para impedir que a classe seja
     * instanciada com `new File()`.
     */
    
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
        $urlParts = implode('/', array_map('rawurldecode', Router::getUrlParts()));
        $folder = realpath(PROJECT_DIR.'/'.self::$folder);
        
        $isUrlPartsValid = $urlParts === '' || str_contains($urlParts, "\0");
        $isFolderValid = $folder === false || !is_dir($folder);

        if ($isUrlPartsValid || $isFolderValid) {
            Galastri::notFound();
        }

        $path = realpath($folder.'/'.$urlParts);
        $prefix = rtrim($folder, '/\\').DIRECTORY_SEPARATOR;
        
        $isPathValid = $path === false || !is_file($path);
        $hasPathInvalidPrefix = !str_starts_with($path, $prefix);

        if ($isPathValid || $hasPathInvalidPrefix) {
            Galastri::notFound();
        }

        self::$path = $path;
    }

    private static function checkExtension(): void
    {
        $fileInfo = pathinfo(self::$path);

        $extension = $fileInfo['extension'] ?? '';
        $allowedExtensions = Config::get('allowedFileExtensions');
        $isThisExtensionInvalid = !in_array($extension, $allowedExtensions);

        if ($allowedExtensions != [] and $isThisExtensionInvalid) {
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
        self::$contents = [
            file_get_contents(self::$path),
            pathinfo(self::$path)['basename'],
            self::$mimeType
        ];
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
        self::applyAuthenticationCachePolicy();

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        $fileName = mb_convert_encoding(self::$contents[1], 'UTF-8', 'UTF-8');
        $fileName = str_replace(['/', '\\'], '_', $fileName);
        $fileName = preg_replace('/[\x00-\x1F\x7F]/u', '_', $fileName);

        if ($fileName === '' || $fileName === '.' || $fileName === '..') {
            $fileName = 'download';
        }

        $fallbackName = preg_replace('/[^a-zA-Z0-9._ -]/u', '_', $fileName);
        header('Content-Disposition: attachment; filename="'.$fallbackName.'"; '
            .'filename*=UTF-8\'\''.rawurlencode($fileName));
            
        header('Expires: 0');
        header('Content-Length: '.mb_strlen(self::$contents[0], '8bit'));
        flush();
        ob_start();
        @print(self::$contents[0]);
        ob_end_flush();
        flush();
    }

    
    
    /**
     * 🟢 EXIGÊNCIA DE CONTROLLER
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Cada classe de saída informa, por esse método, se precisa de um
     * controller. `Galastri` consulta a saída escolhida antes de executá-la.
     *
     * Para `File`, esse retorno faz `Galastri` prosseguir no processamento,
     * mesmo se uma rota for inválida ou um controller não for encontrado.
     * 
     * A classe `File`, portanto, lida com esses casos à sua própria maneira.
     *
     * 
     * _________________________________
     * @return bool  
     *    Retorna sempre `false`, pois a saída `File` não exige um controller.
     */
    public static function requiresController(): bool
    {
        return false;
    }
}
