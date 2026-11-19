<?php
/**
 * -----------------------------------------------------------------------------
 * 📂
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║           ARQUIVO /galastri/modules/database/SqliteDatabase.php           ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 * Este arquivo define a classe responsável por criar e gerenciar conexões com
 * bancos de dados SQLite no framework.
 * ─────────────────────────────────────────────────────────────────────────────
 */



/**
 * 🟢 NAMESPACE DA CLASSE
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * A classe `SqliteDatabase` faz parte dos drivers internos do módulo de banco
 * de dados do framework.
 */

namespace galastri\modules\database;



/**
 * Esta classe cria e gerencia conexões com bancos de dados SQLite no framework.
 *
 * Diferente de bancos que rodam em um servidor separado, como o MySQL, o SQLite
 * armazena os dados diretamente em um arquivo no servidor ou na memória, usando
 * o identificador `':memory:'`. Por isso, não precisamos informar endereço de
 * servidor, porta, usuário ou senha para abrir a conexão: basta indicar onde o
 * banco está localizado e, se necessário, passar opções extras para o `PDO`.
 *
 * Em vez de instanciá-la diretamente com `new SqliteDatabase()`, usamos
 * `Database::createSqlite()` pra obter a instância. Podemos informar o nome de
 * uma configuração já cadastrada em `/app/config/database.php` ou deixar sem
 * argumento para preencher os dados da conexão manualmente antes de chamar
 * `connect()`.
 *
 * ✔️ EXEMPLO
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ Usando uma configuração chamada `'sqlite-example'` já cadastrada em       │
 * │ `/app/config/database.php`:                                               │
 * │                                                                           │
 * │ ┌─────────────────────────────────────────────────────┐                   │
 * │ │ $db = Database::createSqlite('sqlite-example');     │                   │
 * │ │ $db->connect();                                     │                   │
 * │ └─────────────────────────────────────────────────────┘                   │
 * │                                                                           │
 * │ Ou criando uma instância vazia pra definir o caminho manualmente na hora: │
 * │                                                                           │
 * │ ┌─────────────────────────────────────────────────────┐                   │
 * │ │ $db = Database::createSqlite();                     │                   │
 * │ │ $db->setLocation(':memory:')->connect();            │                   │
 * │ └─────────────────────────────────────────────────────┘                   │
 * └───────────────────────────────────────────────────────────────────────────┘
 *
 * 💡 OBSERVAÇÃO
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ Quando usamos `':memory:'`, criamos um banco temporário que existe apenas │
 * │ enquanto mantemos aquela conexão aberta. Ao encerrá-la, perdemos os dados │
 * │ desse banco. Pra guardar os dados entre conexões, usamos um arquivo.      │
 * └───────────────────────────────────────────────────────────────────────────┘
 *
 * Nos dois casos, depois de conectar com `connect()`, temos à disposição os
 * recursos pra preparar e enviar consultas, vincular parâmetros, controlar
 * transações, tratar erros e ler os resultados retornados pelo banco.
 */

class SqliteDatabase
{
    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                                TRAITS                                 ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     */
    
    /**
     * 🟢 ESTRUTURA COMPARTILHADA DE BANCO DE DADOS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Esta trait reúne toda a lógica compartilhada pelas classes de banco de
     * dados do framework, evitando repetir esse código em cada driver e deixando
     * nesta classe apenas o que é específico do SQLite.
     *
     * Acesse o arquivo da trait `CoreDatabase` pra ver como ela funciona e
     * depois volte aqui:
     * 🔗 LINK: /galastri/modules/database/traits/CoreDatabase.php
     */
    
    use traits\CoreDatabase;
    
    
    
    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                              CONSTANTES                               ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     */
    
    /**
     * 🟢 NOME DO DRIVER PDO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Esta constante guarda o prefixo do driver utilizado pelo `PDO` para
     * conexões SQLite.
     *
     * Ao abrir a conexão, precisamos informar ao `PDO` qual driver usar e onde
     * está o banco. Reunimos essas informações em uma string de conexão chamada
     * DSN, em que esse identificador fica como prefixo, como em
     * `'sqlite:/caminho/do/banco.sqlite'` ou `'sqlite::memory:'`.
     *
     *
     * _________________________________
     * @var string
     */
    
    private const DRIVER = 'sqlite';



    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                             PROPRIEDADES                              ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     */
    
    /**
     * 🟢 CHAVES OBRIGATÓRIAS NA CONFIGURAÇÃO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Esta propriedade lista os nomes das chaves que precisam estar declaradas
     * na configuração do SQLite em `/app/config/database.php`.
     *
     * Quando carregamos uma conexão desse arquivo, conferimos por esta lista se
     * o bloco correspondente informou a chave `'location'`. A chave `'options'`
     * fica fora porque é opcional na configuração.
     * 
     * ⚠️ IMPORTANTE
     * ┌───────────────────────────────────────────────────────────────────┐
     * │ Esta propriedade é usada na trait CoreDatabase.                   │
     * └───────────────────────────────────────────────────────────────────┘
     *
     *
     * _________________________________
     * @var array<string>
     */
    
    private array $requiredConfigKeys = ['location'];
    
    
    
