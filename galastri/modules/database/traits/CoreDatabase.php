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
     *
     * Esta trait não possui constantes.
     */
    
    
    
    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                             PROPRIEDADES                              ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     */
    
    /**
     * 🟢 CAMINHO DO ARQUIVO DE CONFIGURAÇÕES
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Esta propriedade guarda a localização do arquivo de configurações de
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
    
    private string $configFile = PROJECT_DIR.'/app/config/database.php';
    
    
    
    /**
     * 🟢 INSTÂNCIA ATIVA DO PDO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Esta propriedade armazena a instância ativa da conexão `\PDO` com o
     * banco de dados.
     *
     * Ela começa como `null`, recebe o objeto `\PDO` quando a conexão é aberta
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
     * O array é limpo quando chamamos `cancel()` ou `disconnect()`.
     *
     *
     * _________________________________
     * @var array<int|string,array>
     */
    
    private array $namedResult = [];
    
    
    
    /**
     * 🟢 ÚLTIMO RESULTADO SEM RÓTULO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Esta propriedade armazena o resultado da última consulta executada sem
     * rótulo em `query()` (quando `$label` é `null`).
     *
     * Diferente de `$namedResult`, ela guarda apenas um resultado por vez: cada
     * nova consulta sem rótulo substitui o conteúdo anterior. Quando chamamos
     * `getResult()`, `getLastId()` ou `hasResult()` sem informar um rótulo, é
     * daqui que os dados são lidos.
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
     * O array é limpo quando chamamos `cancel()` ou `disconnect()`.
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
     * é passada ao `\PDO` ao abrir a conexão em drivers com autenticação, como
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
     * é passada ao `\PDO` ao abrir a conexão em drivers com autenticação, como
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
     * construtor do `\PDO` na abertura da conexão (como `[\PDO::ATTR_TIMEOUT =>
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
     * Ao tratar uma `\PDOException`, ela recebe os dados definidos no `onError()`
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
    
    
    
    
    private bool $allowMessageDisplay = false;

    
    
    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                                MÉTODOS                                ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     */
    
    public function setHost(string $host): self
    {
        $this->host = $host;
        return $this;
    }

    public function setPort(string $port): self
    {
        $this->port = $port;
        return $this;
    }

    public function setDatabase(string $database): self
    {
        $this->database = $database;
        return $this;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function setOptions(array $options): self
    {
        $this->options = $options;
        return $this;
    }

    public function setLocation(string $location): self
    {
        $this->location = $location;
        return $this;
    }

    abstract public function connect(): self;

    public function disconnect(): self
    {
        $this->pdo = null;
        $this->connected = false;
        $this->queryChain = [];
        $this->errorChain = [];
        $this->bind = [];
        $this->lastResult = [];
        $this->namedResult = [];
        $this->additionalData = [];
        $this->messageFlagValues = [];

        return $this;
    }

    public function begin(): self
    {
        $this->isConnected();

        if ($this->pdo->inTransaction()) {
            throw new Exception(Message::get('DATABASE_TRANSACTION_ALREADY_ACTIVE'));
        }

        $this->pdo->beginTransaction();

        return $this;
    }

    public function cancel(): self
    {
        $this->isConnected();
        $this->pdo->rollBack();
        $this->queryChain = [];
        $this->errorChain = [];
        $this->bind = [];
        $this->lastResult = [];
        $this->namedResult = [];
        $this->additionalData = [];
        $this->messageFlagValues = [];

        return $this;
    }

    public function commit(): self
    {
        $this->isConnected();
        $this->pdo->commit();

        return $this;
    }

    public function query(string $queryString, int|null|string $label = null): self
    {
        $this->isConnected();

        $this->queryChain[] = function () use ($queryString, $label) {
            if ($label === null) {
                $this->lastResult = [];
            } else {
                unset($this->namedResult[$label]);
            }

            $pdoTypes = [
                'string' => \PDO::PARAM_STR,
                'int' => \PDO::PARAM_INT,
                'float' => \PDO::PARAM_STR,
                'bool' => \PDO::PARAM_BOOL,
                'null' => \PDO::PARAM_NULL,
                'resource' => \PDO::PARAM_LOB,
            ];

            try {
                $pdo = $this->pdo->prepare($queryString);

                foreach ($this->bind as $key => &$value) {
                    $type = Tools::typeOf($value);

                    if (isset($pdoTypes[$type])) {
                        $pdo->bindParam($key, $value, $pdoTypes[$type]);

                    } elseif (empty($value) && !in_array($value, [0, 0.0, '0'], true)) {
                        $pdo->bindParam($key, $value, \PDO::PARAM_NULL);
                        $value = null;

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
                unset($value);

                if ($pdo->execute()) {
                    $result = $this->processResult($pdo);

                    $this->setResult($result, $label);
                }

            } catch (\PDOException $e) {
                $this->filterPdoExceptionMessage($e);
                $this->throwError();
            }
        };

        return $this;
    }

    public function bind(array|int|string $bind, mixed $value = null): self
    {
        $this->isConnected();

        if (!is_array($bind)) {
            $bind = [$bind => $value];
        }

        foreach ($bind as $key => $bindValue) {
            $this->bind[$key] = $bindValue;
        }

        return $this;
    }

    public function clearBind(): self
    {
        $this->bind = [];

        return $this;
    }

    private function throwError(): void
    {
        if (Exception::hasPlaceholders($this->failMessage[0])) {
            $exception = new Exception(
                $this->failMessage,
                $this->messageFlagValues,
                $this->additionalData
            );
        } else {
            $exception = new Exception(
                $this->failMessage,
                $this->additionalData
            );
        }

        $exception->allowMessageDisplay = $this->allowMessageDisplay;

        throw $exception;
    }

    public function onError(
        array|string $message,
        int|string $code,
        array $additionalData = []): self
    {
        $this->errorChain[] = function () use ($message, $code, $additionalData) {
            if (Tools::typeOf($message) !== 'array') {
               $message = [$message];
            }

            $this->additionalData = $additionalData;
            $this->failMessage = [array_shift($message), $code];
            $this->messageFlagValues = $message ?? [];
        };

        return $this;
    }

    public function submit(): self
    {
        $this->isConnected();

        try {
            foreach ($this->queryChain as $function) {
                $function();
            }
        } finally {
            $this->queryChain = [];
            $this->errorChain = [];
        }

        return $this;
    }

    public function getResult(int|null|string $label = null): array
    {
        $this->isConnected();

        if ($label === null) {
            return $this->lastResult;
        }

        if (!isset($this->namedResult[$label])) {
            throw new Exception(
                Message::get('DATABASE_RESULT_DOESNT_EXIST'),
                [$label]
            );
        }

        return $this->namedResult[$label];
    }

    public function getLastId(int|null|string $label = null): int
    {
        $this->isConnected();

        if ($label === null) {
            return $this->lastResult['lastId'] ?? -1;
        }

        return $this->namedResult[$label]['lastId'] ?? -1;
    }

    public function hasResult(int|null|string $label = null): bool
    {
        $this->isConnected();

        if ($label === null) {
            return !empty($this->lastResult) && ($this->lastResult['found'] ?? false);
        }

        return isset($this->namedResult[$label]['found']) && $this->namedResult[$label]['found'];
    }

    private function establishConnection(callable $createPdo): void
    {
        if ($this->connected) {
            throw new Exception(Message::get('DATABASE_ALREADY_CONNECTED'));
        }

        try {
            foreach ($this->requiredProperties as $property) {
                if ($this->$property === '') {
                    throw new Exception(
                        Message::get('DATABASE_MISSING_REQUIRED_PROPERTY'),
                        [$property]
                    );
                }
            }
            $this->pdo = $createPdo();

            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);

            $this->connected = true;

        } catch (\PDOException $e) {
            $this->filterPdoExceptionMessage($e);
            $this->throwError();
        }
    }

    private function setResult(array $result, int|null|string $label): void
    {
        $result = [
            'label' => $label,
            'data' => $result['data'] ?? [],
            'found' => (bool)($result['found'] ?? null),
            'affectedRows' => (int)($result['affectedRows'] ?? -1),
            'lastId' => (int)$result['lastId'] ?? -1,
        ];

        if ($label === null) {
            $this->lastResult = $result;
        } else {
            $this->namedResult[$label] = $result;
        }
    }

    private function isConnected(): void
    {
        if (!$this->connected) {
            throw new Exception(
                Message::get('DATABASE_DISCONNECTED'),
            );
        }
    }

    private function getLastInsertId(): int
    {
        try {
            $lastId = $this->pdo->lastInsertId();
            return $lastId == 0 ? -1 : $lastId;
        } catch (\PDOException $e) {
            return -1;
        }
    }

    private function processResult(\PDOStatement $pdo): array
    {
        $result = [
            'data' => [],
            'found' => false,
        ];

        if ($pdo->columnCount() > 0) {
            while (($row = $pdo->fetch(\PDO::FETCH_ASSOC)) !== false) {
                $result['data'][] = $row;
            }

            $result['found'] = !empty($result['data']);
        } else {
            $result['found'] = $pdo->rowCount() > 0;
        }

        $result['affectedRows'] = $pdo->rowCount();
        $result['lastId'] = $this->getLastInsertId();

        return $result;
    }

    private function resolveDatabaseConfig(?string $dbConfigName = null): array
    {
        if ($dbConfigName === null || !file_exists($this->configFile)) {
            return [];
        }

        $configData = require $this->configFile;

        if (!isset($configData[$dbConfigName])) {
            throw new Exception(
                Message::get('DATABASE_CONFIG_DOESNT_EXIST'),
                [$dbConfigName]
            );
        }

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


    private function filterPdoExceptionMessage(\PDOException $e): void
    {
        $this->allowMessageDisplay = false;
        
        $info = $e->errorInfo ?? [];
        $sqlstateErrorCode = $e->getCode();
        $driverErrorCode = $info[1] ?? null;
        $constraintErrorCode = $info[2] ?? $e->getMessage();

        if (empty($this->errorChain)) {
            $message = preg_replace('/[\t\n]+/u', ' ', trim($e->getMessage()));
            $message = str_replace('{{', '\\{{', $message);
            $this->failMessage = [$message, $e->getCode()];
            $this->additionalData = [];
            $this->messageFlagValues = [];

            return;
        }

        foreach ($this->errorChain as $key => $function) {
            $function();
            unset($this->errorChain[$key]);

            $message = $this->failMessage[0];
            $code = $this->failMessage[1];

            if (
                $sqlstateErrorCode == $code ||
                $driverErrorCode == $code ||
                (
                    $code
                    && is_string($constraintErrorCode)
                    && strpos($constraintErrorCode, (string)$code) !== false)
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
