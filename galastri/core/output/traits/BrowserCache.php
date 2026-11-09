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
     * Este método cuida do cache das respostas de `View` e `File`. Ele prepara
     * as informações que permitem ao navegador guardar uma cópia da página ou
     * do arquivo e conferir, em outro acesso, se pode continuar usando essa
     * cópia. Isso evita que o servidor envie os mesmos dados novamente quando o
     * navegador já tem uma versão válida.
     *
     * `View` chama este método depois de montar o HTML. `File` faz a chamada
     * depois de obter o conteúdo do arquivo. Portanto, quando chegamos aqui,
     * essas classes já prepararam o que enviariam ao navegador e aguardam o
     * resultado desta verificação pra decidir se precisam fazer esse envio.
     *
     * Com o cache configurado em `browserCache`, informamos ao navegador por
     * quanto tempo a cópia pode ser aproveitada e enviamos um código que
     * identifica a versão do conteúdo. Esse código é chamado de `ETag`.
     *
     * Quando o navegador precisa conferir a cópia que guardou, ele pode
     * devolver o `ETag` que recebeu. O método compara esse código com o do
     * conteúdo preparado para a requisição atual. Se forem iguais, define o
     * status HTTP `304`, avisando que aquela cópia foi reaproveitada, sem que a
     * saída então envie o conteúdo novamente.
     *
     * _________________________________
     * @param string $currentContent  
     *    String do conteúdo usado pra gerar o `ETag` da resposta. `View` passa
     *    o HTML que montou. `File` passa o conteúdo do arquivo junto com seu
     *    nome, pra que uma alteração no nome também seja considerada na
     *    conferência.
     * _________________________________
     * @return bool  
     *    Retorna `true` quando pode reaproveitar o conteúdo em cache. Quem
     *    chamou o método usa esse retorno pra encerrar a saída sem enviar o
     *    conteúdo. Com `false`, a saída prossegue com o envio da resposta
     *    normalmente.
     */
    private static function checkBrowserCache(string $currentContent): bool
    {
        /**
         * Primeiro, aplicamos a política de cache das rotas com autenticação.
         * Quando a configuração `authTag` está preenchida, o framework orienta
         * o navegador a não guardar a resposta em cache, desde que ainda seja
         * possível alterar os cabeçalhos.
         *
         * `applyAuthenticationCachePolicy()` retorna `true` ao encontrar
         * `authTag` preenchida. Nesse caso, saímos de `checkBrowserCache()` com
         * `false`, avisando a `View` ou `File` que deve enviar o conteúdo
         * preparado para esta requisição. Assim, mesmo que o navegador tenha
         * mandado o `ETag` de uma cópia antiga, encerramos este método antes da
         * comparação que poderia fazer a saída responder para aproveitar o
         * cache.
         *
         * Se `authTag` estiver vazia, a chamada retorna `false`. Neste caso,
         * seguimos com as próximas verificações de cache.
         */
        
        if (self::applyAuthenticationCachePolicy()) {
            return false;
        }

        
        
        /**
         * A partir daqui, fazemos uma série de testes pra decidir se devemos
         * reaproveitar o cache do navegador.
         */
        
        /**
         * 1. Verificar o tipo da requisição HTTP.
         *
         *    Consultamos esse tipo em `$_SERVER['REQUEST_METHOD']`. Se a chave
         *    estiver ausente ou guardar `null`, usamos `GET` como padrão.
         *
         *    Só prosseguimos se a requisição for `GET` e `HEAD`, pois o HTTP
         *    permite responder com `304` apenas nesses dois tipos de
         *    requisição. `GET` pede o conteúdo da página ou do arquivo. `HEAD`
         *    pede apenas os cabeçalhos, que trazem informações sobre esse
         *    conteúdo, sem receber a página ou o arquivo.
         *
         *    Se a requisição usar outro tipo, como `POST`, encerramos este
         *    método com `false` para que `View` ou `File` segue com o envio da
         *    resposta preparada para a requisição.
         */
        
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if ($requestMethod !== 'GET' && $requestMethod !== 'HEAD') {
            return false;
        }

        
        
        /**
         * 2. Conferir o status da resposta HTTP.
         *    
         *    Conferimos o status HTTP da resposta pra decidir se podemos
         *    continuar a verificação do cache. `http_response_code()` consulta
         *    esse status, cujo padrão em requisições web é `200` (OK).
         *
         *    Só retornamos `false` aqui se houver um código HTTP diferente de
         *    `200`. Nesse caso, a classe de saída segue enviando a resposta com
         *    o status que já tinha. Isso evita que um erro `404`, por exemplo,
         *    seja substituído por um `304`, que orientaria o navegador a usar a
         *    cópia em cache em vez de receber a resposta de erro.
         *
         *    Se o status for `200`, seguimos com as próximas verificações.
         *    Também seguimos se a função retornar `false`, indicando que não há
         *    um status definido, como pode acontecer numa execução do PHP pelo
         *    terminal.
         */
        
        $status = http_response_code();

        if ($status !== false && $status !== 200) {
            return false;
        }

        
        
        /**
         * 3. Checar se `browserCache` está definido.
         * 
         *    Testamos se a configuração de rota `browserCache` foi definida. Se
         *    não foi, ela será vazia e retornamos `false` e a classe de saída
         *    prossegue. O mesmo ocorre se ela for definida, mas vazia, dá no
         *    mesmo.
         */
        
        $browserCache = Config::get('browserCache');

        if (empty($browserCache)) {
            return false;
        }
        
        
        
        /**
         * 4. Checar se a configuração `browserCache` foi preenchida
         *    corretamente.
         * 
         *    Separamos os valores de `browserCache` que usaremos pra montar os
         *    cabeçalhos de cache da resposta.
         *
         *    - A chave `0` informa o prazo de validade da cópia em cache, em
         *      segundos.  
         *    - A chave `1` fornece as diretivas que vão acompanhar `max-age` no
         *      cabeçalho `Cache-Control`.
         *
         *    Quando uma dessas chaves não existe ou guarda `null`, usamos o
         *    padrão correspondente: `0` para a duração e `'public'` para as
         *    diretivas.
         */
            
        $cacheDuration = $browserCache[0] ?? 0;
        $cacheControl = $browserCache[1] ?? 'public';
        
        
        
        /**
         * Conferimos se a duração é um inteiro maior ou igual a zero, como
         * esperado para esse prazo em segundos.
         *
         * Se o valor for de outro tipo ou for negativo, interrompemos
         * o processamento com uma exceção. O valor `0` é aceito.
         */
        
        if (!is_int($cacheDuration) || $cacheDuration < 0) {
            throw new Exception(
                Message::get('CACHE_INVALID_DURATION')
            );
        }
        
        
        
        /**
         * Conferimos também se as diretivas de cache control estão numa string,
         * pois esse texto será incluído no cabeçalho `Cache-Control`.
         * 
         * Se o valor for de outro tipo, lançamos uma exceção.
         */
        
        if (!is_string($cacheControl)) {
            throw new Exception(
                Message::get('CACHE_INVALID_CACHE_CONTROL')
            );
        }
        
        
        
        /**
         * Com as estas verificações atendidas, seguimos para a preparação dos
         * cabeçalhos.
         */
        
        /**
         * Geramos um identificador para a versão atual da resposta, aplicando
         * `md5()` aos dados recebidos em `$currentContent`.
         *
         * Esse identificador será enviado no cabeçalho `ETag` da resposta. Numa
         * próxima requisição, o navegador poderá devolvê-lo para perguntar se a
         * cópia que guardou ainda corresponde à atual.
         */
        
        $etag = md5($currentContent);
        
        
        
        /**
         * Guardamos o instante atual para calcular o vencimento do
         * cache, somando a ele a duração configurada em segundos.
         */

        $now = time();
        
        
        
        /**
         * Definimos os cabeçalhos que orientam o navegador sobre o cache desta
         * resposta:
         *
         * - `Cache-Control`: reúne as diretivas de `$cacheControl` e acrescenta
         *   `max-age`, que informa o prazo de validade da cópia (em segundos).
         */
        
        header('Cache-Control: '.$cacheControl.', max-age='.$cacheDuration);

        
        
        /** 
         * - `Expires`: informa o vencimento do cache em data/hora GMT, contando
         *   a partir da data/hora atual. Fazemos isso somando a data/hora atual
         *   armazenada em `$now` com a duração do cache armazenado em
         *   `$cacheDuration`.
         * 
         *   💡 OBSERVAÇÃO
         *   ┌────────────────────────────────────────────────────────────────┐
         *   │ Quando o navegador consegue identificar o `max-age`, ele usará │
         *   │ este no lugar da data de `Expires`.                            │
         *   └────────────────────────────────────────────────────────────────┘
         */
        
        $gmtDateTime = gmdate('D, d M Y H:i:s', $now + $cacheDuration);
        header('Expires: '.$gmtDateTime.' GMT');

        
        
        /**
         * - `ETag`: envia o identificador desta saída que acabamos de gerar,
         *   entre aspas.
         */

        header('ETag: "'.$etag.'"');
        

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
        
        return false;
    }
}
