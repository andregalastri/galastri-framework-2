<?php
/**
 * -----------------------------------------------------------------------------
 * 📂
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                  ARQUIVO /galastri/core/output/File.php                   ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 * Quando a configuração `output` é `file`, esta classe envia um arquivo pra
 * quem fez a requisição. O controller pode preparar esse arquivo, mas se nenhum
 * arquivo for preparado, a classeainda pode usar o caminho pedido na URL pra
 * procurá-lo dentro da pasta configurada em `fileFolder`.
 * ─────────────────────────────────────────────────────────────────────────────
 */



/**
 * 🟢 NAMESPACE DA CLASSE
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * A classe `View` é um dos formatos de saída do núcleo do framework.
 */

namespace galastri\core\output;



/**
 * 🟢 CLASSES UTILIZADAS
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * ┌─────────────┬─────────────────────────────────────────────────────────────┐
 * │ Classe      │ Uso                                                         │
 * ├─────────────┼─────────────────────────────────────────────────────────────┤
 * │ `Router`    │ Fornece as partes da URL usadas pra montar o caminho do     │
 * │             │ arquivo na pasta configurada.                               │
 * ├─────────────┼─────────────────────────────────────────────────────────────┤
 * │ `Galastri`  │ Fornece o arquivo preparado pelo controller e trata os      │
 * │             │ casos em que a busca pelo arquivo da URL falha.             │
 * ├─────────────┼─────────────────────────────────────────────────────────────┤
 * │ `Config`    │ Fornece as configurações da saída `file`.                   │
 * ├─────────────┼─────────────────────────────────────────────────────────────┤
 * │ `Exception` │ Usada se `fileFolder` estiver vazio ou se a extensão ou o   │
 * │             │ tipo MIME do arquivo falhar na validação.                   │
 * ├─────────────┼─────────────────────────────────────────────────────────────┤
 * │ `Message`   │ Fornece as mensagens usadas nas exceções.                   │
 * ├─────────────┼─────────────────────────────────────────────────────────────┤
 * │ `Tools`     │ Monta a lista de extensões permitidas pra mostrar na        │
 * │             │ mensagem de erro.                                           │
 * └─────────────┴─────────────────────────────────────────────────────────────┘
 */

use galastri\core\Router;
use galastri\core\Galastri;
use galastri\core\config\Config;
use galastri\extensions\Exception;
use galastri\language\Message;
use galastri\modules\Tools;



