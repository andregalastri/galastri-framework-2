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
         * Com as estas primeiras verificações atendidas, seguimos para a
         * preparação dos cabeçalhos.
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
        

        /**
         * Voltamos ao restante das verificações.
         */
        
        /**
         * Checamos se o cabeçalho `If-None-Match` foi recebido. Se não foi,
         * retornamos `false` e deixamos a saída responder com o conteúdo
         * normalmente.
         * 
         * O cabeçalho `If-None-Match` é onde ficam armazenadas as ETags que
         * identificam nosso cache. Precisamos dele pra comparar se a ETag do
         * arquivo atual corresponde à ETag que o navegador possui, decidindo se
         * usaremos o arquivo em cache ou não.
         * 
         * Começamos tentanr armazenar o cabeçalho `If-None-Match`, mas já aí
         * temos uma condição: nem sempre ele é enviado pelo navegador.
         * 
         * Alguns motivos pra isso são:
         * 
         * 1. Quando é a primeira requisição e o navegador ainda não possui um
         *    cache com ETag, então não há cabeçalho `If-None-Match` para
         *    enviar.
         * 
         * 2. Quando o cache pode ser utilizado diretamente por estar dentro da
         *    validade, então a requisição não é nem enviada, o próprio
         *    navegador puxa o que precisa do cache.
         * 
         *    ⚠️ IMPORTANTE
         *    ┌────────────────────────────────────────────────────────────────┐
         *    │ Se a aplicação necessitar que o navegador sempre consulte se o │
         *    │ cache ainda é válido, é sugerido configurar `browserCache` com │
         *    │ [0, 'no-cache'].                                               │
         *    │                                                                │
         *    │ Não confunda `no-cache` com `no-store`. Apesar do termo        │
         *    │ 'no-cache' parecer indicar não guardar cache, ela apenas       │
         *    │ define que o cache precisa ser sempre revalidado no servidor.  │
         *    └────────────────────────────────────────────────────────────────┘
         * 
         * Se `If-None-Match` não estiver disponível, usamos uma string vazia.
         */
        
        $ifNoneMatch = trim($_SERVER['HTTP_IF_NONE_MATCH'] ?? '');

        
        
        /**
         * Se `If-None-Match` for uma string vazia, retornamos `false` para que
         * a classe de saída prossiga com o envio da resposta atual. Não há
         * cache pra ser conferido, neste caso.
         */
        
        if ($ifNoneMatch === '') {
            return false;
        }
        
        
        
        /**
         * Aqui temos uma questão que pode confundir.
         * 
         * Este bloco `if` só vai executar as verificações se `If-None-Match`
         * não for igual a `*`.
         * 
         * O protocolo HTTP, seguindo a RFC 9110, seção 13.1.2, permite que o
         * client (como o navegador) envie `If-None-Match` de duas formas:
         * 
         * 1. Uma lista de identificadores entre aspas.
         * 2. O caractere `*`.
         * 
         * Segundo a regra do protocolo, em requisições GET e HEAD que seriam
         * atendidas com status `200`, devemos responder com status `304` ao
         * receber `*` em `If-None-Match`, sem comparar ETags.
         * 
         * O `if` abaixo, portanto, tem o objetivo APENAS de cobrir uma
         * possibilidade do protocolo HTTP e garantir que a resposta da
         * requisição esteja de acordo com o esperado. Se `If-None-Match` for
         * `*` numa requisição GET ou HEAD, nenhum teste ou comparação deste
         * bloco precisa ser feito. Devemos apenas seguir para a definição do
         * status `304`.
         * 
         * Tratamos esse caso APENAS para entregar a resposta prevista pelo
         * protocolo ao client que enviou o asterisco.
         *
         * Por isso, só executamos a validação e a comparação dos
         * identificadores quando o valor recebido é diferente de `*`. Com `*`,
         * pulamos esse bloco e seguimos diretamente para o código que define o
         * status `304` abaixo.
         */
        
        if ($ifNoneMatch !== '*') {
            
        
        
            /**
             * Definimos o formato de cada ETag que podemos encontrar no
             * cabeçalho `If-None-Match`, enviado pelo client.
             *
             * Cada identificador deve estar entre aspas e pode ter o prefixo
             * `W/`, por exemplo `"abc123"` ou `W/"abc123"`.
             *
             * O prefixo `W/` indica uma ETag fraca: ela identifica conteúdos
             * que o servidor considera equivalentes, sem garantir que sejam
             * idênticos byte por byte. O protocolo HTTP exige que a comparação
             * feita para `If-None-Match` desconsidere esse prefixo.
             *
             * Por isso, no padrão abaixo, usamos `(?:W/)?` pra permitir a
             * correspondencia, mas sem capturar o W/.
             * 
             * Já o que está dentro das aspas são os identificadores que vamos
             * comparar e, por isso, precisam ser capturados. Por isso
             * envolvemos tudo o que vamos capturar entre parênteses.
             *
             * Os códigos `\x21`, `\x23-\x7E` e `\x80-\xFF` representam os bytes
             * permitidos pelo HTTP dentro das aspas. O `*` permite zero ou mais
             * desses bytes, pois uma ETag também pode ter um identificador
             * vazio.
             */
            
            $etagPattern = '(?:W/)?"([\x21\x23-\x7E\x80-\xFF]*)"';
            
            
            
            /**
             * Montamos outro padrão para conferir o cabeçalho inteiro. Ele deve
             * conter pelo menos uma ETag, podendo trazer outras separadas por
             * vírgulas.
             *
             * `\A` e `\z` marcam o início e o fim da string. Assim, não basta
             * encontrar uma ETag válida no meio do texto: todo o valor recebido
             * precisa respeitar o formato esperado.
             *
             * `[ \t]` aceita espaços e tabulações fora dos identificadores. As
             * vírgulas adicionais aceitas por `[ \t,]*` permitem ignorar itens
             * vazios da lista, conforme previsto pelo protocolo.
             *
             * Uma vírgula dentro das aspas pertence ao identificador. Por isso,
             * não podemos simplesmente dividir o cabeçalho a cada vírgula.
             */
            
            $listPattern = 
                '~\A[ \t,]*'.$etagPattern
                .'(?:[ \t]*,[ \t,]*'.$etagPattern.')*[ \t,]*\z~';
            
            
                
            /**
             * Conferimos se o valor armazenado em `$ifNoneMatch` corresponde ao
             * formato completo da lista.
             *
             * `preg_match()` retorna `1` quando o formato corresponde, `0`
             * quando não corresponde e `false` se ocorrer um erro na execução.
             *
             * Só prosseguimos quando o resultado for `1`. Nos demais casos,
             * retornamos `false`, encerrando esta verificação sem definir o
             * status `304`. A classe de saída seguirá com o envio do conteúdo.
             */
            
            if (preg_match($listPattern, $ifNoneMatch) !== 1) {
                return false;
            }
            
            
            
            /**
             * Agora que sabemos que a lista tem um formato válido, extraímos
             * seus identificadores para fazer a comparação.
             *
             * `preg_match_all()` procura todas as ETags e coloca os resultados
             * em `$matches`. A posição `$matches[0]` contém as ETags completas,
             * enquanto `$matches[1]` contém só os valores capturados dentro das
             * aspas, sem as aspas e sem o prefixo `W/`.
             *
             * Por exemplo, para `"abc123", W/"def456"`, teremos em
             * `$matches[1]` os valores `abc123` e `def456`. São esses valores
             * que precisamos comparar com a ETag calculada pelo servidor.
             *
             * `$matchCount` recebe a quantidade de ETags encontradas ou `false`
             * se ocorrer um erro na execução da expressão.
             */
            
            $matchCount = preg_match_all(
                '~'.$etagPattern.'~',
                $ifNoneMatch,
                $matches
            );
            
            
            
            /**
             * Se ocorrer um erro durante a extração, não teremos os
             * identificadores necessários para conferir a ETag. Nesse caso,
             * retornamos `false` para que a classe de saída siga com o envio do
             * conteúdo.
             */
            
            if ($matchCount === false) {
                return false;
            }
            
            
            
            /**
             * Estando tudo certo, porém, procuramos o valor de `$etag` entre os
             * identificadores recebidos, calculado anteriormente com
             * `md5($currentContent)` a partir do conteúdo que o servidor vai
             * responder.
             *
             * O terceiro argumento de `in_array()`, definido como `true`, exige
             * que o valor e o tipo sejam iguais, sem conversões durante a
             * comparação.
             *
             * Se nenhum identificador corresponder, retornamos `false` para
             * que o conteúdo seja enviado ao client.
             *
             * Se encontrarmos uma correspondência, passamos por esta condição
             * sem retornar.
             */
            
            if (!in_array($etag, $matches[1], true)) {
                return false;
            }
        }
        
        
        
        /**
         * Passando por todas as condições sem retornar, a execução seguirá para
         * o código que define o status `304`, indicando que a cópia em cache
         * correspondente àquela ETag pode ser reutilizada sem receber o
         * conteúdo novamente.
         */
        
        http_response_code(304);
        return true;
    }
}



/**
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * ◀️ VOLTAR AO TÓPICO ANTERIOR:
 * 🔗 LINK: /galastri/core/output/File.php
 */