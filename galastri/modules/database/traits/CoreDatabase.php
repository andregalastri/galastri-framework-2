<?php
/**
 * -----------------------------------------------------------------------------
 * 📂
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║         ARQUIVO /galastri/modules/database/traits/CoreDatabase.php        ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 * Este arquivo define a trait que concentra o funcionamento comum das classes
 * de banco de dados do framework.
 * ─────────────────────────────────────────────────────────────────────────────
 */



/**
 * 🟢 NAMESPACE DA TRAIT
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * A trait `CoreDatabase` faz parte das traits internas dos módulos de banco de
 * dados.
 */

namespace galastri\modules\database\traits;



/**
 * 🟢 CLASSES UTILIZADAS
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * ┌─────────────┬─────────────────────────────────────────────────────────────┐
 * │ Classe      │ Uso                                                         │
 * ├─────────────┼─────────────────────────────────────────────────────────────┤
 * │ `Exception` │ Interrompe a execução quando falta configuração, a conexão  │
 * │             │ não está ativa ou alguma consulta falha.                    │
 * ├─────────────┼─────────────────────────────────────────────────────────────┤
 * │ `Message`   │ Fornece o texto e o código das exceções lançadas no módulo. │
 * ├─────────────┼─────────────────────────────────────────────────────────────┤
 * │ `Tools`     │ Identifica o tipo dos valores passados no `bind()` e valida │
 * │             │ o formato da mensagem em `onError()`.                       │
 * └─────────────┴─────────────────────────────────────────────────────────────┘
 */

use galastri\extensions\Exception;
use galastri\language\Message;
use galastri\modules\Tools;



/**
 * Esta trait reúne toda a estrutura compartilhada pelas classes de conexão com
 * banco de dados, como `MysqlDatabase` e `SqliteDatabase`.
 *
 * Em vez de repetir a mesma lógica em cada driver, deixamos aqui tudo o que é
 * igual entre eles:
 *
 * - Os setters que guardam os dados da conexão (`host`, `port`, `database`,
 *   `username`, `password`, `options` e `location`).
 * 
 * - O carregamento automático de configurações a partir do arquivo
 *   `/app/config/database.php`.
 * 
 * - O controle da conexão PDO e das transações (`begin`, `commit` e `cancel`).
 * 
 * - A fila de consultas com parâmetros (`query`, `bind`, `clearBind` e
 *   `submit`).
 * 
 * - O tratamento personalizado de falhas (`onError`) e a leitura dos resultados
 *   (`getResult`, `getLastId` e `hasResult`).
 *
 * Com isso, cada classe de banco só precisa cuidar do que é específico do seu
 * driver, como definir suas chaves obrigatórias e implementar `connect()` com o
 * DSN correspondente.
 */

trait CoreDatabase
{
    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                              CONSTANTES                               ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     */
    
    /**
     * 🟢 CAMINHO DO ARQUIVO DE CONFIGURAÇÕES
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Esta constante guarda a localização do arquivo de configurações de
     * bancos de dados da aplicação.
     *
     * Quando criamos uma conexão informando o nome de uma configuração, é nesse
     * arquivo que os dados dela serão buscados.
     *
     * Aproveite para ver o arquivo de configurações agora e depois volte aqui:
     * 🔗 LINK: /app/config/database.php
     *
     *
     * _________________________________
     * @var string
     */

    private const CONFIG_FILE = PROJECT_DIR.'/app/config/database.php';
    
    
    
    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                             PROPRIEDADES                              ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     */
    
    /**
     * 🟢 INSTÂNCIA ATIVA DO PDO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Esta propriedade armazena a instância ativa da conexão `PDO` com o
     * banco de dados.
     *
     * Ela começa como `null`, recebe o objeto `PDO` quando a conexão é aberta
     * e volta a ser `null` ao desconectar. É através dela que preparamos as
     * consultas SQL, controlamos transações e obtemos o último ID inserido.
     *
     *
     * _________________________________
     * @var ?\PDO
     */
    
    private ?\PDO $pdo = null;
    
    
    
    /**
     * 🟢 ESTADO DA CONEXÃO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Esta propriedade indica se há uma conexão ativa com o banco de dados no
     * momento.
     *
     * Ela começa como `false`, passa para `true` quando a conexão é aberta e
     * volta para `false` ao desconectar. Os métodos da classe consultam esse
     * estado para impedir operações sem conexão e evitar que uma nova conexão
     * seja aberta sobre outra já ativa.
     *
     *
     * _________________________________
     * @var bool
     */
    
    private bool $connected = false;
    
    
    
    /**
     * 🟢 PARÂMETROS DAS CONSULTAS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Esta propriedade armazena os parâmetros informados pelo método `bind()`
     * para serem vinculados às consultas SQL antes da execução.
     *
     * ✔️ EXEMPLO
     * ┌───────────────────────────────────────────────────────────────────────┐
     * │ Ao chamar `$db->bind(':nome', 'André')->bind(':idade', 30)`, esta     │
     * │ propriedade passa a armazenar:                                        │
     * │                                                                       │
     * │ [                                                                     │
     * │     ':nome' => 'André',                                               │
     * │     ':idade' => 30,                                                   │
     * │ ]                                                                     │
     * └───────────────────────────────────────────────────────────────────────┘
     *
     * Ao executar as consultas, a classe percorre esse array para aplicar os
     * valores com `bindParam()`, mantendo os parâmetros salvos até serem limpos
     * com `clearBind()`, `cancel()` ou `disconnect()`.
     *
     *
     * _________________________________
     * @var array<int|string,mixed>
     */
    
    private array $bind = [];
    
    
    
    /**
     * 🟢 RESULTADOS NOMEADOS DAS CONSULTAS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Esta propriedade armazena os resultados das consultas executadas com um
     * rótulo (`$label`) em `query()`.
     *
     * Quando informamos um nome ou número no segundo parâmetro de `query()`, o
     * resultado fica guardado sob essa chave, permitindo rodar várias consultas
     * e ler cada uma depois por `getResult()`, `getLastId()` e `hasResult()`.
     *
     * ✔️ EXEMPLO
     * ┌───────────────────────────────────────────────────────────────────────┐
     * │ Ao executar `$db->query('SELECT ...', 'usuarios')->submit()`, esta    │
     * │ propriedade passa a armazenar algo como:                              │
     * │                                                                       │
     * │ [                                                                     │
     * │     'usuarios' => [                                                   │
     * │         'label' => 'usuarios',                                        │
     * │         'data' => [...],                                              │
     * │         'found' => true,                                              │
     * │         'affectedRows' => 2,                                          │
     * │         'lastId' => -1,                                               │
     * │     ],                                                                │
     * │ ]                                                                     │
     * └───────────────────────────────────────────────────────────────────────┘
     *
     * O array é limpo quando chamamos `cancel()` ou `disconnect()`. Se um mesmo
     * nome for dado para duas consultas diferentes, a segunda sobrepõe a
     * primeira (ou a remove, caso haja falha). Se uma consulta falhar, a nova
     * chave não é armazenada. Os resultados de outros rótulos permanecem
     * disponíveis.
     *
     *
     * _________________________________
     * @var array<int|string,array>
     */
    
    private array $namedResult = [];
    
    
    
    /**
     * 🟢 ÚLTIMO RESULTADO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Esta propriedade armazena o resultado da última consulta executada com
     * sucesso por `submit()`.
     *
     * Diferente de `$namedResult`, ela guarda apenas um resultado por vez:
     * cada nova consulta concluída substitui o conteúdo anterior. Se a consulta
     * for nomeada, seu resultado também fica guardado em `$namedResult`,
     * permitindo acessá-lo pelo rótulo depois.
     *
     * Quando chamamos `getResult()`, `getLastId()` ou `hasResult()` sem informar
     * um rótulo, é daqui que os dados são lidos.
     *
     * ✔️ EXEMPLO
     * ┌───────────────────────────────────────────────────────────────────────┐
     * │ Ao executar `$db->query('SELECT ...')->submit()`, esta propriedade    │
     * │ passa a armazenar:                                                    │
     * │                                                                       │
     * │ [                                                                     │
     * │     'label' => null,                                                  │
     * │     'data' => [...],                                                  │
     * │     'found' => true,                                                  │
     * │     'affectedRows' => 2,                                              │
     * │     'lastId' => -1,                                                   │
     * │ ]                                                                     │
     * └───────────────────────────────────────────────────────────────────────┘
     *
     * O array é limpo antes de executar cada consulta e também quando chamamos
     * `cancel()` ou `disconnect()`. Se uma consulta falhar, ele permanece vazio,
     * evitando que o resultado anterior seja confundido com o da nova execução.
     *
     *
     * _________________________________
     * @var array<string,mixed>
     */
    
    private array $lastResult = [];
    
    
    
    /**
     * 🟢 ENDEREÇO DO SERVIDOR
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Esta propriedade guarda o endereço do servidor de banco de dados (como
     * `'localhost'` ou `'127.0.0.1'`).
     *
     * Ela recebe esse valor por meio de `setHost()`, seja automaticamente ao
     * carregar uma configuração de `/app/config/database.php` ou manualmente na
     * aplicação, e é usada ao montar a conexão em drivers baseados em servidor,
     * como `MysqlDatabase`.
     *
     *
     * _________________________________
     * @var string
     */
    
    private string $host = '';
    
    
    
    /**
     * 🟢 PORTA DE CONEXÃO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Esta propriedade guarda a porta de conexão com o servidor de banco de
     * dados.
     *
     * Ela é preenchida por `setPort()`, seja ao carregar uma configuração de
     * `/app/config/database.php` ou manualmente na aplicação (drivers como
     * `MysqlDatabase` já a formatam para o DSN). Quando fica vazia, o driver
     * utiliza a porta padrão do servidor.
     *
     *
     * _________________________________
     * @var string
     */
    
    private string $port = '';
    
    
    
    /**
     * 🟢 NOME DO BANCO DE DADOS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Esta propriedade guarda o nome do banco de dados que será acessado na
     * conexão.
     *
     * Ela recebe esse valor por meio de `setDatabase()`, seja ao carregar uma
     * configuração de `/app/config/database.php` ou manualmente na aplicação, e
     * é usada ao montar a conexão em drivers baseados em servidor, como
     * `MysqlDatabase`.
     *
     *
     * _________________________________
     * @var string
     */
    
    private string $database = '';
    
    
    
    /**
     * 🟢 USUÁRIO DE ACESSO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Esta propriedade guarda o nome do usuário utilizado para autenticar no
     * servidor de banco de dados.
     *
     * Ela recebe esse valor por meio de `setUsername()`, seja ao carregar uma
     * configuração de `/app/config/database.php` ou manualmente na aplicação, e
     * é passada ao `PDO` ao abrir a conexão em drivers com autenticação, como
     * `MysqlDatabase`.
     *
     *
     * _________________________________
     * @var string
     */
    
    private string $username = '';
    
    
    
