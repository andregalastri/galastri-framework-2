<?php
/**
 * -----------------------------------------------------------------------------
 * 📂
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║               ARQUIVO /galastri/modules/Authentication.php                ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 * Este arquivo define a classe que usamos pra registrar, verificar e encerrar
 * as autenticações da aplicação.
 * ─────────────────────────────────────────────────────────────────────────────
 */



/**
 * 🟢 NAMESPACE DA CLASSE
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * A classe `Authentication` faz parte dos módulos do framework.
 */

namespace galastri\modules;



/**
 * 🟢 CLASSES UTILIZADAS
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * ┌─────────────┬─────────────────────────────────────────────────────────────┐
 * │ Classe      │ Uso                                                         │
 * ├─────────────┼─────────────────────────────────────────────────────────────┤
 * │ `Exception` │ Usamos pra interromper uma operação quando encontramos um   │
 * │             │ problema ao registrar ou alterar uma autenticação.          │
 * ├─────────────┼─────────────────────────────────────────────────────────────┤
 * │ `Message`   │ Fornece o texto e o código de erro das exceções lançadas.   │
 * │             │                                                             │
 * └─────────────┴─────────────────────────────────────────────────────────────┘
 */

use galastri\extensions\Exception;
use galastri\language\Message;



/**
 * Esta classe permite definir rotas da aplicação que não têm acesso público
 * liberado. É normal que uma aplicação tenha partes, como um painel de
 * administração, por exemplo, que queremos que o usuário se autentique antes de
 * poder acessar.
 *
 * O que esta classe faz é basicamente checar se o usuário possui uma
 * autenticação válida pra acessar determinada rota, conferindo os dados da
 * sessão PHP e do cookie enviado pelo navegador.
 *
 * ✔️ EXEMPLO
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ Suponha a seguinte configuração de rota:                                  │
 * │                                                                           │
 * │ ┌───────────────────────────────────────────┐                             │
 * │ │ '/' => [                                  │                             │
 * │ │     'output' => 'view',                   │                             │
 * │ │                                           │                             │
 * │ │     '@main' => [...],                     │                             │
 * │ │     '@login' => [...],                    │                             │
 * │ │                                           │                             │
 * │ │     '/painel-admin' => [                  │                             │
 * │ │         'authTag' => 'acesso-admin',      │                             │
 * │ │         'authFailRedirectTo' => '/login', │                             │
 * │ │                                           │                             │
 * │ │         '@main' => [...],                 │                             │
 * │ │         '@usuarios' => [...],             │                             │
 * │ │         '@paginas' => [...],              │                             │
 * │ │     ],                                    │                             │
 * │ │ ];                                        │                             │
 * │ └───────────────────────────────────────────┘                             │
 * │                                                                           │
 * │ Neste exemplo, se um usuário acessar a página inicial ou a página         │
 * │ `/login`, ele pode fazer isso sem estar autenticado. Porém, se tentar     │
 * │ acessar `/painel-admin`, o framework entende que se trata de uma rota     │
 * │ protegida e vai checar se ele possui a autenticação exigida antes de      │
 * │ prosseguir com a requisição.                                              │
 * │                                                                           │
 * │ -----------COMO O FRAMEWORK SABE QUE ESSA ROTA ESTÁ PROTEGIDA?----------- │
 * │                                                                           │
 * │ A resposta é a configuração `authTag`. Veja que dentro da rota            │
 * │ `/painel-admin` definimos duas configurações:                             │
 * │                                                                           │
 * │ - `authTag`, com o valor 'acesso-admin'.                                  │
 * │ - `authFailRedirectTo`, que aponta para `/login`.                         │
 * │                                                                           │
 * │ Quando o usuário acessa `/painel-admin`, o framework encontra a           │
 * │ configuração `authTag` preenchida e entende que precisa conferir uma      │
 * │ autenticação antes de prosseguir. O nome que informamos nessa             │
 * │ configuração indica qual autenticação é necessária pra prosseguir.        │
 * │                                                                           │
 * │ No nosso exemplo, ele vai buscar pela autenticação chamada 'acesso-admin' │
 * │ na sessão PHP do usuário no servidor e no cookie enviado pelo navegador.  │
 * │                                                                           │
 * │ Se essa autenticação não existir ou estiver inválida, significa que o     │
 * │ usuário não está autenticado pra acessar essa rota. No caso do exemplo,   │
 * │ então, ele é redirecionado pra página `/login`, conforme definimos em     │
 * │ `authFailRedirectTo`.                                                     │
 * │                                                                           │
 * │ Se a autenticação existir e estiver válida, então o framework permite o   │
 * │ acesso e prossegue com a requisição da rota.                              │
 * └───────────────────────────────────────────────────────────────────────────┘
 *
 * Porém, tudo o que o framework faz automaticamente é essa conferência: checar
 * se o usuário mantém uma autenticação válida e correspondente à da
 * configuração `authTag` da rota.
 *
 * O framework não cria essa autenticação para o usuário sozinho, nem possui um
 * meio de fazer isso automaticamente. Cabe à aplicação criar este mecanismo
 * para, aí sim, chamar a classe Authentication pra registrar a autenticação.
 *
 * ------------ENTÃO COMO O USUÁRIO PODE ADQUIRIR ESSA AUTENTICAÇÃO?------------
 *
 * Normalmente, a aplicação terá que ter uma página que possui um formulário com
 * campos como usuário e senha. Ali, ele informa seus dados, e a aplicação (não
 * o framework) valida isso através de uma consulta ao banco de dados, ou
 * qualquer outra forma que confirme a autenticação ou não.
 *
 * Quando a aplicação decide que o usuário pode ser considerado autenticado, ela
 * usa o método `Authentication::register()` pra informar o nome da tag e
 * preparar os valores que quiser guardar junto da autenticação.
 * 
 * ⚠️ PORTANTO, É IMPORTANTE REFORÇAR:
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ A validação de dados de login, como usuário e senha, precisa ser          │
 * │ implementada pelo app. O framework não faz isso sozinho, é necessário que │
 * │ a própria aplicação crie uma forma de receber os dados e checar se eles   │
 * │ são válidos. Se forem, aí registra-se a autenticação do usuário.          │
 * │                                                                           │
 * │ O que o framework faz automaticamente é a checagem se o usuário está ou   │
 * │ não autenticado, mas o processo de validar e registrar uma autenticação   │
 * │ depende de programar isso na aplicação.                                   │
 * └───────────────────────────────────────────────────────────────────────────┘
 */

