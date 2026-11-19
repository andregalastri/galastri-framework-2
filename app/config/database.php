<?php
/**
 * -----------------------------------------------------------------------------
 * 📂
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                      ARQUIVO /app/config/database.php                     ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 * Este arquivo centraliza as configurações de acesso aos bancos de dados da
 * aplicação.
 *
 * Ele retorna um `array` onde cada chave define o nome de uma conexão. Quando
 * informamos esse nome ao criar uma instância de `MysqlDatabase` ou
 * `SqliteDatabase`, carregamos automaticamente os dados da configuração
 * correspondente neste arquivo.
 *
 * ✔️ EXEMPLO
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ $db = new MysqlDatabase('mysql-example');                                 │
 * └───────────────────────────────────────────────────────────────────────────┘
 *
 * Se preferir configurar a conexão manualmente pelos setters da instância, não
 * é obrigatório cadastrar a conexão neste arquivo.
 * ─────────────────────────────────────────────────────────────────────────────
 */

return [

    /**
     * 🟢 CONFIGURAÇÃO MYSQL
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Cada chave armazena uma configuração da conexão MySQL, sendo elas:
     *
     * 
     * 🔶 host
     * ─────────────────────────────────────────────────────────────────────────
     * Endereço do servidor MySQL. Obrigatório e não pode ficar vazio.
     * - Exemplo: `'host' => 'localhost'` ou `'host' => '127.0.0.1'`.
     *
     * 🔶 port
     * ─────────────────────────────────────────────────────────────────────────
     * Porta de conexão do MySQL (em string). Obrigatório, mas pode ficar vazio
     * pra usar a porta padrão do servidor.
     * - Exemplo: `'port' => '3306'` ou `'port' => ''`.
     *
     * 🔶 database
     * ─────────────────────────────────────────────────────────────────────────
     * Nome do banco de dados no servidor. Obrigatório e não pode ficar vazio.
     * - Exemplo: `'database' => 'minha_aplicacao'`.
     *
     * 🔶 username
     * ─────────────────────────────────────────────────────────────────────────
     * Usuário usado para autenticar no banco. Obrigatório e não pode ficar
     * vazio.
     * - Exemplo: `'username' => 'root'`.
     *
     * 🔶 password
     * ─────────────────────────────────────────────────────────────────────────
     * Senha do usuário informado. Obrigatório, mas pode ficar vazio caso não
     * utilize senha.
     * - Exemplo: `'password' => '123456'` ou `'password' => ''`.
     *
     * 🔶 options
     * ─────────────────────────────────────────────────────────────────────────
     * Um `array` com opções extras repassadas ao `\PDO` na abertura da conexão.
     * Opcional, pode ficar vazio e também pode ser omitido da configuração.
     * - Exemplo: `'options' => [\PDO::ATTR_TIMEOUT => 5]`.
     */
    
    'mysql-example' => [
        'host'       => '',
        'port'       => '',
        'database'   => '',
        'username'   => '',
        'password'   => '',
        'options'    => [],
    ],

    
    
    /**
     * 🟢 CONFIGURAÇÃO SQLITE
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Cada chave armazena uma configuração da conexão SQLite, sendo elas:
     *
     *
     * 🔶 location
     * ─────────────────────────────────────────────────────────────────────────
     * Caminho do arquivo do banco SQLite no servidor (ou `':memory:'` pra usar
     * um banco em memória). Obrigatório e não pode ficar vazio.
     * - Exemplo: `'location' => PROJECT_DIR.'/database/dados.sqlite'` ou
     *   `'location' => ':memory:'`.
     *
     * 🔶 options
     * ─────────────────────────────────────────────────────────────────────────
     * Um `array` com opções extras repassadas ao `\PDO` na abertura da conexão.
     * Opcional, pode ficar vazio e também pode ser omitido da configuração.
     * - Exemplo: `'options' => [\PDO::ATTR_TIMEOUT => 5]`.
     */
    
    'sqlite-example' => [
        'location'   => '',
        'options'    => [],
    ],
];



/**
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * ◀️ VOLTAR A UM TÓPICO ANTERIOR:
 * 🔗 LINK: /galastri/modules/database/traits/CoreDatabase.php
 */