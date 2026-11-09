<?php
/**
 * -----------------------------------------------------------------------------
 * 📂
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║           ARQUIVO /galastri/core/output/traits/BrowserCache.php           ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 * Nas classes de saída do framework `View`, `Json` e `File`, vimos que o
 * navegador pode aproveitar a cópia de um conteúdo que já existe em cache. É
 * aqui que o framework faz essa conferência.
 *
 * Este trait também cuida das respostas de rotas com `authTag`, orientando
 * o navegador a não guardar essas respostas em cache.
 * ─────────────────────────────────────────────────────────────────────────────
 */



/**
 * 🟢 NAMESPACE DO TRAIT
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * A trait `BrowserCache` é uma extensão dos formatos de saída do núcleo do
 * framework.
 */

namespace galastri\core\output\traits;



/**
 * 🟢 CLASSES UTILIZADAS
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * ┌─────────────┬─────────────────────────────────────────────────────────────┐
 * │ Classe      │ Uso                                                         │
 * ├─────────────┼─────────────────────────────────────────────────────────────┤
 * │ `Config`    │ Fornece os valores de `authTag` e `browserCache`.           │
 * ├─────────────┼─────────────────────────────────────────────────────────────┤
 * │ `Exception` │ Sinaliza valores inválidos na configuração `browserCache`.  │
 * ├─────────────┼─────────────────────────────────────────────────────────────┤
 * │ `Message`   │ Fornece as mensagens usadas nessas exceções.                │
 * └─────────────┴─────────────────────────────────────────────────────────────┘
 */

use galastri\core\config\Config;
use galastri\extensions\Exception;
use galastri\language\Message;



/**
 * 🟢 TRAIT BrowserCache
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * A trait BrowserCache é usada por `File`, `View` e `Json` pra controlar o
 * cache das respostas HTTP que essas classes respondem.
 *
 * Imagine que você volte a uma página de produtos que o navegador guardou em
 * cache. Pra saber se ainda pode usar aquela cópia, ele pode mandar ao servidor
 * o identificador do HTML que guardou. Com `browserCache` configurado, `View`
 * usa `checkBrowserCache()` pra comparar esse identificador com o do HTML que
 * acabou de montar.
 *
 * Se os identificadores baterem, o framework responde com `304` e o navegador
 * pode exibir a cópia que já tem. `File` faz essa mesma conferência antes de
 * enviar um arquivo pra exibição, como `manual.pdf`.
 *
 * Em rotas com `authTag`, as três saídas usam
 *  `applyAuthenticationCachePolicy()` pra orientar o navegador a não guardar a
 *  resposta em cache, seja ela uma página, um arquivo ou dados em JSON.
 */

trait BrowserCache
{
    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                              CONSTANTES                               ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     *
     * Esta trait não possui constantes.
     */
    
    
    
    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                             PROPRIEDADES                              ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     *
     * Esta trait não possui propriedades.
     */
    
    
    
    /**    
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                                MÉTODOS                                ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     */
    
    /**
     * 🟢 CACHE EM ROTAS COM AUTENTICAÇÃO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Quando a rota tem `authTag`, este método orienta o navegador a não
     * guardar em cache a resposta entregue após a autenticação.
     *
     * _________________________________
     * @return bool  
     *    Retorna `true` se a rota tem `authTag`, mesmo quando os cabeçalhos já
     *    foram enviados. Se `authTag` estiver vazia, retorna `false`.
     */
    
    private static function applyAuthenticationCachePolicy(): bool
    {
        /**
         * Se `authTag` estiver vazia, saímos sem mexer nos cabeçalhos.
         * Quando a chamada vem de `checkBrowserCache()`, esse `false`
         * permite que ele prossiga com a conferência do cache.
         */
        
        if (Config::get('authTag') === '') {
            return false;
        }

        
        
        /**
         * Porém, se `authTag` estiver definida, checamos se ainda pudermos
         * alterar os cabeçalhos, enviamos `no-store` em `Cache-Control` para
         * impedir que o conteúdo seja armazenado pelo cache do navegador.
         * Também retiramos os cabeçalhos que indicam até quando uma cópia vale
         * ou ajudam a identificar sua versão.
         */
        
        if (!headers_sent()) {
            header('Cache-Control: private, no-store', true);
            header_remove('Expires');
            header_remove('ETag');
            header_remove('Last-Modified');
        }

        return true;
    }

    
    