final class Authentication
{
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
     *
     * Esta classe não possui propriedades.
     */



    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                                MÉTODOS                                ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     */

    /**
     * 🟢 REGISTRO DE UMA AUTENTICAÇÃO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método registra uma autenticação na sessão PHP e envia ao navegador
     * um cookie com o token que será usado pra conferir essa autenticação nas
     * próximas requisições.
     *
     * Lembra do exemplo do painel de administração? Depois que a aplicação
     * conferir os dados informados pelo usuário e decidir que ele pode entrar,
     * executamos este método para registrar a autenticação.
     *
     * Informamos em `$authTag` o mesmo nome definido na configuração `authTag`
     * da rota. Também podemos passar em `$fields` um array com dados que
     * queremos guardar junto, como o identificador e o nome do usuário, ou
     * qualquer outro dado que a aplicação achar útil de ser mantido em sessão.
     *
     * ✔️ EXEMPLO
     * ┌───────────────────────────────────────────────────────────────────────┐
     * │ Depois que a aplicação validar os dados de login, podemos registrar:  │
     * │                                                                       │
     * │ ┌────────────────────────────────────────────┐                        │
     * │ │ Authentication::register('acesso-admin', [ │                        │
     * │ │     'id' => 42,                            │                        │
     * │ │     'nome' => 'André',                     │                        │
     * │ │ ]);                                        │                        │
     * │ └────────────────────────────────────────────┘                        │
     * │                                                                       │
     * │ Nas próximas requisições, essa autenticação será conferida            │
     * │ automaticamente nas rotas que exigirem a tag 'acesso-admin'.          │
     * └───────────────────────────────────────────────────────────────────────┘
     *
     * Os campos adicionais são opcionais e ficam armazenados na sessão, no
     * servidor. O cookie recebe apenas o token registrado pelo método.
     *
     * Podemos usar `getField()` pra consultar esses campos e `setField()` pra
     * alterar seus valores quando necessário.
     *
     * Se chamarmos este método novamente pra uma tag já registrada, substituímos
     * seus campos e prazos pelos dados dessa chamada.
     *
     * Também podemos informar em `$currentToken` um token que a aplicação já
     * validou e deseja reaproveitar. Se não informarmos um token, geramos um novo.
     *
     * Caso a aplicação informe um token, mas queira substituí-lo por outro,
     * usamos `$regenerateToken = true`.
     *
     * Em todos esses casos, o método retorna o token que foi registrado. Assim,
     * a aplicação pode guardar esse valor quando precisar manter um registro
     * persistente da autenticação.
     *
     *
     * _________________________________
     * @param string $authTag  
     *    Nome da autenticação exigida pela rota.
     * @param array $fields  
     *    Dados adicionais que serão guardados na sessão.
     * @param int $expiration  
     *    Prazo em segundos a partir do registro.
     * @param string $currentToken  
     *    Token que a aplicação deseja registrar. Se estiver vazio, geramos um
     *    novo token. A aplicação deve validar um token persistente antes de
     *    informá-lo aqui.
     * @param bool $regenerateToken  
     *    Se devemos gerar um novo token mesmo quando `$currentToken` foi
     *    informado. Por padrão, reaproveitamos o token recebido.
     * @param bool $regenerateIdSession  
     *    Se devemos trocar o identificador da sessão.
     * _________________________________
     * @return string  
     *    Retorna uma `string` com o token registrado na sessão e incluído no
     *    cookie enviado ao navegador.
     */ 
    