/**
 * 🟢 CLASSE File
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * A classe `File` cuida da resposta HTTP das rotas que entregam arquivos.
 *
 * Pense numa aplicação que oferece manuais e relatórios em PDF. Os manuais
 * já ficam numa pasta do projeto. Quando alguém acessa a URL de um manual,
 * `File` procura o PDF nessa pasta e pode enviá-lo mesmo sem um controller.
 *
 * Já um relatório de vendas pode ser gerado na hora, com os dados do período
 * que a pessoa escolheu. Nesse caso, o controller prepara o PDF e fornece
 * à saída o conteúdo, o nome do arquivo e o tipo MIME (`application/pdf`).
 */

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
     * Em `render()`, usamos `checkBrowserCache()` pra saber se o navegador
     * pode reaproveitar a cópia do arquivo que já tem em cache. Se puder,
     * a resposta é `304` e `render()` termina sem enviar o conteúdo de novo.
     *
     * Quando a rota tem `authTag` configurada, o trait orienta o navegador
     * a não guardar a resposta em cache. Em `render()`, essa política é
     * aplicada dentro de `checkBrowserCache()`. Em `download()`, chamamos
     * `applyAuthenticationCachePolicy()` diretamente.
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

    /**
     * 🟢 DADOS DO ARQUIVO A ENVIAR
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Guarda as informações do arquivo preparado pelo controller ou lido do
     * disco. O array contém, nesta ordem, o conteúdo, o nome do arquivo e o
     * tipo MIME.
     *
     *
     * _________________________________
     * @var array
     */
    
    private static array $contents = [];
    
    
    
    /**
     * 🟢 PASTA DOS ARQUIVOS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Guarda o caminho definido em `fileFolder`. Esse caminho é usado a partir
     * da raiz do projeto pra encontrar a pasta onde procuramos o arquivo pedido
     * na URL.
     *
     *
     * _________________________________
     * @var string
     */
    
    private static string $folder = '';
    
    
    
    /**
     * 🟢 CAMINHO DO ARQUIVO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Guarda o caminho absoluto do arquivo encontrado no disco. É por esse
     * caminho que consultamos a extensão, identificamos o tipo MIME e lemos o
     * conteúdo do arquivo.
     *
     *
     * _________________________________
     * @var string
     */
    
    private static string $path = '';
    
    
    
    /**
     * 🟢 EXTENSÃO DO ARQUIVO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Guarda a extensão do arquivo encontrado no disco, sem o ponto. Por
     * exemplo, para `manual.pdf`, guardamos `pdf`. Se o arquivo não tiver
     * extensão, o valor é uma string vazia.
     *
     *
     * _________________________________
     * @var string
     */

    private static string $extension = '';
    
    
    
    /**
     * 🟢 TIPO MIME DO ARQUIVO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Guarda o tipo MIME do arquivo encontrado no disco, como
     * `application/pdf`. O valor vem de `forceMimeType` quando há um tipo
     * configurado para a extensão. Caso contrário, é detectado a partir do
     * próprio arquivo.
     *
     *
     * _________________________________
     * @var string
     */

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

    
    
    /**
     * 🟢 ENVIANDO O ARQUIVO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * `Galastri` chama este método na etapa de saída das rotas que usam
     * `output => 'file'`. A partir daqui, a classe `File` reúne os dados do
     * arquivo e cuida do envio na resposta HTTP.
     *
     *
     * _________________________________
     * @return void
     */
    
    public static function run(): void
    {
        /**
         * Se houver um controller, começamos verificando se ele preparou um
         * arquivo pra esta resposta.
         */
        
        self::getControllerFileContents();

        
        
        /**
         * Se o array continuar vazio, significa que, ou não há controller, ou o
         * controller não preparou nenhum arquivo e precisamos procurar no disco
         * o arquivo pedido na URL.
         * 
         * Antes de ler o conteúdo precisamos localizar o arquivo, identificar
         * seu tipo MIME e aplicar as regras configuradas pra extensão e tipo
         * MIME.
         */
        
        if (self::$contents == []) {
            self::checkFolderExists();
            self::checkFileExists();
            self::checkExtension();
            self::checkMimeType();
            self::setFileContents();
        }

        /**
         * Por fim, com base em `isDownloadableFile`, pedimos ao navegador que
         * baixe o arquivo, caso ele esteja ativo, ou, caso contrário, enviamos
         * o arquivo pra exibição.
         */
        
        if (Config::get('isDownloadableFile')) {
            self::download();
        } else {
            self::render();
        }
    }

    
    
    /**
     * 🟢 BUSCANDO O ARQUIVO PREPARADO PELO CONTROLLER
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Se houver um controller, pedimos a `Galastri` os dados do arquivo que
     * o controller preparou e guardamos o array em `$contents`.
     *
     * Se o controller não preparou um arquivo, `Galastri` fornece um array
     * vazio. É neste caso que `run()` sabe que precisa procurar o arquivo no
     * disco.
     *
     *
     * _________________________________
     * @return void
     */
    private static function getControllerFileContents(): void
    {
        if (Galastri::controllerExists()) {
            self::$contents = Galastri::getControllerFileContents();
        }
    }

    
    
    /**
     * 🟢 CONFERINDO A CONFIGURAÇÃO DA PASTA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Prepara o caminho da pasta onde a saída `File` procura os arquivos
     * pedidos pela URL.
     *
     *
     * _________________________________
     * @return void
     */
    private static function checkFolderExists(): void
    {
        /**
         * Retiramos as barras `/` do início de `fileFolder`. Assim, tanto
         * `/arquivos` quanto `arquivos` viram o mesmo caminho.
         */
        
        $folder = ltrim(Config::get('fileFolder'), '/');

        
        
        /**
         * Se o valor ficar vazio, lançamos uma exceção indicando que a pasta
         * precisa ser configurada.
         */

        if ($folder == '') {
            throw new Exception(
                Message::get('OUTPUT_UNDEFINED_FILE_FOLDER')
            );
        }

        
        
        /**
         * Guardamos o caminho pra que `checkFileExists()` possa localizar a
         * pasta a partir da raiz do projeto e conferir se ela existe.
         */
        self::$folder = $folder;
    }

    
    
    /**
     * 🟢 LOCALIZANDO O ARQUIVO NO DISCO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Localiza o arquivo pedido na URL e guarda seu caminho depois de confirmar
     * que ele está dentro da pasta configurada em `fileFolder`.
     *
     *
     * _________________________________
     * @return void
     */

    private static function checkFileExists(): void
    {
        /**
         * Decodificamos as partes da URL que o roteador deixou pra saída e
         * juntamos essas partes com `/`. Assim, `guias/meu%20manual.pdf` vira
         * `guias/meu manual.pdf`.
         */
        
        $urlParts = implode('/', array_map('rawurldecode', Router::getUrlParts()));
        
        
        
        /**
         * Resolvemos o caminho da pasta configurada a partir da raiz do
         * projeto.
         */
        
        $folder = realpath(PROJECT_DIR.'/'.self::$folder);
        
        
        
        /**
         * Chamamos `Galastri::notFound()` se o caminho vindo da URL estiver
         * vazio ou contiver um byte nulo, ou se a pasta não existir no disco.
         */
        
        $isUrlPartsValid = $urlParts === '' || str_contains($urlParts, "\0");
        $isFolderValid = $folder === false || !is_dir($folder);

        if ($isUrlPartsValid || $isFolderValid) {
            Galastri::notFound();
        }

        
        
        /**
         * Em `$path`, guardamos o caminho resolvido do arquivo pedido na URL.
         * `realpath()` resolve trechos como `..` e segue links simbólicos pra
         * descobrir onde o caminho aponta.
         */
        
        $path = realpath($folder.'/'.$urlParts);
        
        
        
        /**
         * Em `$prefix`, colocamos o caminho da pasta seguido de `/`. A barra
         * final faz o teste exigir que o caminho do arquivo comece com
         * `/projeto/arquivos/`.
         * 
         * Sem ela, o teste também aceitaria `/projeto/arquivos2/manual.pdf`,
         * porque esse caminho começa com `/projeto/arquivos`.
         */
        
        $prefix = $folder.'/';
        
        
        
        /**
         * O caminho resolvido precisa apontar pra um arquivo e começar com o
         * prefixo da pasta configurada. Se qualquer uma dessas condições
         * falhar, chamamos `Galastri::notFound()`.
         */
        
        $isPathValid = $path === false || !is_file($path);
        $hasPathInvalidPrefix = !str_starts_with($path, $prefix);

        if ($isPathValid || $hasPathInvalidPrefix) {
            Galastri::notFound();
        }
        
        

        /**
         * Guardamos o caminho pra consultar a extensão, identificar o tipo
         * MIME e ler o conteúdo nas próximas etapas.
         */
        
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