    /**
     * 🟢 SENHA DE ACESSO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Esta propriedade guarda a senha do usuário utilizado para autenticar no
     * servidor de banco de dados.
     *
     * Ela recebe esse valor por meio de `setPassword()`, seja ao carregar uma
     * configuração de `/app/config/database.php` ou manualmente na aplicação, e
     * é passada ao `PDO` ao abrir a conexão em drivers com autenticação, como
     * `MysqlDatabase` (podendo ficar vazio se não houver senha).
     *
     *
     * _________________________________
     * @var string
     */
    
    private string $password = '';
    
    
    
    /**
     * 🟢 OPÇÕES EXTRAS DO PDO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Esta propriedade guarda um `array` com opções extras repassadas ao
     * construtor do `PDO` na abertura da conexão (como `[\PDO::ATTR_TIMEOUT =>
     * 5]`).
     *
     * Ela recebe esses valores por meio de `setOptions()`, seja da chave
     * opcional `'options'` de `/app/config/database.php` ou manualmente na
     * aplicação, e é utilizada tanto no `MysqlDatabase` quanto no
     * `SqliteDatabase`.
     *
     *
     * _________________________________
     * @var array
     */
    
    private array $options = [];
    
    
    
    /**
     * 🟢 LOCALIZAÇÃO DO ARQUIVO DO BANCO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Esta propriedade guarda o caminho do arquivo do banco de dados no
     * servidor (ou `':memory:'` para usar um banco em memória).
     *
     * Ela recebe esse valor por meio de `setLocation()`, seja ao carregar uma
     * configuração de `/app/config/database.php` ou manualmente na aplicação, e
     * é usada ao montar a conexão em drivers baseados em arquivo, como
     * `SqliteDatabase`.
     *
     *
     * _________________________________
     * @var string
     */
    
    private string $location = '';
    
    
    
    /**
     * 🟢 FILA DE CONSULTAS SQL
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Esta propriedade armazena a fila de consultas registradas por `query()`
     * aguardando execução.
     *
     * Ao chamar `query()`, a consulta não roda imediatamente: ela é guardada
     * aqui como uma função anônima, permitindo encadear várias consultas antes
     * de enviá-las. Quando chamamos `submit()`, todas são executadas na ordem
     * em que foram adicionadas, e a fila é limpa ao finalizar o `submit()`, ao
     * cancelar ou ao desconectar.
     *
     *
     * _________________________________
     * @var array<\Closure>
     */
    
    private array $queryChain = [];
    
    
    
    /**
     * 🟢 FILA DE TRATAMENTOS DE ERRO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Esta propriedade armazena os tratamentos de erro personalizados
     * registrados pelo método `onError()`.
     *
     * Cada chamada a `onError()` adiciona aqui uma função anônima com a
     * mensagem e o código de erro esperados. Quando ocorre uma falha do PDO ao
     * conectar ou executar consultas, a classe percorre essa fila para aplicar
     * a mensagem personalizada correspondente ao erro. A fila é limpa ao
     * finalizar o `submit()`, ao cancelar ou ao desconectar.
     *
     *
     * _________________________________
     * @var array<\Closure>
     */
    
    private array $errorChain = [];
    
    
    
    /**
     * 🟢 DADOS ADICIONAIS DA EXCEÇÃO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Esta propriedade guarda os dados extras informados no terceiro parâmetro
     * de `onError()` para serem repassados à `Exception` quando a falha
     * acontece.
     *
     * Ao avaliar um erro do PDO, o tratamento correspondente preenche este
     * array para que `throwError()` o entregue à exceção lançada. Se nenhum
     * tratamento corresponder, ou ao chamar `cancel()` ou `disconnect()`, o
     * valor volta para `[]`.
     *
     *
     * _________________________________
     * @var array
     */
    
    private array $additionalData = [];
    
    
    
    /**
     * 🟢 MENSAGEM E CÓDIGO DA FALHA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Esta propriedade armazena a mensagem e o código de erro no formato
     * `[mensagem, código]` usado para construir a `Exception` em `throwError()`.
     *
     * Ao tratar uma `PDOException`, ela recebe os dados definidos no `onError()`
     * correspondente (como `['E-mail já cadastrado.', 1062]`). Se nenhum
     * tratamento em `$errorChain` corresponder à falha, passa a guardar a
     * mensagem e o código originais retornados pelo PDO.
     *
     *
     * _________________________________
     * @var array{0:string,1:int|string}
     */
    
    private array $failMessage;
    
    
    
    /**
     * 🟢 VALORES PARA PLACEHOLDERS DA MENSAGEM DE ERRO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Esta propriedade guarda os valores extras usados para preencher
     * placeholders (`{{}}`) na mensagem de erro definida em `onError()`.
     *
     * Quando passamos um `array` no primeiro parâmetro de `onError()` (como
     * `['O campo {{}} já está em uso.', 'email']`), o texto principal vai para
     * `$failMessage` e os demais itens ficam aqui para que `throwError()` os
     * repasse à `Exception`. Sem placeholders, ou ao cancelar, desconectar ou
     * usar a falha padrão do PDO, ela fica como `[]`.
     *
     *
     * _________________________________
     * @var array
     */
    
    private array $messageFlagValues = [];
    
    
    
    /**
     * 🟢 AUTORIZAÇÃO PARA EXIBIR A MENSAGEM DE ERRO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Esta propriedade indica se a mensagem da exceção lançada em
     * `throwError()` pode ser exibida mesmo quando `displayErrors` estiver
     * `false` nas rotas.
     *
     * Ela é definida como `false` a cada erro do PDO e só passa para `true` se
     * algum tratamento de `onError()` corresponder à falha. Em `throwError()`,
     * esse valor é repassado para `$exception->allowMessageDisplay`, permitindo
     * que `ErrorHandler` exiba mensagens personalizadas sem expor erros brutos
     * do banco em produção.
     *
     *
     * _________________________________
     * @var bool
     */
    
    private bool $allowMessageDisplay = false;

    
    
    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                                MÉTODOS                                ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     */
    
    /**
     * 🟢 DEFININDO O ENDEREÇO DO SERVIDOR
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método define o endereço do servidor de banco de dados na
     * propriedade `$host`.
     *
     * Ele é usado quando configuramos a conexão manualmente antes de chamar
     * `connect()` ou também ao carregar os dados de `/app/config/database.php`.
     *
     * Ao final, retorna a própria instância (`$this`) para permitir encadear
     * outros métodos na mesma chamada.
     *
     *
     * _________________________________
     * @param string $host  
     *    Endereço do servidor de banco de dados.
     * _________________________________
     * @return self  
     *    Retorna a própria instância para encadeamento de chamadas.
     */
    
    public function setHost(string $host): self
    {
        $this->host = $host;
        return $this;
    }

    
    
    /**
     * 🟢 DEFININDO A PORTA DE CONEXÃO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método define a porta usada para conectar ao servidor de banco de
     * dados na propriedade `$port`.
     *
     * Ele é usado ao configurar a conexão manualmente antes de `connect()` ou
     * ao carregar os dados de `/app/config/database.php`. Drivers específicos,
     * como `MysqlDatabase`, podem sobrescrever este método para adaptar o valor
     * ao formato do seu DSN.
     *
     * Ao final, retorna a própria instância (`$this`) para permitir encadear
     * outros métodos na mesma chamada.
     *
     *
     * _________________________________
     * @param string $port  
     *    Porta de conexão com o servidor de banco de dados.
     * _________________________________
     * @return self  
     *    Retorna a própria instância para encadeamento de chamadas.
     */
    
    public function setPort(string $port): self
    {
        $this->port = $port;
        return $this;
    }

    
    
    /**
     * 🟢 DEFININDO O NOME DO BANCO DE DADOS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método define o nome do banco de dados que será acessado na
     * propriedade `$database`.
     *
     * Ele é usado ao configurar a conexão manualmente antes de `connect()` ou
     * ao carregar os dados de `/app/config/database.php`.
     *
     * Ao final, retorna a própria instância (`$this`) para permitir encadear
     * outros métodos na mesma chamada.
     *
     *
     * _________________________________
     * @param string $database  
     *    Nome do banco de dados.
     * _________________________________
     * @return self  
     *    Retorna a própria instância para encadeamento de chamadas.
     */
    
    public function setDatabase(string $database): self
    {
        $this->database = $database;
        return $this;
    }

    
    
    /**
     * 🟢 DEFININDO O USUÁRIO DE ACESSO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método define, na propriedade `$username`, o nome do usuário usado
     * para autenticar no banco de dados.
     *
     * Ele é usado ao configurar a conexão manualmente antes de `connect()` ou
     * ao carregar os dados de `/app/config/database.php`.
     *
     * Ao final, retorna a própria instância (`$this`) para permitir encadear
     * outros métodos na mesma chamada.
     *
     *
     * _________________________________
     * @param string $username  
     *    Nome do usuário de acesso ao banco de dados.
     * _________________________________
     * @return self  
     *    Retorna a própria instância para encadeamento de chamadas.
     */
    
    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    
    
    /**
     * 🟢 DEFININDO A SENHA DE ACESSO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método define, na propriedade `$password`, a senha do usuário usado
     * para autenticar no banco de dados.
     *
     * Ele é usado ao configurar a conexão manualmente antes de `connect()` ou
     * ao carregar os dados de `/app/config/database.php`.
     *
     * Ao final, retorna a própria instância (`$this`) para permitir encadear
     * outros métodos na mesma chamada.
     *
     *
     * _________________________________
     * @param string $password  
     *    Senha do usuário de acesso ao banco de dados.
     * _________________________________
     * @return self  
     *    Retorna a própria instância para encadeamento de chamadas.
     */
    
    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    
    
    /**
     * 🟢 DEFININDO OPÇÕES EXTRAS DO PDO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método define, na propriedade `$options`, as opções extras
     * repassadas ao `PDO` na abertura da conexão.
     *
     * Ele é usado ao configurar a conexão manualmente antes de `connect()` ou
     * ao carregar os dados de `/app/config/database.php`.
     *
     * Ao final, retorna a própria instância (`$this`) para permitir encadear
     * outros métodos na mesma chamada.
     *
     *
     * _________________________________
     * @param array $options
     *    Opções extras de conexão para o `PDO`.
     * _________________________________
     * @return self
     *    Retorna a própria instância para encadeamento de chamadas.
     */
    
    public function setOptions(array $options): self
    {
        $this->options = $options;
        return $this;
    }

    
    
    /**
     * 🟢 DEFININDO A LOCALIZAÇÃO DO ARQUIVO DO BANCO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método define, na propriedade `$location`, o caminho do arquivo do
     * banco de dados no servidor (ou `':memory:'` para usar um banco em
     * memória).
     *
     * Ele é usado ao configurar a conexão manualmente antes de `connect()` ou
     * ao carregar os dados de `/app/config/database.php`.
     *
     * Ao final, retorna a própria instância (`$this`) para permitir encadear
     * outros métodos na mesma chamada.
     *
     *
     * _________________________________
     * @param string $location
     *    Caminho do arquivo do banco de dados ou `':memory:'`.
     * _________________________________
     * @return self
     *    Retorna a própria instância para encadeamento de chamadas.
     */
    