    public static function register(
        string $authTag,
        array  $fields = [],
        int    $expiration = 86400,
        string $currentToken = '',
        bool   $regenerateToken = false,
        bool   $regenerateIdSession = true
    ) : string
    {
        /**
         * Precisamos de uma sessão ativa antes de guardar os dados da
         * autenticação. Por isso, chamamos `startSession()`, que inicia ou
         * retoma a sessão PHP caso ela ainda não esteja aberta.
         */
        
        self::startSession();
        
        

        /**
         * Por padrão, também trocamos o identificador dessa sessão ao registrar
         * a autenticação. Assim, depois que a aplicação autenticar o usuário,
         * passamos a usar um novo identificador para a sessão por questões de
         * segurança, mas preservamos os dados que já estavam nela.
         *
         * É possível, porém, evitar esta troca de identificador colocando
         * `$regenerateIdSession = false`. Assim seguimos com o identificador
         * atual.
         *
         * 💡 OBSERVAÇÃO
         * ┌───────────────────────────────────────────────────────────────────┐
         * │ O argumento `true` de `session_regenerate_id()` indica que a      │
         * │ sessão anterior deve ser removida, porém os dados dela            │
         * │ continuarão disponíveis na nova sessão.                           │
         * └───────────────────────────────────────────────────────────────────┘
         *
         * Se a troca for solicitada, mas o `session_regenerate_id()` retornar
         * `false` por algum problema, fechamos a escrita da sessão e lançamos
         * uma exceção. Nesse caso, interrompemos o método antes de registrar a
         * autenticação.
         */
        
        if ($regenerateIdSession && !session_regenerate_id(true)) {
            session_write_close();
            
            throw new Exception(
                Message::get('AUTHENTICATION_SESSION_REGENERATION_FAILED')
            );
        }
        
        
        
        /**
         * Com a sessão ativa, vamos armazenar nela alguns valores que serão
         * úteis para checarmos a autenticação.
         *
         * Guardamos as autenticações dentro de `$_SESSION['gAuth']`, usando
         * `$authTag` como chave pra identificar cada uma. Esse agrupamento fica
         * reservado para os dados administrados por esta classe.
         *
         * Assim, conseguimos localizar as autenticações quando precisarmos
         * remover todas elas, preservando os demais dados da aplicação.
         *
         * Primeiro, precisamos definir qual token será registrado.
         *
         * Se `$currentToken` estiver vazio ou `$regenerateToken` estiver como
         * `true`, criamos um novo token usando `random_bytes(48)`, que fornece
         * 48 bytes aleatórios. Como esses bytes podem conter caracteres que não
         * são próprios pra texto, usamos `base64_encode()` pra transformá-los
         * em uma string.
         *
         * Caso contrário, reaproveitamos o token informado em `$currentToken`.
         * Guardamos o valor escolhido em `$token` pra usá-lo tanto na sessão
         * quanto no cookie.
         *
         * MAS PRA QUE PRECISAMOS DESSE TOKEN?
         *
         * Vamos guardar o mesmo valor na sessão PHP e no cookie enviado ao
         * navegador. Nas próximas requisições, a comparação desses valores será
         * uma das verificações feitas por `validate()` pra confirmar a
         * autenticidade da autorização.
         */ 

        if ($currentToken === '' || $regenerateToken) {
            $token = base64_encode(random_bytes(48));
        } else {
            $token = $currentToken;
        }

        $_SESSION['gAuth'][$authTag]['token'] = $token;
        
        
        
        /**
         * Também guardamos o IP informado em `$_SERVER['REMOTE_ADDR']`.
         * Opcionalmente podemos usar isso em `validate()`, para que confira se
         * o IP da requisição corresponde ao do registro. Por padrão, porém, a
         * conferência do IP fica desativada, sendo necessário ativar
         * manualmente ao chamar `validate()`.
         */
        
        $_SESSION['gAuth'][$authTag]['ip'] = $_SERVER['REMOTE_ADDR'];
        
        
        
        /**
         * Guardamos em `expiration` a duração informada pra autenticação,
         * em segundos. Por padrão, recebemos 86400, equivalentes a 24 horas.
         *
         * Mas guardar só essa duração não informa quando o prazo termina.
         * Precisamos também calcular o momento em que a autenticação vai
         * expirar.
         *
         * Pra isso, usamos `time()`, que fornece o instante atual em segundos,
         * e somamos a duração recebida. Guardamos o resultado em `expiresAt`.
         * Se informarmos 3600 segundos, por exemplo, esse resultado representa
         * o momento daqui a uma hora.
         *
         * Mais adiante, usaremos `expiresAt` pra definir a expiração do cookie.
         * Nas próximas requisições, `validate()` também consulta esse valor pra
         * conferir se o prazo já terminou. Se tiver terminado, remove os dados
         * dessa autenticação da sessão e recusa o acesso.
         */
        
        $_SESSION['gAuth'][$authTag]['expiration'] = $expiration;
        $_SESSION['gAuth'][$authTag]['expiresAt'] = time() + $expiration;
            
        

        /**
         * Por fim, `fields` recebe os dados adicionais informados pela
         * aplicação, como o identificador e o nome do usuário. Se nenhum campo
         * for informado, recebemos um array vazio.
         */

        $_SESSION['gAuth'][$authTag]['fields'] = $fields;

        
        
        /**
         * Com os dados gravados na sessão, precisamos enviar o token ao
         * navegador. Usamos `setcookie()` pra incluir na resposta HTTP a
         * instrução de criação do cookie.
         *
         * O nome desse cookie será o valor de `$authTag`. No exemplo do painel,
         * portanto, teremos um cookie chamado 'acesso-admin'.
         */
        
        setcookie(
            $authTag,
            
            
            
            /**
             * Já o conteúdo será uma string JSON com o token que acabamos de
             * guardar na sessão. Usamos `json_encode()` pra converter o array
             * ['token' => $token] nessa string.
             *
             * Os campos adicionais, como o identificador e o nome do usuário,
             * ficam guardados na sessão PHP, no servidor.
             *
             * Quando o navegador enviar esse cookie nas próximas requisições,
             * `validate()` poderá recuperar o token e compará-lo com o da
             * sessão.
             *
             * A opção `JSON_THROW_ON_ERROR` faz a conversão lançar uma exceção
             * caso não consiga gerar o JSON, interrompendo o registro nesse
             * ponto.
             */
            
            json_encode(
                ['token' => $token],
                JSON_THROW_ON_ERROR
            ),
            
            
            
            /**
             * Este array define as condições de validade e envio do cookie.
             *
             * Em `expires`, usamos a mesma data de expiração da autenticação
             * definida para a sessão. Assim, ambos possuem o mesmo prazo de
             * validade.
             *
             * Em `path`, definimos '/', permitindo o envio do cookie em
             * qualquer caminho da aplicação. Isso permite registrar a
             * autenticação em `/login` e usar o cookie depois, ao acessar
             * `/painel-admin`, por exemplo.
             *
             * A opção `secure` marca o cookie para envio por conexões HTTPS.
             *
             * Já `httponly` impede que o JavaScript da página leia o cookie
             * através de `document.cookie`.
             *
             * Com `samesite` definido como 'Strict', permitimos o envio do
             * cookie apenas em requisições originadas no mesmo site. Por
             * exemplo, se o usuário clicar em um link de outro site para o
             * painel, esse cookie não acompanha a requisição inicial, mesmo que
             * ele já esteja autenticado.
             *
             * Por fim, `domain` recebe o host da requisição, extraído de
             * `$_SERVER['HTTP_HOST']`. Se o valor incluir uma porta, como
             * 'exemplo.com:8080', `PHP_URL_HOST` retorna apenas 'exemplo.com'.
             * O domínio informado também abrange os subdomínios abaixo dele.
             *
             * 💡 OBSERVAÇÃO
             * ┌───────────────────────────────────────────────────────────────┐
             * │ O prefixo 'http://' serve apenas pra montar o texto que       │
             * │ `parse_url()` vai interpretar. A configuração funciona também │
             * │ em protocolo HTTPS.                                           │
             * └───────────────────────────────────────────────────────────────┘
             */
            
            [
                'expires' => $_SESSION['gAuth'][$authTag]['expiresAt'],
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict',
                'domain' => parse_url(
                    'http://'.$_SERVER['HTTP_HOST'], PHP_URL_HOST
                ),
            ]
        );

        
        
        /**
         * Se o cookie dessa autenticação já existir nos dados da requisição,
         * atualizamos seu conteúdo com o token que acabamos de registrar.
         *
         * O `setcookie()` acima inclui na resposta HTTP uma instrução para o
         * navegador, mas não altera `$_COOKIE`. Se tivermos trocado o token,
         * essa entrada ainda conterá o valor antigo, enquanto a sessão já terá
         * o novo. Uma chamada de `validate()` nesta mesma requisição
         * encontraria valores diferentes e recusaria a autenticação.
         *
         * Por isso, atualizamos também essa entrada com o mesmo JSON enviado ao
         * navegador, mantendo os valores correspondentes.
         *
         * Fazemos isso somente se a entrada já existir. Se o navegador não
         * enviou esse cookie, não criamos a chave em `$_COOKIE`, e a ausência
         * continuará sendo identificada por `validate()` nesta requisição.
         */

        if (isset($_COOKIE[$authTag])) {
            $_COOKIE[$authTag] = json_encode(
                ['token' => $token],
                JSON_THROW_ON_ERROR
            );
        }

        
        
        /**
         * Terminamos de preparar a autenticação.
         *
         * Agora, `session_write_close()` grava os dados da sessão no servidor e
         * encerra sua escrita nesta etapa da requisição.
         *
         * Os dados ficam disponíveis pra serem consultados nas próximas
         * requisições, enquanto a sessão for mantida e a autenticação estiver
         * dentro do prazo definido.
         *
         * Se precisarmos alterar a sessão novamente durante esta requisição,
         * devemos reabri-la antes.
         */
        
        session_write_close();
        
        
        
        /**
         * Retornamos o valor de token.
         */
        return $token;
    }

    
    
