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
 * Em vez de instanciá-la diretamente com `new SqliteDatabase()`, costumamos
 * obtê-la por meio de `Database::createSqlite()`. Podemos informar o nome de
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
     *
     * Esta classe não possui constantes.
     */

    const DRIVER = 'sqlite';



    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                             PROPRIEDADES                              ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     *
     * Esta classe não possui propriedades.
     */
    
    private array $requiredConfigKeys = ['location'];
    private array $requiredProperties = ['location'];



    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                                MÉTODOS                                ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     */

    public function __construct(?string $dbConfigName = null)
    {
        $config = $this->resolveDatabaseConfig($dbConfigName);

        if (empty($config)) {
            return;
        }

        $this->setLocation($config['location']);
        $this->setOptions($config['options'] ?? []);
    }

    public function connect(): self
    {
        $createPdo = function (): \PDO {
            return new \PDO(
                self::DRIVER.':'.$this->location,
                null,
                null,
                $this->options
            );
        };

        $this->establishConnection($createPdo);

        return $this;
    }
}