    public function setLocation(string $location): self
    {
        $this->location = $location;
        return $this;
    }

    
    
    /**
     * 🟢 ABERTURA DA CONEXÃO COM O BANCO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método é uma declaração de obrigatoriedade, forçando cada classe de
     * driver a implementar a abertura da conexão com o seu respectivo banco de
     * dados.
     *
     * Como cada driver monta um DSN diferente e passa argumentos próprios ao
     * `PDO`, `MysqlDatabase` e `SqliteDatabase` definem seu próprio
     * `connect()` e delegam a inicialização da conexão para
     * `establishConnection()`.
     *
     *
     * _________________________________
     * @return self
     *    Retorna a própria instância para encadeamento de chamadas.
     */
    
    abstract public function connect(): self;

    
    
    /**
     * 🟢 ENCERRAMENTO DA CONEXÃO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método encerra a conexão ativa com o banco de dados e redefine o
     * estado interno da instância.
     *
     * As configurações da conexão `host`, `database`, `location`, entre outras
     * são preservadas, permitindo reconectar com `connect()` depois se
     * precisar.
     *
     * Ao final, retorna a própria instância (`$this`) para permitir encadear
     * outros métodos na mesma chamada.
     *
     *
     * _________________________________
     * @return self
     *    Retorna a própria instância para encadeamento de chamadas.
     */
    
    public function disconnect(): self
    {
        /**
         * Descarta a instância `PDO`.
         */
        
        $this->pdo = null;
        
        
        /**
         * Marca `$connected` como `false`, informando que não está mais
         * conectado.
         */
        
        $this->connected = false;
        
        
        
        /**
         * Limpa as filas de consultas e erros, os parâmetros de bind e todos os
         * resultados armazenados.
         */
        
        $this->queryChain = [];
        $this->errorChain = [];
        $this->bind = [];
        $this->lastResult = [];
        $this->namedResult = [];
        $this->additionalData = [];
        $this->messageFlagValues = [];

        return $this;
    }

    
    
    /**
     * 🟢 INÍCIO DE TRANSAÇÃO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método inicia uma transação na conexão ativa com o banco de dados.
     *
     * Com uma transação aberta, as alterações enviadas ficam pendentes até você
     * confirmá-las com `commit()` ou desfazê-las com `cancel()`.
     *
     *
     * _________________________________
     * @return self
     *    Retorna a própria instância para encadeamento de chamadas.
     */
    
    public function begin(): self
    {
        /**
         * Garante que a conexão com o banco esteja ativa antes de prosseguir.
         * Se estiver desconectada, `isConnected()` lança uma exceção.
         */
        
        $this->isConnected();

        
        
        /**
         * Verifica se já existe uma transação em andamento nesta conexão. Caso
         * exista, lança uma exceção para evitar transações sobrepostas.
         */
        
        if ($this->pdo->inTransaction()) {
            throw new Exception(
                Message::get('DATABASE_TRANSACTION_ALREADY_ACTIVE')
            );
        }

        
        
        /**
         * Solicita ao `PDO` a abertura da transação no banco de dados.
         */
        
        $this->pdo->beginTransaction();

        
        
        return $this;
    }

    
    
    /**
     * 🟢 CANCELAMENTO DA TRANSAÇÃO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método cancela a transação ativa no banco de dados e descarta os
     * dados acumulados da operação atual.
     *
     * As alterações enviadas entre a chamada de `begin()` até o momento do
     * cancelamento são desfeitas no banco. A conexão com banco de dados é
     * mantida.
     * 
     * ⚠️ IMPORTANTE
     * ┌───────────────────────────────────────────────────────────────────────┐
     * │ Este método não desfaz transações já efetivadas no banco de dados.    │
     * │ Ele apenas cancela a transação atual.                                 │
     * └───────────────────────────────────────────────────────────────────────┘
     *
     *
     * _________________________________
     * @return self
     *    Retorna a própria instância para encadeamento de chamadas.
     */
    
    public function cancel(): self
    {
        /**
         * Garante que a conexão com o banco esteja ativa antes de prosseguir.
         * Se estiver desconectada, `isConnected()` lança uma exceção.
         */

        $this->isConnected();
        
        
        
        /**
         * Solicita ao `PDO` o rollback da transação atual, desfazendo as
         * alterações que estavam pendentes no banco de dados.
         */
        
        $this->pdo->rollBack();
        
        
        
        /**
         * Limpa as filas de consultas e erros, os parâmetros de `bind()`, os
         * resultados guardados e os dados auxiliares de erro da operação
         * cancelada, preservando a conexão e suas configurações.
         */
        
        $this->queryChain = [];
        $this->errorChain = [];
        $this->bind = [];
        $this->lastResult = [];
        $this->namedResult = [];
        $this->additionalData = [];
        $this->messageFlagValues = [];

        return $this;
    }

    
    
    /**
     * 🟢 CONFIRMAÇÃO DA TRANSAÇÃO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método confirma a transação ativa no banco de dados.
     *
     * As alterações enviadas entre a chamada de `begin()` até o momento do
     * commit são gravadas de forma definitiva no banco. A conexão com banco de
     * dados é mantida para novas requisições.
     *
     *
     * _________________________________
     * @return self
     *    Retorna a própria instância para encadeamento de chamadas.
     */
    
    public function commit(): self
    {
        /**
         * Garante que a conexão com o banco esteja ativa antes de prosseguir.
         * Se estiver desconectada, `isConnected()` lança uma exceção.
         */

        $this->isConnected();
        
        
        
        /**
         * Solicita ao `PDO` a efetivação da transação atual, tornando
         * definitivas as alterações realizadas no banco de dados.
         */
        $this->pdo->commit();

        return $this;
    }

    
    
    /**
     * 🟢 REGISTRO DE CONSULTA SQL NA FILA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método registra uma instrução SQL na fila `$queryChain` para ser
     * executada quando `submit()` for chamado.
     *
     * Cada consulta executada com sucesso atualiza `$lastResult`. Se informarmos
     * um rótulo em `$label`, o resultado também fica guardado em `$namedResult`,
     * permitindo acessá-lo por esse identificador em `getResult()`,
     * `getLastId()` e `hasResult()`.
     *
     *
     * _________________________________
     * @param string $queryString  
     *    Instrução SQL a ser preparada e executada.
     * 
     * @param int|null|string $label  
     *    Rótulo opcional para identificar o resultado desta consulta.
     * _________________________________
     * @return self
     *    Retorna a própria instância para encadeamento de chamadas.
     */
    