    /**
     * 🟢 VALIDAÇÃO DE UMA AUTENTICAÇÃO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método verifica se o usuário mantém uma autenticação válida para a
     * tag informada, conferindo os dados da sessão PHP e do cookie recebido.
     *
     * Quando registramos uma autenticação com `register()`, guardamos um token
     * na sessão e enviamos esse mesmo token em um cookie, armazenado pelo
     * navegador. Agora, precisamos conferir se o navegador enviou esse cookie
     * de volta e se seu token corresponde ao que temos na sessão.
     *
     * Também verificamos se a autenticação está dentro do prazo definido. Se
     * alguma das verificações falhar, retornamos `false`. Se todas passarem,
     * retornamos `true`.
     *
     * Este método é chamado automaticamente por `Galastri` quando a rota possui
     * a configuração `authTag` preenchida. O resultado permite que o núcleo
     * decida se a requisição pode continuar ou se deve seguir o tratamento de
     * falha de autenticação.
     *
     * Também podemos chamar o método diretamente na aplicação:
     *
     * ✔️ EXEMPLO
     * ┌───────────────────────────────────────────────────────────────────────┐
     * │ $autenticado = Authentication::validate('acesso-admin');              │
     * │                                                                       │
     * │ Aqui, `$autenticado` recebe `true` ou `false`, conforme o resultado   │
     * │ da conferência da autenticação chamada 'acesso-admin'.                │
     * └───────────────────────────────────────────────────────────────────────┘
     *
     * Opcionalmente, podemos passar `true` em `$ipCheck` pra conferir também se
     * o IP da requisição corresponde ao que guardamos no registro. Por padrão,
     * essa conferência fica desativada.
     *
     * Na chamada automática feita pelo núcleo, `$ipCheck` não é informado,
     * então o IP não participa da verificação das rotas.
     *
     *
     * _________________________________
     * @param string $authTag Nome da autenticação que queremos conferir.
     * @param bool $ipCheck Se devemos conferir também o IP registrado na
     *    autenticação.
     * _________________________________
     * @return bool  
     *    Retorna `true` se a autenticação passar nas verificações, ou `false`
     *    se falhar.
     */
    