    /**
     * 🟢 PROPRIEDADES OBRIGATÓRIAS NA CONEXÃO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Esta propriedade lista os nomes das propriedades que não podem estar
     * vazias no momento de abrir a conexão com o SQLite.
     *
     * Antes de instanciar o `PDO`, conferimos por esta lista se `location` foi
     * preenchida, seja pelo arquivo `/app/config/database.php` ou pelo setter
     * `setLocation()`. A propriedade `$options` fica fora porque é opcional.
     *
     * ⚠️ IMPORTANTE
     * ┌───────────────────────────────────────────────────────────────────┐
     * │ Esta propriedade é usada na trait CoreDatabase.                   │
     * └───────────────────────────────────────────────────────────────────┘
     *
     *
     * _________________________________
     * @var array<string>
     */
    
    private array $requiredProperties = ['location'];



    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                                MÉTODOS                                ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     */

    /**
     * 🟢 INICIALIZAÇÃO DA INSTÂNCIA SQLITE
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método inicializa a instância de `SqliteDatabase`. Existem duas
     * formas de inicializar:
     * 
     * 1. Carregando automaticamente uma configuração de
     *    `/app/config/database.php`, informando a chave da configuração do
     *    banco de dados que se quer acessar no parâmetro `$dbConfigName`, como
     *    `Database::createSqlite('nome_da_config')`.
     * 
     * 2. Não informando nada para gerar a instância vazia, deixando a conexão
     *    para ser configurada manualmente pelos setters depois.
     *
     * ⚠️ IMPORTANTE
     * ┌───────────────────────────────────────────────────────────────────────┐
     * │ Gerar a instância não significa conectar-se ao banco de dados. O      │
     * │ construtor apenas prepara os dados da conexão; a abertura real do     │
     * │ banco só acontece quando chamamos `connect()`.                        │
     * └───────────────────────────────────────────────────────────────────────┘
     *
     *
     * _________________________________
     * @param ?string $dbConfigName  
     *    Nome da configuração cadastrada em `/app/config/database.php`, ou
     *    `null` para configurar a conexão manualmente pelos setters.
     */
    
    public function __construct(?string $dbConfigName = null)
    {
        
        /**
         * Buscamos a configuração em `/app/config/database.php`. Se
         * `$dbConfigName` for `null`, recebemos um array vazio de
         * `resolveDatabaseConfig()`.
         * 
         * Se o nome não existir ou faltar `location`, uma exceção é lançada.
         */
        
        $config = $this->resolveDatabaseConfig($dbConfigName);

        
        
        /**
         * Quando `$config` vem vazio, significa que `$dbConfigName` não foi
         * informado. Encerramos o construtor aqui para que a aplicação defina
         * os dados manualmente pelos setters depois.
         */
        
        if (empty($config)) {
            return;
        }

        
        
        /**
         * Quando `$config` for, de fato, uma configuração válida, preenchemos a
         * localização obrigatória do banco e as opções extras do `PDO`. Como
         * `options` é opcional no arquivo de configuração, usamos um array
         * vazio como fallback quando ela não estiver definida.
         */
        
        $this->setLocation($config['location']);
        $this->setOptions($config['options'] ?? []);
    }

    
    
    /**
     * 🟢 ABERTURA DA CONEXÃO SQLITE
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método abre a conexão com o banco de dados SQLite usando a
     * localização e as opções configuradas na instância.
     *
     * Como cada driver monta seu próprio DSN e passa argumentos específicos ao
     * `PDO`, preparamos aqui apenas a criação da instância `PDO` pro SQLite e
     * delegamos as validações, a configuração de atributos e o tratamento de
     * erros para `establishConnection()` do `CoreDatabase`.
     *
     *
     * _________________________________
     * @return self  
     *    Retorna a própria instância para encadeamento de chamadas. Lança uma
     *    exceção se já houver uma conexão ativa, se `$location` estiver vazio
     *    ou se o `PDO` falhar ao abrir o banco.
     */
    
    public function connect(): self
    {
        
        /**
         * Preparamos a criação do `PDO` dentro de uma função anônima, sem
         * executá-la agora, porque é `establishConnection()`, da trait
         * `CoreDatabase`, quem faz as validações iniciais e executa essa
         * abertura dentro do seu `try/catch`. Assim, qualquer falha do `PDO` ao
         * abrir o banco é capturada lá para aplicar os tratamentos de
         * `onError()`, se houver, sem precisarmos repetir essa estrutura aqui.
         *
         * Usamos a constante `DRIVER` como prefixo do DSN, que completamos com
         * a localização do banco para indicar ao PDO qual banco abrir, como
         * `'sqlite:/caminho/do/banco.sqlite'`.
         * 
         * Como o SQLite não usa autenticação de servidor, passamos `null` para
         * usuário e senha, repassando em seguida as opções extras em
         * `$this->options`.
         */
        
        $createPdo = function (): \PDO {
            return new \PDO(
                self::DRIVER.':'.$this->location,
                null,
                null,
                $this->options
            );
        };

        
        
        /**
         * Entregamos a função para `establishConnection()`, que faz as
         * checagens iniciais, abre a conexão executando `$createPdo()`, aplica
         * os atributos padrão do framework no `PDO` e marca a instância como
         * conectada.
         */
        
        $this->establishConnection($createPdo);

        return $this;
    }
}



/**
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * ◀️ VOLTAR A UM TÓPICO ANTERIOR:
 * 🔗 LINK: /galastri/modules/Database.php
 */