    public function query(string $queryString, int|null|string $label = null): self
    {
        /**
         * Garante que a conexão com o banco esteja ativa antes de prosseguir.
         * Se estiver desconectada, `isConnected()` lança uma exceção.
         */

        $this->isConnected();

        
        
        /**
         * A consulta não é executada na hora em que este método é chamado.
         * Armazenamos a execução em uma função anônima, colocando-a em uma
         * fila, formada pelo array `$queryChain`, permitindo executá-la somente
         * quando `submit()` for chamado.
         */
        
        $this->queryChain[] = function () use ($queryString, $label) {
            
        
        
            /**
             * Limpamos `$lastResult` antes de executar a consulta. Assim, se a
             * execução falhar, essa propriedade não continuará oferecendo o
             * resultado da consulta anterior.
             */
            
            $this->lastResult = [];
            
            
            
            /**
             * Se a consulta for nomeada, também removemos o resultado anterior
             * registrado com esse rótulo. Os resultados guardados com outros
             * rótulos continuam disponíveis.
             */
            
            if ($label !== null) {
                unset($this->namedResult[$label]);
            }
            
            
            
            /**
             * Mapeia os tipos de dados, relacionando-os aos tipos de parâmetro
             * aceitos pelo `PDO`. Isso é importante para que o valor chegue ao
             * banco de dados com o tipo correspondente ao dado em si.
             *
             * 💡 OBSERVAÇÃO
             * ┌───────────────────────────────────────────────────────────────┐
             * │ Valores `float` usam `PARAM_STR` porque o PDO não possui um   │
             * │ tipo específico para decimais.                                │
             * └───────────────────────────────────────────────────────────────┘
             */

            $pdoTypes = [
                'string' => \PDO::PARAM_STR,
                'int' => \PDO::PARAM_INT,
                'float' => \PDO::PARAM_STR,
                'bool' => \PDO::PARAM_BOOL,
                'null' => \PDO::PARAM_NULL,
                'resource' => \PDO::PARAM_LOB,
            ];

            
            
            /**
             * Aqui vamos tentar fazer a preparação e a consulta. Porém, sabendo
             * que podem ocorrer erros diversos específicos da classe PDO,
             * precisamos estar preparados para uma eventual exceção do tipo
             * `PDOException`. Se ela ocorrer, então conseguimos capturar
             * através do try/catch e tratar a mensagem de erro ou código antes
             * de prosseguir.
             */
            try {
                
            
            
                /**
                 * Prepara a consulta SQL no `PDO` antes de vincular os
                 * parâmetros.
                 * 
                 * Preparar a consulta com `prepare()` significa enviar primeiro
                 * o molde do comando SQL (ainda sem os valores, só com
                 * marcadores como :email ou ?) para o banco de dados analisar e
                 * deixar pronto para execução.
                 *
                 * Na prática, isso permite duas coisas importantes:
                 * 
                 * 1. Separar o comando SQL dos dados, fazendo o banco analisar
                 *    a estrutura da consulta primeiro no `prepare()` e receber
                 *    os valores separadamente na hora do `execute()`, sem nunca
                 *    misturá-los ao texto SQL. Isso evita que um valor digitado
                 *    pelo usuário altere a query (proteção contra SQL
                 *    Injection).
                 *
                 * 2. Criar o objeto `PDOStatement` em `$pdo` que será o
                 *    responsável por receber os parâmetros de `bind()`,
                 *    executar o comando SQL e, por fim, dar acesso ao resultado
                 *    do comando.
                 */
                
                $pdo = $this->pdo->prepare($queryString);

                
                
                /**
                 * 🤓 Aqui precisamos detalhar uma coisa:
                 * 
                 * Temos que percorrer os parâmetros registrados em
                 * `$this->bind` para vinculá-los à consulta preparada.
                 *
                 * No foreach() precisamos passar o valor através de referência
                 * adicionando `&` antes de `$value` (ficando `&$value`). O
                 * culpado disso é o `bindParam()`, que possui um funcionamento
                 * um pouco mais complexo que o `bindValue()`.
                 * 
                 * Quando informamos o valor no `bindParam()`, não estamos
                 * passando uma cópia do valor, mas sim uma referência que irá
                 * guardar qual será a variável que `execute()` deverá ler
                 * quando for chamado.
                 *
                 * Se repassarmos $value para ele, não estaremos passando o
                 * valor, mas sim apontando pra variável $value, que seria uma
                 * variável local do foreach.
                 * 
                 * Com a declaração `&$value`, a variável passa a não ter um
                 * valor próprio, mas sim uma referência direta à fonte de
                 * dados, que neste caso é a posição de `$this->bind[$key]`. É
                 * como uma janela posicionada logo atrás de outra, mostrando a
                 * mesma cena, ao invés de serem duas janelas cada uma mostrando
                 * uma cópia de cenas iguais.
                 *
                 * ✔️ EXEMPLO
                 * ┌───────────────────────────────────────────────────────────┐
                 * │ Imagine                                                   │
                 * │                                                           │
                 * │ $this->bind = [':nome' => 'André', ':idade' => 30];       │
                 * │                                                           │
                 * ├───────────────────────────────────────────────────────────┤
                 * │ Sem o `&` no `$value`, teremos:                           │
                 * │                                                           │
                 * │ - Volta 1 do `foreach`:                                   │
                 * │                                                           │
                 * │ O `bindParam()` vincula o parâmetro `:nome` à             │
                 * │ variável local `$value`, que no momento armazena 'André'. │
                 * │                                                           │
                 * │ - Volta 2 do `foreach`:                                   │
                 * │                                                           │
                 * │ O `foreach` sobrescreve `$value` com o valor `30` e       │
                 * │ também vincula o parâmetro `:idade` à variável local      │
                 * │ `$value`, que no momento armazena `30`.                   │
                 * │                                                           │
                 * │ Quando `execute()` for chamado, tanto `:nome` quanto      │
                 * │ `:idade` vão receber `30`, pois o bindParam() vai estar   │
                 * │ apontando ambos para a variável local `$value`, cujo      │
                 * │ valor atual é 30!                                         │
                 * ├───────────────────────────────────────────────────────────┤
                 * │ Com o `&` no `$value`, teremos:                           │
                 * │                                                           │
                 * │ - Volta 1 do `foreach`:                                   │
                 * │                                                           │
                 * │ O `bindParam()` vincula o parâmetro `:nome` ao lugar pra  │
                 * │ onde `&$value` aponta no momento. Usando a comparação das │
                 * │ janelas, ela passa a mostrar a mesma cena que             │
                 * │ `$this->bind[':nome']`. É isso que será armazenado, essa  │
                 * │ "seta", digamos assim.                                    │
                 * │                                                           │
                 * │ - Volta 2 do `foreach`:                                   │
                 * │                                                           │
                 * │ O `foreach` faz `$value` referenciar o próximo elemento   │
                 * │ do array. Novamente, usando a comparação das janelas, ela │
                 * │ passa a mostrar a mesma cena que `$this->bind[':idade']`. │
                 * │                                                           │
                 * │ Por isso, `:idade` fica vinculado a esse elemento. O      │
                 * │ vínculo de `:nome` continua registrado e ligado a         │
                 * │ `$this->bind[':nome']`, preservando o valor anterior.     │
                 * │                                                           │
                 * │ Quando `execute()` rodar, `:nome` vai estar apontando pra │
                 * │ 'André' e `:idade` vai estar apontando pra `30`, pois o   │
                 * │ bindParam() registrou a fonte de onde o dado vem.         │
                 * └───────────────────────────────────────────────────────────┘
                 * 
                 * ------POR QUE USAR bindParam() AO INVÉS DE bindValue()?------
                 * 
                 * Mantemos `bindParam()` para preservar o comportamento de
                 * valores que são armazenados como `resource` no PHP, como o
                 * caso de streams de arquivos abertos por `fopen()`. O PDO pode
                 * modificar o valor referenciado ao processar esse parâmetro.
                 *
                 * No SQLite, por exemplo, o conteúdo lido do stream passa a
                 * ficar armazenado como uma string no próprio array. Assim,
                 * outra consulta pode reutilizar esse conteúdo sem precisar ler
                 * o arquivo novamente.
                 *
                 * Uma troca direta por `bindValue()` muda esse comportamento: o
                 * array continua guardando o stream em si. Ao reutilizar esse
                 * parâmetro em outra consulta, o PDO tenta ler seu conteúdo a
                 * partir da posição atual.
                 *
                 * Se a primeira consulta já leu o conteúdo até o fim, a segunda
                 * pode receber um conteúdo vazio, pois a posição de leitura do
                 * stream não foi resetada.
                 * 
                 * Mantemos `bindParam()`, portanto, para preservar esse
                 * reaproveitamento: o conteúdo já lido do `resource` continua
                 * disponível no array para as próximas consultas.
                 */
                
                foreach ($this->bind as $key => &$value) {
                
                    /**
                     * Identificamos o tipo do valor e checamos se ele está
                     * vazio e se é zero (`0`, `0.0` ou `'0'`).
                     *
                     * Precisamos dessa checagem de zero porque a função
                     * `empty()` do PHP também considera `0`, `0.0` e `'0'` como
                     * vazios, e esses valores são dados válidos que não devem
                     * ser convertidos para `null` caso passem pelo teste de
                     * vazio abaixo.
                     */
                    
                    $type = Tools::typeOf($value);
                    $isEmpty = empty($value);
                    $isZero = in_array($value, [0, 0.0, '0'], true);

                    
                    
                    /**
                     * Se o tipo do valor estiver mapeado na lista `$pdoTypes`,
                     * vinculamos o parâmetro com a constante `PDO::PARAM_...`
                     * correspondente.
                     */
                    
                    if (isset($pdoTypes[$type])) {
                        $pdo->bindParam(
                            $key,
                            $value,
                            $pdoTypes[$type]
                        );

                        
                        
                    /**
                     * Se o tipo não estiver em `$pdoTypes`, mas o valor estiver
                     * vazio e não for zero, vinculamos como `PDO::PARAM_NULL`
                     * e também alteramos `$value` para `null`.
                     *
                     * Precisamos mudar o valor que `$value` está referenciando,
                     * alterando para `null`. Isso porque `bindParam()` lerá o
                     * valor no `execute()`. Se continuasse com o valor
                     * original, o `PDO` poderia falhar ao tentar enviá-lo.
                     */
                    
                    } elseif ($isEmpty && !$isZero) {
                        $pdo->bindParam(
                            $key,
                            $value,
                            \PDO::PARAM_NULL
                        );
                        
                        $value = null;

                        
                        
                    /**
                     * Caso contrário, trata-se de um tipo não suportado e não
                     * vazio (como um array preenchido ou um objeto), que não
                     * pode ser enviado diretamente como parâmetro SQL. Nesse
                     * caso, interrompemos com uma exceção informando os tipos
                     * válidos.
                     */
                    
                    } else {
                        throw new Exception(
                            Message::get('DATABASE_BIND_PARAMETER_TYPE'),
                            [
                                $type,
                                'string, int, float, bool, null e resource'
                            ]
                        );
                    }
                }
                
                
                
                /**
                 * No PHP, após um `foreach` com referência (`&$value`), a
                 * variável `$value` continua apontando para o último elemento
                 * de `$this->bind`. Chamamos `unset($value)` para quebrar essa
                 * ligação logo após o laço e evitar alterações acidentais.
                 */
                
                unset($value);

                
                
                /**
                 * Com os parâmetros devidamente vinculados, executamos a
                 * consulta preparada no banco de dados com `execute()`.
                 *
                 * Se a execução retornar `true`, extraímos os dados e metadados
                 * do resultado e os guardamos para leitura posterior.
                 */
                
                if ($pdo->execute()) {
                    
                
                
                    /**
                     * Agora `processResult()` lê a resposta da consulta e
                     * devolve um array padronizado com os registros retornados
                     * e as informações da execução (linhas afetadas e último ID
                     * inserido).
                     */
                    
                    $result = $this->processResult($pdo);

                    
                    
                    /**
                     * Grava o resultado estruturado em `$lastResult`. Se um
                     * rótulo foi informado, também guarda esse resultado em
                     * `$namedResult[$label]`.
                     */
                    
                    $this->setResult($result, $label);
                }

            } catch (\PDOException $e) {
                
                /**
                 * Se ocorrer alguma falha do `PDO` ao preparar, vincular ou
                 * executar a consulta, repassamos os dados da exceção para
                 * filterPdoExceptionMessage() para tratar a exceção, comparando
                 * o erro com os tratamentos cadastrados por `onError()`, se
                 * houver. Caso algum corresponda, preparamos a mensagem
                 * personalizada; caso contrário, preparamos a mensagem original
                 * do `PDO`.
                 */
                
                $this->filterPdoExceptionMessage($e);
                
                
                
                /**
                 * Dispara a `Exception` final do framework com a mensagem, o
                 * código de erro e os dados extras definidos acima.
                 */
                
                $this->throwError();
            }
        };

        return $this;
    }
    
    
    
    /**
     * 🟢 REGISTRO DE PARÂMETROS DAS CONSULTAS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método registra os valores que vão preencher os marcadores das
     * consultas SQL, guardando-os na propriedade `$bind`.
     *
     * Em vez de montar a consulta colando valores direto no texto SQL (como
     * `"WHERE id = $id"`), escrevemos a consulta em `query()` usando marcadores
     * nomeados (como `:id`) ou posicionais (`?`) e entregamos os valores
     * separados por aqui.
     * 
     * ⚠️ IMPORTANTE
     * ┌───────────────────────────────────────────────────────────────────────┐
     * │ Os marcadores nomeados precisam sempre iniciar com dois pontos `:`    │
     * └───────────────────────────────────────────────────────────────────────┘
     *
     * O motivo disso é segurança e previsibilidade: quando colamos variáveis
     * direto na string SQL, criamos uma vulnerabilidade para ataques de SQL
     * injection, em que o atacante altera o valor da variável pra algo que
     * acaba sendo interpretado como um comando SQL arbitrário, com
     * possibilidade de danos ao banco de dados.
     * 
     * Com `bind()`, o `PDO` envia a estrutura da consulta e os valores
     * separadamente, tratando cada dado apenas como valor e respeitando seu
     * tipo.
     *
     * Podemos usar `bind()` de duas formas: informando um parâmetro por chamada
     * (chave e valor) ou passando vários de uma vez em um `array`.
     *
     * ✔️ EXEMPLO
     * ┌───────────────────────────────────────────────────────────────────────┐
     * │ Passando um parâmetro por chamada:                                    │
     * │                                                                       │
     * │ $db->bind(':id', 42)                                                  │
     * │    ->bind(':ativo', true)                                             │
     * │    ->query('SELECT * FROM contas WHERE id = :id AND ativo = :ativo')  │
     * │    ->submit();                                                        │
     * │                                                                       │
     * ├───────────────────────────────────────────────────────────────────────┤
     * │ Passando vários parâmetros de um array:                               │
     * │                                                                       │
     * │ $valores = [':id' => 42, ':ativo' => true];                           │
     * │                                                                       │
     * │ $db->bind($valores)                                                   │
     * │    ->query('SELECT * FROM contas WHERE id = :id AND ativo = :ativo')  │
     * │    ->submit();                                                        │
     * └───────────────────────────────────────────────────────────────────────┘
     *
     * - Os parâmetros informados vão se acumulando em `$this->bind` a cada
     *   chamada
     * 
     * - Se você chamar `bind()` informando uma chave já existente, apenas o
     *   valor dela é atualizado.
     * 
     * - Tudo o que for registrado aqui continua valendo para as próximas
     *   consultas até ser limpo com `clearBind()`, `cancel()` ou
     *   `disconnect()`.
     *
     *
     * _________________________________
     * @param array|int|string $bind  
     *    Pode ser um marcador nomeado (como `':id'`), uma posição numérica
     *    (`1`, `2`...) para marcadores `?`, ou um `array` associativo com
     *    vários pares `[chave => valor]`.
     * 
     * @param mixed $value  
     *    Valor que substituirá o parâmetro na querystring. É ignorado quando
     *    `$bind` for um `array`.
     * _________________________________
     * @return self
     *    Retorna a própria instância para encadeamento de chamadas.
     */
    