    public static function validate(
        string $authTag,
        bool   $ipCheck = false
    ): bool
    {
        /**
         * Precisamos abrir a sessão pra consultar os dados da autenticação
         * guardados no servidor.
         */
        
        self::startSession();

        
        
        /**
         * Buscamos os dados da autenticação dentro do agrupamento `gAuth` da
         * sessão PHP, usando o nome informado em `$authTag`. Também buscamos o
         * cookie com esse mesmo nome, recebido do navegador.
         *
         * Guardamos esses valores em `$sessionTag` e `$cookieTag` pra usá-los
         * nas próximas verificações. Se algum deles não existir ou estiver como
         * `null`, usamos `false` como valor de fallback.
         */
        
        $sessionTag = $_SESSION['gAuth'][$authTag] ?? false;
        $cookieTag = $_COOKIE[$authTag] ?? false;
        
        
        
        /**
         * No primeiro teste, precisamos que os dados da sessão estejam em um
         * array e que o conteúdo do cookie seja uma string (um JSON), conforme
         * preparamos em `register()`.
         *
         * Se algum valor estiver fora do tipo esperado, inclusive quando
         * recebemos o `false` definido acima, fechamos a escrita da sessão e
         * retornamos `false` para indicar que a autenticação não é válida,
         * encerrando a verificação.
         */
        
        if (!is_array($sessionTag) || !is_string($cookieTag)) {
            session_write_close();
            return false;
        }

        
        
        /**
         * Recuperamos a data de expiração guardada em `expiresAt`. Lembra que,
         * em `register()`, calculamos esse valor somando a duração da
         * autenticação ao instante em que ela foi registrada?
         *
         * Agora, precisamos que esse valor seja um número inteiro e represente
         * uma data que ainda não chegou. Usamos `time()` pra comparar o prazo
         * com a data atual.
         *
         * Se `expiresAt` não existir ou estiver como `null`, recebemos `null`,
         * que será recusado pela conferência de tipo.
         *
         * Se o valor não for inteiro ou o prazo já tiver expirado, removemos da
         * sessão os dados da autenticação indicada por `$authTag`. Depois,
         * gravamos essa remoção ao fechar a escrita da sessão e retornamos
         * `false` para indicar que a autenticação não é válida.
         */
        
        $expiresAt = $sessionTag['expiresAt'] ?? null;

        if (!is_int($expiresAt) || $expiresAt <= time()) {
            unset($_SESSION['gAuth'][$authTag]);
            session_write_close();
            return false;
        }

        
        
        /**
         * Precisamos converter a string JSON armazenada em `$cookieTag` para um
         * array PHP.
         *
         * Usamos `json_decode()` com `true` como segundo argumento, que faz os
         * objetos do JSON serem convertidos em arrays associativos. Assim, o
         * conteúdo {"token":"..."} pode ser acessado pela chave 'token'.
         *
         * Guardamos o resultado na própria variável `$cookieTag`. Se o JSON
         * estiver inválido, `json_decode()` retorna `null`. Nesse caso, o
         * operador `??` usa o array vazio como fallback.
         */
        
        $cookieTag = json_decode($cookieTag, true) ?? [];
        
        
        
        /**
         * Agora, buscamos o token guardado na sessão e o recebido pelo cookie.
         * Se alguma chave não existir ou estiver como `null`, usamos `false`.
         *
         * Precisamos que ambos sejam strings, conforme o token que geramos em
         * `register()`. Se algum deles não for, fechamos a escrita da sessão e
         * retornamos `false` para indicar que a autenticação não é válida.
         *
         * Passar por essa conferência significa que temos dois tokens com o
         * tipo esperado, mas ainda falta a comparação dos valores, que vem logo
         * depois.
         */
        
        $sessionToken = $sessionTag['token'] ?? false;
        $cookieToken = $cookieTag['token'] ?? false;

        if (!is_string($sessionToken) || !is_string($cookieToken)) {
            session_write_close();
            return false;
        }
        
        
        
        /**
         * Já conferimos que os dois tokens são strings. Agora, precisamos
         * verificar se possuem exatamente o mesmo valor.
         *
         * Em `register()`, guardamos o mesmo token na sessão e no cookie. Por
         * isso, o token recebido do navegador precisa corresponder ao que está
         * guardado no servidor para finalmente validarmos esta autenticação.
         *
         * Se forem diferentes, porém, fechamos a escrita da sessão e retornamos
         * `false` para indicar que a autenticação não é válida.
         *
         * Se forem iguais, seguimos pra próxima verificação (que é opcional).
         */
        
        if ($sessionToken !== $cookieToken) {
            session_write_close();
            return false;
        }
        
        
        
        /**
         * Esta conferência só acontece se `$ipCheck` for `true`.
         *
         * Recuperamos o IP que guardamos quando a autenticação foi registrada.
         * Se esse dado não existir ou estiver como `null`, usamos `false`.
         *
         * Sendo `$ipCheck = true`, comparamos o IP registrado com
         * `$_SERVER['REMOTE_ADDR']`, que informa o endereço de quem está se
         * conectando ao servidor nesta requisição.
         *
         * Se os valores forem diferentes, fechamos a escrita da sessão e
         * retornamos `false` para indicar que a autenticação não é válida. Isso
         * também recusa a autenticação quando o IP registrado estiver ausente.
         */
        
        $ip = $sessionTag['ip'] ?? false;

        if ($ipCheck && $ip !== $_SERVER['REMOTE_ADDR']) {
            session_write_close();
            return false;
        }

        
        
        /**
         * Se chegamos até aqui, a autenticação passou por todas as verificações
         * exigidas nesta chamada.
         *
         * Fechamos a escrita da sessão e retornamos `true`, informando a quem
         * chamou o método que a autenticação está válida.
         */
        
        session_write_close();
        return true;
    }

    
    
    /**
     * 🟢 DEFINIÇÃO DE UM CAMPO DA AUTENTICAÇÃO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método cria ou atualiza um campo adicional nos dados de uma
     * autenticação guardada na sessão PHP.
     *
     * Lembra dos campos que podemos informar em `register()`? Podemos usar este
     * método pra alterar um desses valores ou acrescentar campos que ainda não
     * existem.
     *
     * Informamos o nome do campo em `$fieldName`, o valor em `$fieldValue` e a
     * tag que queremos alterar em `$authTag`.
     *
     * ✔️ EXEMPLO
     * ┌─────────────────────────────────────────────────────────────────────┐
     * │ Suponha que já registramos a autenticação 'acesso-admin' e queremos │
     * │ atualizar o nome guardado nela:                                     │
     * │                                                                     │
     * │ Authentication::setField(                                           │
     * │     fieldName: 'nome',                                              │
     * │     fieldValue: 'André Galastri',                                   │
     * │     authTag: 'acesso-admin'                                         │
     * │ );                                                                  │
     * │                                                                     │
     * │ Se o campo 'nome' já existir, seu valor será substituído. Se ainda  │
     * │ não existir, ele será criado com o valor informado.                 │
     * └─────────────────────────────────────────────────────────────────────┘
     *
     * O valor pode ser uma string, um número inteiro, um booleano ou `null`.
     * Ele fica guardado na sessão e pode ser consultado com `getField()`.
     *
     * Antes de gravar, conferimos se existe uma entrada para a tag na sessão.
     * Se essa entrada estiver ausente ou como `null`, lançamos uma exceção e
     * interrompemos a alteração.
     *
     *
     * _________________________________
     * @param string $fieldName  
     *    Nome do campo que queremos criar ou atualizar.
     * @param bool|int|null|string $fieldValue  
     *    Valor que será guardado no campo.
     * @param string $authTag  
     *    Nome da autenticação que receberá a alteração.
     * _________________________________
     * @return void
     */
    