    /**
     * 🟢 CONFERINDO A CÓPIA GUARDADA PELO NAVEGADOR
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Quando o navegador pergunta se pode usar a cópia que guardou em cache,
     * este método confere se ela ainda corresponde ao que a saída enviaria
     * agora.
     *
     * _________________________________
     * @param string $currentContent  
     *    Dados usados pra gerar o `ETag` da resposta atual. `View` passa o HTML
     *    que montou. `File` passa o conteúdo e o nome do arquivo.
     * _________________________________
     * @return bool   
     *    Retorna `true` quando define o status `304`. Nesse caso, a saída é
     *    encerrada, sem enviar a resposta atual (que é a mesma do conteúdo em
     *    cache). Se retornar `false`, a saída segue com a resposta que estava
     *    preparando.
     */
    
    private static function checkBrowserCache(string $currentContent): bool
    {
        /**
         * Em rotas com `authTag`, a política de armazenamento é de não gravar
         * nada em cache. Antes de iniciarmos qualquer verificação de cache,
         * validamos aqui se a rota possui uma `authTag`. Se for o caso,
         * encerramos e não prosseguimos com a verificação.
         */
        
        if (self::applyAuthenticationCachePolicy()) {
            return false;
        }

        
        
        /**
         * Só fazemos essa conferência em requisições `GET` e `HEAD`. Se o
         * método não estiver disponível em `$_SERVER`, usamos `GET`.
         */
        
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if ($requestMethod !== 'GET' && $requestMethod !== 'HEAD') {
            return false;
        }

        
        
        /**
         * Se a resposta já tem um status definido, ele precisa ser `200` pra
         * seguir com a conferência. Assim, uma resposta de erro, por exemplo,
         * segue com o status que já tinha e não é armazenada em cache.
         */

        $status = http_response_code();

        if ($status !== false && $status !== 200) {
            return false;
        }

        
        
        /**
         * Com `browserCache` preenchido, lemos seus dados sendo a duração do
         * cache em segundos na chave `0` e a diretiva que vai acompanhar
         * `max-age`, na chave `1`.
         * 
         * Se a chave `0` não estiver definida, usamos `0`. Se a chave `1` não
         * estiver definida, usamos `public`.
         */
        
        $browserCache = Config::get('browserCache');

        if (is_array($browserCache) && !empty($browserCache)) {
            
            $cacheDuration = $browserCache[0] ?? 0;
            $cacheControl = $browserCache[1] ?? 'public';
            
            

            /**
             * A duração do cache precisa ser um inteiro maior ou igual a zero.
             * Verificamos isso para garantir que o preenchimento está correto e
             * lançamos uma exceção caso o valor seja inválido.
             */
            
            if (!is_int($cacheDuration) || $cacheDuration < 0) {
                throw new Exception(
                    Message::get('CACHE_INVALID_DURATION')
                );
            }

            
            
            /**
             * Fazemos o mesmo com a diretiva de cache, que precisa ser uma
             * string.
             */
            if (!is_string($cacheControl)) {
                throw new Exception(
                    Message::get('CACHE_INVALID_CACHE_CONTROL')
                );
            }

            
            
            /**
             * Geramos o `ETag` que identifica a versão atual do conteúdo e
             * informamos a duração do cache em `Cache-Control` e `Expires`.
             *
             * Como a conferência é feita pelo `ETag`, removemos `Last-Modified`
             * se ele já tiver sido definido.
             */
            
            $etag = md5($currentContent);
            $now = time();

            header('Cache-Control: ' . $cacheControl . ', max-age=' . $cacheDuration, true);
            header('Expires: ' . gmdate('D, d M Y H:i:s', $now + $cacheDuration) . ' GMT', true);
            header('ETag: "' . $etag . '"', true);

            header_remove('Last-Modified');
            
            
            
            /**
             * O navegador envia os identificadores que quer comparar com o
             * `ETag` atual em `If-None-Match`. Se o cabeçalho vier com `*`,
             * basta que exista uma versão do recurso pedido.
             */

            $ifNoneMatch = trim($_SERVER['HTTP_IF_NONE_MATCH'] ?? '');
            $matched = $ifNoneMatch === '*';

            if (!$matched) {
                
            
                /**
                 * O cabeçalho pode trazer mais de um `ETag`, separado por
                 * vírgulas. Lemos um por vez, aceitando também o prefixo `W/`,
                 * que indica um `ETag` fraco.
                 *
                 * Se encontrarmos um trecho inválido, descartamos a
                 * correspondência mesmo que um `ETag` anterior tenha batido.
                 */
                
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
            
            /**
             * Com uma correspondência, o status `304` diz ao navegador que ele
             * pode reaproveitar a cópia que já tem.
             */

            if ($matched) {
                http_response_code(304);
                return true;
            }
        }

        return false;
    }
}