    public function bind(array|int|string $bind, mixed $value = null): self
    {
        /**
         * Garante que a conexão com o banco esteja ativa antes de prosseguir.
         * Se estiver desconectada, `isConnected()` lança uma exceção.
         */

        $this->isConnected();

        
        
        /**
         * Como este método aceita receber tanto um `array` com vários
         * parâmetros quanto apenas um par separado em `$bind` e `$value`,
         * precisamos unificar a forma como vamos tratar os dados para não ter
         * que criar duas lógicas diferentes.
         * 
         * Conferimos primeiro se `$bind` ainda não é um `array`.
         *
         * Quando não for (por exemplo, numa chamada `bind(':id', 42)`),
         * reorganizamos os dois argumentos dentro de um `array` associativo,
         * usando `$bind` como chave e `$value` como valor (por exemplo, `[':id'
         * => 42]`).
         *
         * O motivo dessa conversão é evitar duplicar lógica: a partir desta
         * linha, `$bind` passa a ser sempre tratado como um `array`, permitindo
         * que o laço logo abaixo opere de forma unificada tanto para um único
         * parâmetro quanto para vários.
         */
        
        if (!is_array($bind)) {
            $bind = [$bind => $value];
        }

        
        
        /**
         * Sendo agora tudo tratado como array, percorremos item por item e
         * gravamos cada chave diretamente em `$this->bind`.
         */
        
        foreach ($bind as $key => $bindValue) {
            $this->bind[$key] = $bindValue;
        }

        return $this;
    }

    
    
    /**
     * 🟢 LIMPEZA DOS PARÂMETROS REGISTRADOS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método apaga todos os parâmetros armazenados em `$bind`, deixando
     * a lista vazia para as próximas consultas.
     *
     * O motivo de existir um método separado para isso é que `submit()` não
     * limpa `$bind` automaticamente depois de executar as consultas, o que
     * permite reaproveitar os mesmos parâmetros em mais de uma query. Porém,
     * após chamar `submit()`, se você for montar outra consulta na mesma
     * conexão com marcadores diferentes (ou sem parâmetros), os antigos
     * precisam ser descartados antes para o `PDO` não tentar vincular
     * marcadores que não existem na nova SQL.
     *
     *
     * _________________________________
     * @return self  
     *    Retorna a própria instância para encadeamento de chamadas.
     */
    public function clearBind(): self
    {
        $this->bind = [];

        return $this;
    }

    
    
    /**
     * 🟢 DISPARO DA EXCEÇÃO DE BANCO DE DADOS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método constrói e lança a `Exception` do framework quando ocorre uma
     * falha de banco de dados capturada do `PDO`.
     *
     * Ele é chamado ao conectar ou executar consultas, logo após
     * `filterPdoExceptionMessage()` preencher `$failMessage`,
     * `$messageFlagValues`, `$additionalData` e `$allowMessageDisplay` com os
     * dados vindos de `onError()` ou do próprio `PDO`.
     *
     *
     * _________________________________
     * @return void
     */
    
    private function throwError(): void
    {
        /**
         * A classe `Exception` espera argumentos em posições diferentes para
         * casos em que o texto principal possua ou não placeholders (`{{}}`).
         *
         * Se houver placeholders, o 2º argumento precisa ser um array com os
         * valores que vão preenchê-los.
         */
        
        $hasPlaceholders = Exception::hasPlaceholders($this->failMessage[0]);
        
        if ($hasPlaceholders) {
            
            $exception = new Exception(
                $this->failMessage,
                $this->messageFlagValues,
                $this->additionalData
            );
        
        
        
        /**
         * Caso contrário, ou seja, se não houver placeholders na mensagem,
         * então o texto é repassado e o 2º argumento recebe apenas
         * `$additionalData`.
         */
        
        } else {
            
            $exception = new Exception(
                $this->failMessage,
                $this->additionalData
            );
        }

        
        
        /**
         * Repassa o status da propriedade `$allowMessageDisplay` à exceção,
         * informando se a mensagem pode ser exibida ao usuário mesmo com
         * `displayErrors` desativado nas rotas, junto ao contexto de onde o
         * erro ocorreu (que no caso é em `database`).
         * 
         * A exibição com `displayErrors` desativado só é liberada quando algum
         * `onError()` correspondeu ao erro ocorrido.
         */
        
        $exception->allowMessageDisplay = $this->allowMessageDisplay;
        $exception->errorSource = 'database';
        
        throw $exception;
    }

    
    
    /**
     * 🟢 TRATAMENTO PERSONALIZADO DE ERROS DO BANCO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método registra, na fila `$errorChain`, uma mensagem personalizada
     * para quando ocorrer um erro específico do banco de dados.
     *
     * Por padrão, falhas do `PDO` trazem mensagens técnicas internas e ficam
     * ocultas do usuário quando a configuração de rota `displayErrors` está
     * `false`. Com `onError()`, podemos interceptar um erro específico, seja
     * pelo código SQLSTATE, pelo código numérico do driver ou por um trecho da
     * mensagem ou constraint, e substituí-lo por uma mensagem amigável para a
     * aplicação, liberando também a exibição dela no `ErrorHandler`.
     *
     * Você pode encadear quantos `onError()` precisar antes de chamar
     * `connect()` ou `submit()`. Se algum tratamento corresponder à falha,
     * `filterPdoExceptionMessage()` aplica o primeiro que bater com `$code`.
     *
     * ✔️ EXEMPLO
     * ┌───────────────────────────────────────────────────────────────────────┐
     * │ $db->onError('E-mail já cadastrado.', 1062)                           │
     * │    ->onError(['O campo {{}} já existe.', 'CPF'], 'cpf_unique')        │
     * │    ->bind([':email' => $email, ':cpf' => $cpf])                       │
     * │    ->query('INSERT INTO users (email, cpf) VALUES (:email, :cpf)')    │
     * │    ->submit();                                                        │
     * └───────────────────────────────────────────────────────────────────────┘
     *
     *
     * _________________________________
     * @param array|string $message  
     *    Texto da mensagem ou `array` onde o primeiro item é a mensagem (com
     *    placeholders `{{}}`) e os demais são os valores que os preenchem.
     * 
     * @param int|string $code  
     *    Código SQLSTATE, código do driver ou trecho da mensagem/constraint do
     *    erro que deve acionar este tratamento.
     * 
     * @param array $additionalData  
     *    Dados extras opcionais repassados para a `Exception` lançada.
     * _________________________________
     * @return self
     *    Retorna a própria instância para encadeamento de chamadas.
     */
    
    public function onError(
        array|string $message,
        int|string $code,
        array $additionalData = []): self
    {
        
        /**
         * O tratamento não é aplicado na hora em que este método é chamado.
         * Armazenamos a execução em uma função anônima, colocando-a em uma
         * fila, formada pelo array `$errorChain` para que
         * `filterPdoExceptionMessage()` a execute somente se ocorrer uma
         * `PDOException`.
         */
        
        $this->errorChain[] = function () use ($message, $code, $additionalData)
        {
            
        
        
            /**
             * Se `$message` veio como uma `string` simples, envolvemos em um
             * `array` de um elemento para padronizar a extração logo abaixo,
             * tratando os dois formatos com o mesmo código.
             */
            
            if (Tools::typeOf($message) !== 'array') {
               $message = [$message];
            }

            
            
            /**
             * Preenche as propriedades de erro da instância com os dados deste
             * tratamento: `array_shift()` retira o primeiro item de `$message`
             * (o texto principal) para montar `$failMessage`, enquanto os itens
             * restantes (se houver) seguem para `$messageFlagValues` para
             * preencher placeholders `{{}}` em `throwError()`.
             */
            
            $this->additionalData = $additionalData;
            $this->failMessage = [array_shift($message), $code];
            $this->messageFlagValues = $message ?? [];
        };

        return $this;
    }

    
    
    /**
     * 🟢 EXECUÇÃO DAS CONSULTAS ENFILEIRADAS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método executa, na ordem em que foram registradas, todas as
     * consultas SQL acumuladas na fila `$queryChain`.
     *
     * Como `query()` e `onError()` apenas preparam e enfileiram suas ações sem
     * rodar na hora, `submit()` é o ponto em que a execução é disparada.
     *
     * Ao terminar, com sucesso ou com erro, ele esvazia `$queryChain` e
     * `$errorChain` para não interferir nas próximas operações, mantendo os
     * parâmetros de `$bind` até você chamar `clearBind()`, `cancel()` ou
     * `disconnect()`.
     *
     *
     * _________________________________
     * @return self  
     *    Retorna a própria instância para encadeamento de chamadas.
     */
    
    public function submit(): self
    {
        /**
         * Garante que a conexão com o banco esteja ativa antes de prosseguir.
         * Se estiver desconectada, `isConnected()` lança uma exceção.
         */

        $this->isConnected();

        
        
        /**
         * Percorre `$queryChain` executando cada consulta registrada por
         * `query()`. Se alguma falhar e lançar uma exceção, o laço é
         * interrompido naquele ponto e as consultas seguintes não rodam.
         */
        
        try {
            foreach ($this->queryChain as $function) {
                $function();
            }
            
            
            
        /**
         * Usamos `finally` (sem `catch`) para não esconder nenhuma exceção que
         * possa ocorrer, mas garantindo que `$queryChain` e `$errorChain` sejam
         * sempre limpos ao final, mesmo em caso de falha, para que consultas e
         * tratamentos antigos não sobrem para a próxima chamada.
         */
        
        } finally {
            $this->queryChain = [];
            $this->errorChain = [];
        }

        return $this;
    }
    
    
    