    public static function setField(
        string               $fieldName,
        bool|int|null|string $fieldValue,
        string               $authTag
    ): void
    {
        /**
         * Precisamos abrir a sessão antes de consultar e alterar os dados da
         * autenticação.
         */
        
        self::startSession();

        
        
        /**
         * Antes de gravar o campo, conferimos se o agrupamento `gAuth` da
         * sessão possui uma entrada com o nome informado em `$authTag`. É nessa
         * entrada que `register()` guarda os dados da autenticação.
         *
         * Se ela não existir ou estiver como `null`, fechamos a escrita da
         * sessão e lançamos uma exceção, interrompendo a alteração. Passamos
         * `$authTag` pra identificar na mensagem qual autenticação não foi
         * registrada.
         *
         * O motivo dessa conferência é evitar que a gravação do campo crie uma
         * entrada na sessão pra uma tag que ainda não possui dados.
         *
         * Aqui conferimos apenas a existência dessa entrada. A conferência
         * completa da validade da autenticação é feita por `validate()`.
         */
        
        if (!isset($_SESSION['gAuth'][$authTag])) {
            session_write_close();

            throw new Exception(
                Message::get('AUTHENTICATION_UNCONFIGURED_AUTH_TAG'),
                [$authTag]
            );
        }

        
        
        /**
         * Guardamos o valor em `fields`, dentro dos dados da tag informada.
         * Usamos `$fieldName` como chave pra identificar o campo que queremos
         * criar ou atualizar.
         *
         * Se esse campo já existir, a atribuição substitui seu valor. Se ainda
         * não existir, ele é criado com o valor de `$fieldValue`.
         */
        
        $_SESSION['gAuth'][$authTag]['fields'][$fieldName] = $fieldValue;

        
        
        /**
         * Gravamos a alteração no servidor e fechamos a escrita da sessão.
         * Assim, o campo fica disponível pra consulta nas próximas requisições,
         * enquanto os dados dessa autenticação forem mantidos na sessão.
         */
        
        session_write_close();
    }

    
    /**
     * 🟢 CONSULTA DE UM CAMPO DA AUTENTICAÇÃO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método consulta o valor de um campo adicional guardado nos dados de
     * uma autenticação na sessão PHP.
     *
     * Podemos consultar os campos informados em `register()` e aqueles que
     * criamos ou atualizamos depois com `setField()`. Pra isso, informamos o
     * nome do campo em `$field` e a tag da autenticação em `$authTag`.
     *
     * ✔️ EXEMPLO
     * ┌───────────────────────────────────────────────────────────────────────┐
     * │ Depois de atualizar o nome no exemplo de `setField()`, podemos ler:   │
     * │                                                                       │
     * │ $nome = Authentication::getField('nome', 'acesso-admin');             │
     * │                                                                       │
     * │ Nesse caso, `$nome` recebe 'André Galastri', que foi o valor guardado │
     * │ no campo 'nome' dessa autenticação.                                   │
     * └───────────────────────────────────────────────────────────────────────┘
     *
     * Se a tag ou o campo não existir na sessão, retornamos `null`. Esse também
     * será o retorno se o próprio campo tiver sido definido com o valor `null`.
     *
     * A consulta recupera o dado presente na sessão. A conferência da validade
     * da autenticação, incluindo o prazo e o cookie, é feita por `validate()`.
     *
     *
     * _________________________________
     * @param string $field  
     *    Nome do campo que queremos consultar.
     * @param string $authTag  
     *    Nome da autenticação que contém o campo.
     * _________________________________
     * @return bool|int|null|string  
     *    Retorna o valor guardado no campo, ou `null` se ele não estiver
     *    disponível.
     */
    
    public static function getField (
        string $field,
        string $authTag
    ): bool|int|null|string
    {
        /**
         * Abrimos a sessão pra consultar os dados guardados no servidor.
         */
        
        self::startSession();

        
        
        /**
         * Dentro do agrupamento `gAuth` da sessão, usamos `$authTag` pra
         * localizar os dados da autenticação. Neles, acessamos `fields` e
         * buscamos o campo informado em `$field`.
         *
         * Guardamos o resultado em `$value`. Se a tag ou o campo não existir,
         * ou se o valor estiver como `null`, o operador `??` fornece `null`
         * como fallback.
         */
        $value = $_SESSION['gAuth'][$authTag]['fields'][$field] ?? null;

        
        
        /**
         * Fechamos a escrita da sessão e devolvemos o valor que acabamos de
         * consultar.
         */
        
        session_write_close();

        return $value;
    }

    
    
    /**
     * 🟢 DEFINIÇÃO DO PRAZO DE UMA AUTENTICAÇÃO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método define uma nova duração para uma autenticação que já foi
     * registrada, calculando um novo prazo de expiração a partir da data de
     * execução deste método, atualizando esse prazo na sessão e reenviando o
     * cookie ao navegador com esse novo prazo limite.
     *
     * Lembra que em `register()` definimos uma duração para a autenticação? Por
     * padrão, ela recebe 86400 segundos (24 horas), mas pode haver situações em
     * que a aplicação queira redefinir esse tempo enquanto o usuário navega,
     * seja para estipular um prazo menor ou maior ou para renovar o acesso por
     * um novo período, sem precisar recriar a autenticação nem perder os dados
     * que já estão na sessão.
     *
     * Informamos a nova duração em segundos em `$seconds` e a tag
     * correspondente em `$authTag`. A partir do momento da chamada, a
     * autenticação passa a valer exatamente a quantidade de segundos informada,
     * substituindo o prazo anterior.
     *
     * ✔️ EXEMPLO
     * ┌───────────────────────────────────────────────────────────────────────┐
     * │ Suponha que a autenticação 'acesso-admin' foi registrada com o prazo  │
     * │ padrão de 24 horas, mas agora queremos que ela expire em 2 horas      │
     * │ (7200 segundos) contadas a partir de agora:                           │
     * │                                                                       │
     * │ Authentication::setExpiration(7200, 'acesso-admin');                  │
     * │                                                                       │
     * │ O método recalcula a expiração na sessão para `time() + 7200` e emite │
     * │ um novo cookie com o mesmo token, válido por 2 horas a contar deste   │
     * │ instante.                                                             │
     * └───────────────────────────────────────────────────────────────────────┘
     *
     * O método substitui apenas a duração e atualiza o cookie com o novo prazo,
     * preservando o token e os campos já existentes na sessão.
     *
     * Antes de aplicar a alteração, conferimos se a tag existe na sessão e se
     * possui um token registrado. Se ela ainda não tiver sido criada, lançamos
     * uma exceção e interrompemos o método.
     *
     *
     * _________________________________
     * @param int $seconds  
     *    Nova duração da autenticação em segundos, contados a partir do momento
     *    da chamada.
     * @param string $authTag  
     *    Nome da autenticação que receberá o novo prazo.
     * _________________________________
     * @return void
     */
    
