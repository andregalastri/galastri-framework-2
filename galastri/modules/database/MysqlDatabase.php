<?php
/**
 * -----------------------------------------------------------------------------
 * 📂
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║           ARQUIVO /galastri/modules/database/MysqlDatabase.php            ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 * Este arquivo define a classe responsável por criar e gerenciar conexões com
 * bancos de dados MySQL no framework.
 * ─────────────────────────────────────────────────────────────────────────────
 */



/**
 * 🟢 NAMESPACE DA CLASSE
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * A classe `MysqlDatabase` faz parte dos módulos de banco de dados do
 * framework.
 */

namespace galastri\modules\database;



/**
 * Esta classe cria e gerencia conexões com bancos de dados MySQL no framework.
 *
 * Diferente de bancos baseados em arquivo ou memória, como o SQLite, o MySQL
 * roda em um servidor próprio e exige autenticação. Por isso, para abrir a
 * conexão, precisamos informar múltiplas informações para o `PDO`, quando forem
 * necessárias.
 *
 * Criamos a instância com `new MysqlDatabase()`. Podemos informar ao construtor
 * o nome de uma configuração já cadastrada em `/app/config/database.php` ou
 * deixar sem argumento para preencher os dados da conexão manualmente antes de
 * chamar `connect()`.
 *
 * ✔️ EXEMPLO
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ Usando uma configuração chamada `'mysql-example'` já cadastrada em        │
 * │ `/app/config/database.php`:                                               │
 * │                                                                           │
 * │ ┌─────────────────────────────────────────────────────┐                   │
 * │ │ $db = new MysqlDatabase('mysql-example');           │                   │
 * │ │ $db->connect();                                     │                   │
 * │ └─────────────────────────────────────────────────────┘                   │
 * │                                                                           │
 * │ Ou criando uma instância vazia pra definir os dados manualmente na hora:  │
 * │                                                                           │
 * │ ┌─────────────────────────────────────────────────────┐                   │
 * │ │ $db = new MysqlDatabase();                          │                   │
 * │ │ $db->setHost('localhost')                           │                   │
 * │ │    ->setDatabase('minha_aplicacao')                 │                   │
 * │ │    ->setUsername('root')                            │                   │
 * │ │    ->setPassword('123456')                          │                   │
 * │ │    ->connect();                                     │                   │
 * │ └─────────────────────────────────────────────────────┘                   │
 * └───────────────────────────────────────────────────────────────────────────┘
 *
 * Nos dois casos, depois de conectar com `connect()`, temos à disposição os
 * recursos pra preparar e enviar consultas, vincular parâmetros, controlar
 * transações, tratar erros e ler os resultados retornados pelo banco.
 */

class MysqlDatabase
{
    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                                TRAITS                                 ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     */
    
    use traits\CoreDatabase {
        traits\CoreDatabase::setPort as private parentSetPort;
    }
    
    
    
    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                              CONSTANTES                               ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     */

    private const DRIVER = 'mysql';



    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                             PROPRIEDADES                              ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     */

    private array $requiredConfigKeys = ['host', 'port', 'username', 'password', 'database'];
    private array $requiredProperties = ['host', 'username', 'database'];


    
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

        $this->setHost($config['host']);
        $this->setPort($config['port']);
        $this->setDatabase($config['database']);
        $this->setUsername($config['username']);
        $this->setPassword($config['password']);
        $this->setOptions($config['options'] ?? []);
    }

    public function setPort(string $value): self
    {
        $this->port = empty($value) ? '' : 'port='.$value;
        return $this;
    }

    public function connect(): self
    {
        $dsn = self::DRIVER.':host='.$this->host.';dbname='.$this->database;

        if ($this->port !== '') {
            $dsn .= ';'.$this->port;
        }

        $createPdo = function () use ($dsn): \PDO {
            return new \PDO(
                $dsn,
                $this->username,
                $this->password,
                $this->options
            );
        };

        $this->establishConnection($createPdo);

        return $this;
    }
}