    /**
     * 🟢 LEITURA DO RESULTADO DA CONSULTA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método retorna os dados e metadados de uma consulta já executada por
     * `submit()`.
     *
     * Quando chamamos sem parâmetros, ele devolve o resultado da última consulta,
     * armazenado em `$lastResult`. Quando informamos um rótulo, conforme usado
     * em `query()`, ele busca o resultado correspondente em `$namedResult`.
     *
     * Quando há resultado, o array retornado traz as seguintes chaves:
     *
     * - label: o rótulo da consulta, ou `null` quando não for uma consulta
     *   nomeada.
     * 
     * - data: lista com os registros retornados (em `SELECT`), ou um array
     *   vazio `[]`.
     * 
     * - found: armazena `true` ou `false` indicando se houve registros
     *   retornados ou linhas afetadas.
     * 
     * - affectedRows: a quantidade de linhas afetadas pela execução.
     * 
     * - lastId: último ID inserido na conexão, ou `-1` se não aplicável.
     *
     *
     * _________________________________
     * @param int|null|string $label  
     *    Rótulo da consulta desejada, ou `null` para obter o resultado da última
     *    consulta.
     * _________________________________
     * @return array  
     *    Retorna um array. Quando `$label` não for informado, retorna o valor
     *    de `$lastResult`, que armazena o resultado da última consulta
     *    executada ou um array vazio `[]` caso nenhuma consulta tenha sido
     *    realizada com sucesso.
     *
     *    Quando `$label` for informado e já houver uma consulta executada com
     *    esse rótulo, retorna o resultado dela em `$namedResult`. Se o rótulo
     *    não existir na lista, lança uma exceção antes de retornar qualquer
     *    valor.
     */
    
    public function getResult(int|null|string $label = null): array
    {
        /**
         * Garante que a conexão com o banco esteja ativa antes de prosseguir.
         * Se estiver desconectada, `isConnected()` lança uma exceção.
         */

        $this->isConnected();

        
        
        /**
         * Se nenhum rótulo foi informado, devolvemos diretamente
         * `$this->lastResult`, que será `[]` se nenhuma consulta tiver sido
         * executada com sucesso ainda ou se os resultados tiverem sido limpos.
         */
        
        if ($label === null) {
            return $this->lastResult;
        }
        
        
        
        /**
         * Quando um rótulo for informado, buscamos a entrada correspondente em
         * `$this->namedResult`, usando `false` como fallback caso esse rótulo
         * não exista.
         */
        
        $namedResult = $this->namedResult[$label] ?? false;

        
        
        /**
         * Se o rótulo não existir na lista, lançamos uma exceção antes de
         * retornar qualquer valor. Assim evitamos retornar `[]` em silêncio
         * quando houver erro de digitação no rótulo ou quando `submit()` não
         * tiver sido chamado.
         */
        
        if (!$namedResult) {
            throw new Exception(
                Message::get('DATABASE_RESULT_DOESNT_EXIST'),
                [$label]
            );
        }

        return $namedResult;
    }

    
    
    /**
     * 🟢 LEITURA DO ÚLTIMO ID INSERIDO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método retorna o último ID de inserção registrado no resultado de
     * uma consulta executada por `submit()`.
     *
     * Quando chamamos sem parâmetros, ele lê a chave `'lastId'` de
     * `$lastResult`, referente à última consulta executada. Quando informamos
     * um rótulo em `$label`, ele lê a chave `'lastId'` da consulta guardada em
     * `$namedResult`.
     *
     * Após executar cada consulta, consultamos o último ID informado pela
     * conexão PDO e guardamos esse valor junto ao resultado. Por isso, mesmo
     * em comandos como `SELECT`, `UPDATE` ou `DELETE`, podemos encontrar o ID
     * de um `INSERT` anterior. Isso não significa que aquela consulta gerou
     * um novo ID.
     *
     * Ao consultar um resultado nomeado, recuperamos o valor que registramos
     * naquele momento. Assim, podemos recuperar o ID de um `INSERT` anterior
     * mesmo depois de executar outras inserções na mesma conexão.
     *
     *
     * _________________________________
     * @param int|null|string $label  
     *    Rótulo da consulta desejada, ou `null` para ler o ID da última
     *    consulta executada.
     * _________________________________
     * @return int  
     *    Retorna um int com o ID positivo guardado no resultado consultado.
     *    Retorna `-1` quando não houver resultado salvo ou quando o PDO não
     *    tiver informado um ID positivo ao concluirmos aquela consulta,
     *    inclusive se ocorrer uma falha ao consultar o ID. Se a conexão estiver
     *    encerrada, lança uma exceção antes de retornar qualquer valor.
     */
    
    public function getLastId(int|null|string $label = null): int
    {
        /**
         * Garante que a conexão com o banco esteja ativa antes de prosseguir.
         * Se estiver desconectada, `isConnected()` lança uma exceção.
         */

        $this->isConnected();

        
        
        /**
         * Sem um rótulo, devolvemos o `'lastId'` registrado em
         * `$this->lastResult`. Se não houver resultado salvo, retornamos `-1`.
         */
        
        if ($label === null) {
            return $this->lastResult['lastId'] ?? -1;
        }
        
        
        
        /**
         * Quando um rótulo foi informado, retorna o `'lastId'` da entrada
         * correspondente em `$this->namedResult`, ou `-1` caso o rótulo não
         * exista ou não haja um ID positivo registrado nesse resultado.
         */
        
        return $this->namedResult[$label]['lastId'] ?? -1;
    }

    
    
    /**
     * 🟢 VERIFICAÇÃO DE EXISTÊNCIA DE RESULTADO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método informa se uma consulta executada por `submit()` trouxe
     * algum resultado no banco de dados.
     *
     * Em consultas que retornam linhas, como `SELECT`, ele verifica se ao
     * menos um registro foi encontrado. Em comandos de escrita, como `INSERT`,
     * `UPDATE` ou `DELETE`, ele verifica se ao menos uma linha foi afetada.
     *
     * Quando chamamos sem parâmetros, ele consulta `$lastResult`, referente à
     * última consulta executada. Quando informamos um rótulo em `$label`, ele
     * consulta a entrada correspondente em `$namedResult`. Se não houver
     * resultado salvo ou o rótulo não existir, retorna `false` sem lançar
     * exceção.
     *
     *
     * _________________________________
     * @param int|null|string $label  
     *    Rótulo da consulta desejada, ou `null` para verificar a última consulta
     *    executada.
     * _________________________________
     * @return bool  
     *    Retorna `true` se a consulta encontrou registros ou afetou linhas, ou
     *    `false` caso contrário ou se não houver resultado para o rótulo
     *    informado.
     */
    
    public function hasResult(int|null|string $label = null): bool
    {
        /**
         * Garante que a conexão com o banco esteja ativa antes de prosseguir.
         * Se estiver desconectada, `isConnected()` lança uma exceção.
         */

        $this->isConnected();
        
        
        
        /**
         * Verifica se `$this->lastResult` possui algum resultado armazenado e
         * lê a chave `found`, usando `false` como fallback caso ela não
         * exista.
         */
        
        $hasLastResult = !empty($this->lastResult);
        $hasLastResultFound = $this->lastResult['found'] ?? false;


        
        /**
         * Se nenhum rótulo foi informado, retorna `true` apenas quando houver
         * resultado salvo em `$this->lastResult` com a chave `found` igual a
         * `true`.
         */
        
        if ($label === null) {
            return $hasLastResult && $hasLastResultFound;
        }
        
        
        
        /**
         * Já quando um rótulo foi informado, busca a entrada correspondente em
         * `$this->namedResult`, usando um array vazio como fallback se o rótulo
         * não existir.
         * 
         * Retorna `true` apenas se a chave `found` existir e for verdadeira.
         * Caso contrário, retorna `false`.
         */
        
        $namedResult = $this->namedResult[$label] ?? [];

        return isset($namedResult['found']) && $namedResult['found'];
    }

    
    
    /**
     * 🟢 INICIALIZAÇÃO INTERNA DA CONEXÃO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método reúne toda a lógica comum de abertura da conexão com o banco
     * de dados, executando as validações iniciais, criando o objeto `PDO` e
     * aplicando os atributos padrão da conexão.
     *
     * Como cada driver monta um DSN e recebe argumentos próprios no `PDO`,
     * classes como `MysqlDatabase` e `SqliteDatabase` implementam `connect()`
     * apenas preparando a função `$createPdo` e delegam o restante para cá,
     * evitando duplicar verificações e tratamento de erros em cada driver.
     *
     *
     * _________________________________
     * @param callable $createPdo  
     *    Função responsável por instanciar e retornar o objeto `PDO` do driver
     *    específico.
     * _________________________________
     * @return void
     */
    