    public static function setExpiration(
        int $seconds,
        string $authTag
    ): void
    {
        /**
         * Precisamos abrir a sessão antes de consultar o token guardado nela e
         * atualizar os dados de validade da autenticação.
         */
        
        self::startSession();

        
        
        /**
         * Antes de atualizar o prazo, conferimos se a sessão possui um token
         * registrado para a tag informada em `$authTag`. É desse valor que
         * vamos precisar logo abaixo pra montar o cookie atualizado.
         *
         * Se a entrada ou o token não existir ou estiver como `null`, fechamos
         * a escrita da sessão e lançamos uma exceção, interrompendo a operação.
         * Passamos `$authTag` pra identificar na mensagem qual autenticação não
         * está configurada.
         *
         * O motivo dessa conferência é evitar criar na sessão uma entrada
         * incompleta pra uma tag que nunca foi registrada ou reenviar um cookie
         * sem token.
         *
         * Aqui conferimos apenas a presença do token dessa tag na sessão. A
         * conferência completa da validade da autenticação é feita por
         * `validate()`.
         */
        
        if (!isset($_SESSION['gAuth'][$authTag]['token'])) {
            session_write_close();

            throw new Exception(
                Message::get('AUTHENTICATION_UNCONFIGURED_AUTH_TAG'),
                [$authTag]
            );
        }

        
        
        /**
         * Substituímos a duração guardada em `expiration` pela nova
         * quantidade de segundos recebida em `$seconds`.
         *
         * Em seguida, recalculamos `expiresAt` somando essa duração ao instante
         * atual fornecido por `time()`. Assim, o prazo anterior deixa de valer
         * e a autenticação passa a expirar exatamente daqui a `$seconds`
         * segundos.
         */
        
        $_SESSION['gAuth'][$authTag]['expiration'] = $seconds;
        $_SESSION['gAuth'][$authTag]['expiresAt'] = time() + $seconds;

        
        
        /**
         * Com os novos valores gravados na sessão, precisamos atualizar também
         * o cookie no navegador, senão ele continuaria expirando no prazo
         * antigo.
         *
         * Reenviamos o cookie com o mesmo nome em `$authTag`, reaproveitando no
         * JSON o token que já estava salvo na sessão e mantendo as mesmas
         * configurações de caminho, segurança e domínio definidas em
         * `register()` para sobrescrever o mesmo cookie, só que agora com
         * `'expires'` apontando para o novo `expiresAt`.
         */
        
        setcookie(
            $authTag,
            json_encode(
                ['token' => $_SESSION['gAuth'][$authTag]['token']],
                JSON_THROW_ON_ERROR
            ),
            [
                'expires' => $_SESSION['gAuth'][$authTag]['expiresAt'],
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict',
                'domain' => parse_url(
                    'http://'.$_SERVER['HTTP_HOST'], PHP_URL_HOST
                ),
            ]
        );

        
        
        /**
         * Gravamos as alterações na sessão do servidor e fechamos sua escrita
         * nesta etapa da requisição.
         */
        
        session_write_close();
    }

    
    
    /**
     * 🟢 REMOÇÃO DE UMA AUTENTICAÇÃO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método remove uma autenticação específica, apagando seus dados da
     * sessão PHP e instruindo o navegador a invalidar o cookie correspondente.
     *
     * Quando o usuário clica no botão de deslogar, por exemplo, precisamos
     * encerrar a autenticação dele para que não continue acessando as rotas
     * protegidas.
     *
     * Como uma aplicação pode manter mais de uma tag de autenticação ao mesmo
     * tempo (por exemplo, uma para o painel administrativo e outra para uma
     * área do cliente), este método apaga apenas a autenticação informada em
     * `$authTag`. As demais autenticações e outros dados guardados na sessão
     * PHP permanecem intactos.
     *
     * Se a intenção for remover todas as autenticações da sessão atual de uma
     * só vez, preservando os outros dados da aplicação, usamos o método
     * `removeAll()`.
     *
     * ✔️ EXEMPLO
     * ┌───────────────────────────────────────────────────────────────────────┐
     * │ Suponha que o usuário clicou pra sair do painel administrativo. Para  │
     * │ encerrar apenas essa autenticação, fazemos:                           │
     * │                                                                       │
     * │ Authentication::remove('acesso-admin');                               │
     * │                                                                       │
     * │ A tag 'acesso-admin' é removida da sessão e dos dados da requisição   │
     * │ atual, e o navegador recebe a instrução para expirar o cookie. Com    │
     * │ isso, o acesso às rotas protegidas por essa tag passa a ser recusado. │
     * └───────────────────────────────────────────────────────────────────────┘
     *
     * Diferente de `setField()` e `setExpiration()`, este método não
     * exige que a tag exista na sessão. Se ela não estiver registrada, seguimos
     * normalmente: enviamos a instrução para expirar o cookie e removemos sua
     * chave de `$_COOKIE` na requisição atual.
     *
     * 💡 OBSERVAÇÃO
     * ┌──────────────────────────────────────────────────────────────────────┐
     * │ Do lado do servidor, enviamos uma instrução pra expirar o cookie. A  │
     * │ exclusão depende de o navegador receber e processar essa resposta,   │
     * │ então não conseguimos garantir que ela realmente aconteça.           │
     * │                                                                      │
     * │ Mesmo que a função `setcookie()` retorne `true`, isso não confirma   │
     * │ que o navegador excluiu o cookie.                                    │
     * │                                                                      │
     * │ De qualquer maneira, depois que gravamos a remoção dos dados da      │
     * │ autenticação na sessão, `validate()` passa a recusá-la nessa sessão, │
     * │ mesmo que o navegador ainda envie o cookie.                          │
     * └──────────────────────────────────────────────────────────────────────┘
     *
     *
     * _________________________________
     * @param string $authTag  
     *    Nome da autenticação que queremos remover.
     * _________________________________
     * @return void
     */
    