    private function establishConnection(callable $createPdo): void
    {
        /**
         * Checa se uma conexão já existe e, caso sim, lança uma exceção
         * impedindo a abertura de uma nova conexão nesta instância, exigindo
         * que a aplicação chame `disconnect()` antes de conectar novamente.
         *
         * O motivo disso é que os setters alteram os dados usados para abrir a
         * próxima conexão, mas não modificam a conexão PDO que já está aberta.
         * 
         * ✔️ EXEMPLO
         * ┌───────────────────────────────────────────────────────────────────┐
         * │ Imagine chamar                                                    │
         * │                                                                   │
         * │ $db->connect();                                                   │
         * │ $db->setDatabase('outro_banco');                                  │
         * │ $db->connect();                                                   │
         * │                                                                   │
         * │ Se apenas ignorássemos a segunda chamada, a aplicação continuaria │
         * │ usando o banco anterior, dando a impressão de que a troca         │
         * │ funcionou.                                                        │
         * │                                                                   │
         * │ Também não encerramos a conexão automaticamente, pois ela pode    │
         * │ ter uma transação em andamento.                                   │
         * └───────────────────────────────────────────────────────────────────┘
         * 
         * Portanto, exigir que se execute um `disconnect()` antes de fazer uma
         * nova conexão deixa explícito no código da aplicação que a conexão
         * anterior foi claramente encerrada.
         */
        
        if ($this->connected) {
            throw new Exception(Message::get('DATABASE_ALREADY_CONNECTED'));
        }

        
        
        /**
         * Colocamos a criação do `PDO` e sua configuração dentro de um
         * `try/catch` para `PDOException` porque falhas externas, como servidor
         * fora do ar, credenciais erradas ou arquivo sem permissão, fazem o
         * `PDO` lançar exceções com detalhes crus do banco. Capturando aqui,
         * conseguimos aplicar os tratamentos de `filterPdoExceptionMessage()` e
         * converter tudo em uma `Exception` controlada pelo framework.
         */
        
        try {
            
        
        
            /**
             * Percorremos a lista `$requiredProperties` do driver atual antes
             * mesmo de tentar conectar, porque a instância pode ter sido
             * configurada manualmente pelos setters sem passar por
             * `/app/config/database.php`. Se esquecermos de preencher `host`,
             * `database`, `username` ou `location`, já apontamos na hora qual
             * propriedade faltou em vez de tentar conectar à toa e receber um
             * erro genérico do `PDO`.
             */
            
            foreach ($this->requiredProperties as $property) {
                if ($this->$property === '') {
                    throw new Exception(
                        Message::get('DATABASE_MISSING_REQUIRED_PROPERTY'),
                        [$property]
                    );
                }
            }
            
            
            
            /**
             * Só agora executamos a função `$createPdo` recebida do driver.
             * Recebemos essa criação como função anônima justamente para poder
             * validar tudo antes e só abrir a conexão de fato aqui dentro do
             * `try/catch` comum, guardando o objeto `PDO` em `$this->pdo`.
             */
            
            $this->pdo = $createPdo();

            
            /**
             * Em seguida forçamos o `PDO` a trabalhar no modo de exceção para
             * garantir que qualquer erro futuro no banco sempre dispare
             * `PDOException` e caia nos nossos blocos `try/catch`, mesmo se o
             * usuário tiver passado outra configuração em `$options`.
             */
            
            $this->pdo->setAttribute(
                \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION
            );
            
            
            
            /**
             * Também desativamos a emulação de `prepared statements` para que o
             * próprio banco compile o molde SQL e receba os parâmetros
             * separados no protocolo, em vez de o PHP simular isso montando uma
             * string SQL antes de enviar.
             *
             * Fazemos isso por segurança e prevenção de bugs: além de não
             * depender do escape local do PHP contra SQL Injection, evitamos
             * problemas de tipagem como `LIMIT :qtd` tratado como texto entre
             * aspas e garantimos que colunas numéricas retornem com seus tipos
             * reais em vez de tudo vir como `string`.
             */
            
            $this->pdo->setAttribute(
                \PDO::ATTR_EMULATE_PREPARES, false
            );

            
            
            /**
             * Por fim, marcamos `$this->connected` como `true` aqui no final,
             * depois que a conexão foi aberta e configurada sem falhas. Se
             * marcássemos antes, qualquer erro no caminho deixaria a instância
             * dizendo que está conectada sem estar.
             */
            
            $this->connected = true;

        } catch (\PDOException $e) {
            
            /**
             * Se o `PDO` falhar ao conectar ou ao aplicar os atributos,
             * repassamos o erro para `filterPdoExceptionMessage()` verificar se
             * há algum `onError()` correspondente que exiba uma mensagem de
             * erro programada pela aplicação. Em seguida, disparamos a
             * `Exception` do framework com `throwError()`.
             */
            
            $this->filterPdoExceptionMessage($e);
            $this->throwError();
        }
    }

    
    
    /**
     * 🟢 PADRONIZAÇÃO E ARMAZENAMENTO DO RESULTADO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método padroniza o array de resultado da consulta executada e o
     * grava nas propriedades `$lastResult` e `$namedResult`.
     *
     * Centralizamos essa normalização aqui para garantir que todos os métodos
     * de leitura, como `getResult()`, `getLastId()` e `hasResult()`, encontrem
     * sempre exatamente as mesmas cinco chaves e os mesmos tipos de dados, sem
     * depender de verificações extras em cada ponto de leitura.
     *
     *
     * _________________________________
     * @param array $result  
     *    Dados e metadados extraídos da consulta executada.
     * 
     * @param int|null|string $label  
     *    Rótulo da consulta, ou `null` caso nenhum rótulo tenha sido informado.
     * _________________________________
     * @return void
     */
    
    private function setResult(array $result, int|null|string $label): void
    {
        
        /**
         * Montamos o array final garantindo todas as chaves esperadas e
         * convertendo os tipos com segurança. Definimos também valores padrão
         * para o caso de alguma chave não ter vindo preenchida em `$result`,
         * evitando avisos de chave indefinida ou tipos inesperados na leitura.
         */
        
        $result = [
            'label' => $label,
            'data' => $result['data'] ?? [],
            'found' => (bool) ($result['found'] ?? null),
            'affectedRows' => (int) ($result['affectedRows'] ?? -1),
            'lastId' => (int) ($result['lastId'] ?? -1),
        ];

        
        
        /**
         * Se a consulta recebeu um rótulo, guardamos o resultado também em
         * `$this->namedResult[$label]`. Fazemos isso para preservar o acesso
         * individual a essa consulta mesmo quando outras forem executadas em
         * seguida e sobrescreverem `$this->lastResult`.
         */
        
        if ($label !== null) {
            $this->namedResult[$label] = $result;
        }
        
        
        
        /**
         * Por fim, atualizamos `$this->lastResult` com o resultado desta
         * consulta, para que os métodos de leitura consigam consultar sempre a
         * última execução.
         */
        
        $this->lastResult = $result;
    }

    
    
    /**
     * 🟢 VERIFICAÇÃO DE CONEXÃO ATIVA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método confere se a instância possui uma conexão ativa com o banco
     * de dados, lançando uma exceção caso não tenha.
     *
     * Centralizamos essa checagem aqui para não repetir o mesmo `if` em todos
     * os métodos que dependem de conexão aberta.
     *
     * ⚠️ IMPORTANTE
     * ┌───────────────────────────────────────────────────────────────────────┐
     * │ Vale notar que ele não retorna `true` ou `false`: funciona como uma   │
     * │ barreira de segurança que deixa o fluxo seguir quando está tudo certo │
     * │ e barra a operação quando o banco está desconectado.                  │
     * └───────────────────────────────────────────────────────────────────────┘
     *
     * 
     * _________________________________
     * @return void
     */
    
    private function isConnected(): void
    {
        if (!$this->connected) {
            throw new Exception(
                Message::get('DATABASE_DISCONNECTED'),
            );
        }
    }

    
    
    /**
     * 🟢 OBTENÇÃO DO ÚLTIMO ID GERADO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método consulta no `PDO` o último ID gerado automaticamente na
     * conexão atual e devolve esse valor padronizado como inteiro.
     *
     * Ele é chamado por `processResult()` logo após cada consulta ser
     * executada, preenchendo a chave `'lastId'` que depois fica disponível em
     * `getResult()` e `getLastId()`.
     *
     *
     * _________________________________
     * @return int
     *    Retorna o último ID positivo informado pelo PDO, ou `-1` quando o
     *    valor não for positivo ou ocorrer uma falha ao consultar o ID.
     */
    
    private function getLastInsertId(): int
    {
        
        /**
         * Colocamos a chamada dentro de um `try/catch` para `PDOException`
         * porque `lastInsertId()` é executado para toda consulta concluída,
         * inclusive em comandos como `SELECT`, `UPDATE` ou criação de tabelas.
         * Se o driver lançar uma exceção ao consultar o ID, capturamos essa
         * falha para não derrubar uma consulta que já deu certo, retornando
         * `-1` nesses casos.
         */
        
        try {
            
        
        
            /**
             * Consultamos `lastInsertId()` no PDO, que retorna o ID como uma
             * string numérica, como `'42'`. O retorno também pode ser `'0'`,
             * inclusive quando nenhum ID foi gerado ainda, ou `false` em caso
             * de falha.
             */
            
            $lastId = $this->pdo->lastInsertId();
            
            
            /**
             * Usamos a comparação `<= 0` para capturar valores iguais ou
             * menores que `0`, além de `false` e string vazia, retornados pelo
             * `PDO`, convertendo-os para `-1`. Fazemos isso para sinalizar
             * claramente que não existe um ID válido, já que o método sempre
             * retorna um número inteiro.
             * 
             * Quando houver um ID válido, o retorno tipado como `int` converte
             * a string numérica para inteiro automaticamente.
             * 
             * Qualquer falha de `PDOException` também retorna `-1`.
             */
            
            return $lastId <= 0 ? -1 : $lastId;
        } catch (\PDOException $e) {
            return -1;
        }
    }
    
    

    /**
     * 🟢 EXTRAÇÃO DO RESULTADO DA CONSULTA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método lê a resposta deixada no `PDOStatement` após a execução da
     * consulta e organiza os registros e metadados em um array.
     *
     * Ele é chamado em `query()` logo depois de `execute()` concluir, servindo
     * de ponte entre o retorno bruto do `PDO` e `setResult()`. Com isso, tanto
     * consultas de leitura quanto comandos de escrita saem daqui com a mesma
     * estrutura básica pronta para armazenamento.
     *
     *
     * _________________________________
     * @param \PDOStatement $pdo  
     *    Objeto `PDOStatement` da consulta já executada (isso é importante),
     *    usado para extrair os resultados da operação.
     * _________________________________
     * @return array  
     *    Retorna um array estruturado pronto para ser normalizado e armazenado
     *    por `setResult()`.
     */
    
    private function processResult(\PDOStatement $pdo): array
    {
        /**
         * Inicializamos o array já com `data` vazio e `found` como `false`
         * porque nem todo comando SQL devolve linhas. Assim, comandos como
         * `INSERT`, `UPDATE` ou `DELETE` já partem com esses valores padrão
         * seguros.
         */
        
        $result = [
            'data' => [],
            'found' => false,
        ];

        
        
        /**
         * Verificamos `columnCount() > 0` para descobrir se o comando retornou
         * um conjunto tabular de colunas, como em `SELECT` ou `SHOW`, sem
         * precisar analisar o texto da string SQL. Mesmo que um `SELECT` não
         * encontre nenhum registro, o banco ainda informa as colunas da tabela,
         * enquanto comandos como `UPDATE` ou `DELETE` retornam `0` colunas.
         */
        
        if ($pdo->columnCount() > 0) {
            
        
        
            /**
             * Para os casos em que há o registro de uma coluna ou mais, lemos
             * cada registro com `FETCH_ASSOC` até `fetch()` retornar `false`,
             * indicando que os resultados acabaram.
             * 
             * Escolhemos `FETCH_ASSOC` para trazer apenas os nomes das colunas
             * como chaves do array, evitando duplicar cada campo em índices
             * numéricos como o modo padrão do `PDO` faria.
             * 
             * Armazenamos cada linha na chave `data`, que é quem armazena os
             * resultados da consulta em si.
             */
            
            while (($row = $pdo->fetch(\PDO::FETCH_ASSOC)) !== false) {
                $result['data'][] = $row;
            }

            
            
            /**
             * Como esta consulta devolve linhas, definimos `found` conferindo
             * se `data` não está vazio. Aqui, por se tratar geralmente de um
             * `SELECT`, não confiamos em `rowCount()` porque o comportamento
             * dele varia entre drivers e no SQLite costuma retornar `0` mesmo
             * quando há registros. Neste caso, é melhor simplesmente verificar
             * se o array `$result['data']` está vazio ou não.
             */
            
            $result['found'] = !empty($result['data']);
         
            
            
        /**
         * Quando `columnCount()` é `0`, o comando não devolve linhas de
         * resultado. Neste caso, provavelmente estaremos lidando com um
         * `INSERT`, `UPDATE` ou `DELETE`, o que significa que `data` não
         * armazenará nenhum resultado. Como não estamos lidando com `SELECT`
         * aqui, é seguro checar `rowCount()` para determinar se houve ou não
         * registros afetados.
         */
        
        } else {
            $result['found'] = $pdo->rowCount() > 0;
        }

        
        
        /**
         * Completamos o array registrando o número de linhas afetadas e o
         * último ID gerado pela conexão, entregando tudo pronto para
         * `setResult()` finalizar e armazenar.
         */
        
        $result['affectedRows'] = $pdo->rowCount();
        $result['lastId'] = $this->getLastInsertId();

        return $result;
    }

    
    