    public static function remove(
        string $authTag
    ): void
    {
        /**
         * Precisamos abrir a sessão antes de apagar os dados da autenticação
         * guardados no servidor.
         */
        
        self::startSession();

        
        
        /**
         * Removemos do agrupamento `gAuth` da sessão apenas a chave
         * correspondente a `$authTag`, apagando o token, o prazo e os campos
         * adicionais dessa autenticação.
         *
         * Se a tag não existir na sessão, `unset()` simplesmente não faz nada e
         * o método continua normalmente, sem lançar erro ou exceção. Outras
         * tags de autenticação ou dados da sessão permanecem intactos.
         */
        
        unset($_SESSION['gAuth'][$authTag]);

        
        
        /**
         * Em seguida, instruímos o navegador a apagar o cookie dessa tag.
         *
         * Como o protocolo HTTP não tem um comando direto pra deletar cookies,
         * reenviamos o cookie de nome `$authTag` com o conteúdo vazio e com uma
         * data de expiração no passado (`time() - 86400`, equivalente a 24
         * horas atrás). Quando recebe uma validade já vencida, o navegador
         * descarta o cookie imediatamente.
         *
         * Mantemos as mesmas opções de caminho, segurança e domínio definidas
         * em `register()`, pois o navegador só substitui e remove o cookie
         * original quando nome, `path` e `domain` coincidem.
         */
        
        setcookie(
            $authTag,
            '',
            [
                'expires' => time() - 86400,
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict',
                'domain' => parse_url('http://'.$_SERVER['HTTP_HOST'], PHP_URL_HOST),
            ]
        );

        
        
        /**
         * O `setcookie()` acima apenas avisa o navegador na resposta HTTP pra
         * remover o cookie, mas não altera a superglobal `$_COOKIE` que o PHP
         * montou no início desta requisição.
         *
         * Por isso, removemos também a chave `$authTag` de `$_COOKIE`. Assim,
         * se algum outro código ainda nesta mesma requisição consultar esse
         * cookie, ele já não estará mais disponível.
         */
        
        unset($_COOKIE[$authTag]);

        
        
        /**
         * Por fim, gravamos a remoção no servidor e fechamos a escrita da
         * sessão nesta etapa da requisição.
         */
        
        session_write_close();
    }

    
    
    /**
     * 🟢 REMOÇÃO DE TODAS AS AUTENTICAÇÕES
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método remove todas as autenticações registradas no agrupamento
     * `gAuth` da sessão atual e envia ao navegador a instrução pra expirar os
     * cookies correspondentes.
     *
     * Imagine que a aplicação mantém uma autenticação para o painel
     * administrativo e outra para a área do cliente. Se quisermos encerrar
     * esses dois acessos de uma só vez, podemos usar este método.
     *
     * Pra isso, recuperamos os nomes das tags registradas na sessão e chamamos
     * `remove()` para cada uma. Cada chamada remove os dados daquela
     * autenticação e solicita a exclusão do seu cookie.
     *
     * ✔️ EXEMPLO
     * ┌─────────────────────────────────────────────────────────────────────┐
     * │ Pra encerrar todas as autenticações da sessão atual, usamos:        │
     * │                                                                     │
     * │ Authentication::removeAll();                                        │
     * │                                                                     │
     * │ Dados da aplicação, como um carrinho de compras, por exemplo,       │
     * │ permanecem na sessão. O cookie da sessão PHP e os outros cookies da │
     * │ aplicação também ficam preservados.                                 │
     * └─────────────────────────────────────────────────────────────────────┘
     *
     * A lista vem dos registros da sessão. Assim, conseguimos remover uma
     * autenticação mesmo que seu cookie não tenha sido enviado nesta
     * requisição. Se o agrupamento não existir ou estiver vazio, não teremos
     * nenhuma tag pra remover.
     *
     * A exclusão dos cookies no navegador segue as mesmas condições explicadas
     * em `remove()`.
     *
     *
     * _________________________________
     * @return void
     */
    
    public static function removeAll(): void
    {
        /**
         * Abrimos a sessão pra consultar as autenticações registradas.
         */

        self::startSession();



        /**
         * Cada chave de `gAuth` identifica uma authTag. Usamos `array_keys()`
         * pra guardar esses nomes em uma lista antes de começar as remoções.
         *
         * Se o agrupamento não existir ou estiver como `null`, usamos um array
         * vazio como fallback.
         */

        $authTags = array_keys(
            $_SESSION['gAuth'] ?? []
        );



        /**
         * Fechamos a escrita da sessão antes de iniciar o laço. Fazemos isso
         * pois cada chamada de `remove()` que usaremos no loop a seguir já
         * reabre e fecha a escrita na sessão automaticamente. Por isso, não
         * precisamos mantê-la aberta aqui.
         */

        session_write_close();



        /**
         * Removemos cada autenticação usando o método que já cuida dos dados da
         * sessão e do cookie correspondente.
         */

        foreach ($authTags as $authTag) {
            self::remove($authTag);
        }
    }
    
    

    /**
     * 🟢 INICIALIZAÇÃO DA SESSÃO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método garante que a sessão PHP esteja aberta antes que os outros
     * métodos desta classe leiam ou alterem os dados guardados nela.
     *
     * Todos os métodos de `Authentication` chamam `startSession()` no início da
     * execução. Como eles fecham a escrita com `session_write_close()` ao
     * terminar (e a própria aplicação também pode já ter iniciado a sessão
     * antes), precisamos checar se a sessão realmente precisa ser aberta neste
     * momento.
     *
     * O motivo de centralizar essa conferência aqui é evitar repetir o mesmo
     * teste em cada método e impedir que o PHP dispare avisos ao tentar chamar
     * `session_start()` quando já existe uma sessão ativa.
     *
     *
     * _________________________________
     * @return void
     */
    
    private static function startSession(): void
    {
        /**
         * `session_status()` consulta o estado atual das sessões no PHP. Só
         * chamamos `session_start()` quando o retorno for `PHP_SESSION_NONE`,
         * que indica que sessões estão habilitadas, mas nenhuma está ativa
         * neste momento.
         *
         * Assim, se a sessão já estiver aberta (`PHP_SESSION_ACTIVE`), ou se o
         * recurso estiver desabilitado no servidor (`PHP_SESSION_DISABLED`),
         * não fazemos nada. Caso contrário, `session_start()` inicia ou retoma
         * a sessão para que possamos ler e gravar dados em `$_SESSION`.
         */
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}



/**
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * 📖 ACESSE O TUTORIAL DE USO DA CLASSE:
 * 🔗 LINK: /docs/tutoriais/classe-authentication.md
 * 
 * ◀️ VOLTAR AO TÓPICO ANTERIOR:
 * 🔗 LINK: /docs/estrutura-e-funcionamento/modulos-extensoes-e-outros.md
 */