    /**
     * 🟢 LEITURA E VALIDAÇÃO DA CONFIGURAÇÃO DO BANCO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método localiza, valida e retorna os dados de uma configuração
     * cadastrada no arquivo `/app/config/database.php`.
     *
     * Chamamos este método nos construtores das classes de driver, como
     * `MysqlDatabase` e `SqliteDatabase`. Para carregar uma configuração do
     * arquivo indicado em `CONFIG_FILE`, passamos o nome dela ao criar a
     * instância, como em `new MysqlDatabase('nome-da-config')` ou `new
     * SqliteDatabase('nome-da-config')`.
     * 
     * Centralizamos essa leitura aqui para evitar repetir a busca do arquivo e
     * as validações de chaves em cada driver.
     *
     *
     * _________________________________
     * @param ?string $dbConfigName  
     *    Nome da configuração cadastrada em `/app/config/database.php`, ou
     *    `null` quando a conexão será configurada manualmente pelos setters.
     * _________________________________
     * @return array  
     *    Retorna um array com as chaves da configuração encontrada para o
     *    driver preencher os setters da conexão, ou um array vazio `[]` quando
     *    `$dbConfigName` for `null`. Se um nome foi informado, lança uma
     *    exceção quando o arquivo não existir, a configuração não estiver
     *    cadastrada ou faltar alguma chave obrigatória.
     */
    
    private function resolveDatabaseConfig(?string $dbConfigName = null): array
    {
        /**
         * Se nenhum nome de configuração foi informado, retornamos um array
         * vazio `[]` sem exigir o arquivo de configurações. Assim, quem
         * pretende configurar a conexão manualmente pelos setters recebe a
         * instância vazia pronta para uso.
         */

        if ($dbConfigName === null) {
            return [];
        }


        /**
         * Quando um nome foi informado, precisamos encontrar o arquivo para
         * carregar a configuração solicitada. Se ele não existir, lançamos uma
         * exceção apontando o caminho ausente, em vez de continuar como se a
         * aplicação tivesse escolhido configurar a conexão manualmente.
         */

        if (!file_exists(self::CONFIG_FILE)) {
            throw new Exception(
                Message::get('CONFIG_FILE_NOT_FOUND'),
                [self::CONFIG_FILE]
            );
        }

        
        
        /**
         * Importamos o arquivo de configurações com `require` para obter o
         * array de conexões da aplicação. Usamos `require` em vez de
         * `require_once` porque podemos instanciar vários bancos na mesma
         * requisição e, da segunda chamada em diante, `require_once` retornaria
         * `true` no lugar do array.
         */
        
        $configData = require self::CONFIG_FILE;

        
        
        /**
         * Conferimos se o nome de configuração existe entre as chaves
         * carregadas do arquivo. Se não existir, já lançamos uma exceção
         * apontando o nome ausente, evitando que o código tente ler um índice
         * inexistente mais adiante.
         */
        
        if (!isset($configData[$dbConfigName])) {
            throw new Exception(
                Message::get('DATABASE_CONFIG_DOESNT_EXIST'),
                [$dbConfigName]
            );
        }

        
        
        /**
         * Separamos o bloco da configuração solicitada e percorremos a lista
         * `$requiredConfigKeys` informada na classe do driver atual para
         * garantir que todas as chaves esperadas foram declaradas.
         *
         * Usamos `array_key_exists()` em vez de `isset()` ou `empty()` porque
         * algumas chaves, como `port` e `password` no MySQL, podem ficar vazias
         * quando não forem necessárias, mas ainda assim precisam existir no
         * array da configuração.
         */
        
        $config = $configData[$dbConfigName];

        foreach ($this->requiredConfigKeys as $property) {
            if (!array_key_exists($property, $config)) {
                throw new Exception(
                    Message::get('DATABASE_CONFIG_MISSING_REQUIRED_PROPERTY'),
                    [$dbConfigName, $property]
                );
            }
        }

        return $config;
    }


    
    /**
     * 🟢 FILTRAGEM E PREPARAÇÃO DE EXCEÇÕES DO PDO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método é chamado nos blocos `catch` de `establishConnection()` e
     * `query()` sempre que o `PDO` lança uma `PDOException` ao conectar ou
     * executar uma consulta devido a alguma falha ou configuração. Ele serve
     * para preparar os dados do erro logo antes da execução de um
     * `throwError()`, que é sempre chamado após ele.
     *
     * Ele analisa a exceção recebida e decide se a falha usará uma mensagem
     * personalizada cadastrada via `onError()` ou a mensagem original do `PDO`,
     * deixando `failMessage`, `additionalData`, `messageFlagValues` e
     * `allowMessageDisplay` prontos para construir a exceção final do
     * framework.
     *
     *
     * _________________________________
     * @param \PDOException $e  
     *    Exceção lançada pelo `PDO` contendo o código SQLSTATE, os detalhes do
     *    driver e a mensagem original da falha.
     * _________________________________
     * @return void
     */
    
    private function filterPdoExceptionMessage(\PDOException $e): void
    {
        
        /**
         * Começamos reiniciando `$this->allowMessageDisplay` como `false` para
         * partir do estado mais seguro possível. Assim, se uma falha anterior
         * tiver sido liberada por `onError()`, essa permissão não vaza para um
         * erro novo sem tratamento, evitando expor detalhes internos do banco
         * quando a configuração de rota `displayErrors` estiver `false`.
         */
        
        $this->allowMessageDisplay = false;
        
        
        
        /**
         * Extraímos três identificadores diferentes da exceção porque cada
         * banco reporta falhas de um jeito e `onError()` pode filtrar por
         * qualquer um deles:
         */
        
        
        /**
         * O código SQLSTATE padrão em `$e->getCode()`.
         */
        
        $sqlstateErrorCode = $e->getCode();
        
        
        
        $info = $e->errorInfo ?? [];
        
        /**
         * O código numérico próprio do driver em `$info[1]`, como `1062` no
         * MySQL.
         */
        
        $driverErrorCode = $info[1] ?? null;
        
        
        
        /**
         * A descrição detalhada do erro em `$info[2]`, que costuma conter o
         * nome da constraint ou coluna violada, usando a mensagem da exceção
         * como fallback se `$info[2]` não existir.
         */
        
        $constraintErrorCode = $info[2] ?? $e->getMessage();

        
        
        /**
         * Se `$this->errorChain` estiver vazio, nenhum `onError()` foi
         * cadastrado para esta operação. Nesse caso, preparamos os dados da
         * própria `PDOException` e encerramos o método.
         */
        
        if (empty($this->errorChain)) {
            
            /**
             * Limpamos tabulações e quebras de linha para manter a mensagem
             * legível em uma linha contínua
             */
            
            $message = preg_replace('/[\t\n]+/u', ' ', trim($e->getMessage()));
            
            
            
            /**
             * Escapamos `{{` com `\{{` porque a classe `Exception` interpreta
             * `{{...}}` como placeholder de substituição. Uma mensagem do banco
             * contendo `{{` neste caso dispararia erro de placeholder inválido
             * do próprio `Exception`.
             */
            
            $message = str_replace('{{', '\\{{', $message);
            
            
            
            /**
             * Definimos `$failMessage` para deixá-lo disponível para
             * throwError().
             */
            
            $this->failMessage = [$message, $e->getCode()];
            
            
            
            /**
             * Zeramos `$additionalData` e `$messageFlagValues` para não
             * carregar sobras de erros anteriores.
             */
            
            $this->additionalData = [];
            $this->messageFlagValues = [];

            return;
        }

        
        
        /**
         * Porém, quando há tratamentos em `$this->errorChain`, precisamos
         * percorrer a fila na ordem em que foram registrados por `onError()`
         * até encontrar o tratamento que corresponde à falha.
         */
        
        foreach ($this->errorChain as $key => $function) {
            
        
        
            /**
             * Cada item de `$this->errorChain` é uma função anônima criada por
             * `onError()`. Ao executá-la com `$function()`, ela preenche
             * `$this->failMessage`, `$this->additionalData` e
             * `$this->messageFlagValues` com a mensagem, o código esperado e os
             * dados extras cadastrados naquele tratamento.
             *
             * Logo em seguida, removemos esse item da fila com `unset()` porque
             * este método também é chamado por `connect()`, que não passa pelo
             * `finally` de `submit()`. Assim, se a aplicação capturar uma falha
             * de conexão e continuar usando esta instância, os tratamentos já
             * avaliados não ficam sobrando na fila para consultas futuras.
             */
            
            $function();
            unset($this->errorChain[$key]);

            
            
            /**
             * Por fim, deixamos `$message` inicializada vazia para eventual uso
             * no fallback abaixo e extraímos o código esperado de
             * `$this->failMessage[1]` para `$code`, pois é esse valor que vamos
             * comparar com a falha do `PDO` no `if` a seguir.
             */
            
            $message = '';
            $code = $this->failMessage[1];

            
            
            /**
             * Comparamos `$code` com o SQLSTATE, com o código numérico do
             * driver ou como trecho presente na descrição em
             * `$constraintErrorCode`, exigindo que `$code` não seja vazio antes
             * de usar `strpos()`.
             *
             * Assim que um tratamento corresponde, liberamos
             * `$allowMessageDisplay = true` para o `ErrorHandler` poder exibir
             * a mensagem personalizada mesmo em produção e encerramos aqui,
             * mantendo esse primeiro tratamento compatível como vencedor.
             */
            
            if (
                $sqlstateErrorCode == $code ||
                $driverErrorCode == $code ||
                (
                    $code
                    && is_string($constraintErrorCode)
                    && strpos($constraintErrorCode, (string)$code) !== false
                )
            ) {
                $this->allowMessageDisplay = true;
                return;
            }

            $message = preg_replace('/[\t\n]+/u', ' ', trim($e->getMessage()));
            $message = str_replace('{{', '\\{{', $message);
            $this->failMessage = [$message, $e->getCode()];
            $this->additionalData = [];
            $this->messageFlagValues = [];
        }
    }
}



/**
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * ◀️ VOLTAR A UM TÓPICO ANTERIOR:
 * 🔗 LINK: /galastri/modules/database/MysqlDatabase.php
 * 🔗 LINK: /galastri/modules/database/SqliteDatabase.php
 */