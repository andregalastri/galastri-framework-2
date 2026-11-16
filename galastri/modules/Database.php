<?php
/**
* -----------------------------------------------------------------------------
* 📂
* ╔═══════════════════════════════════════════════════════════════════════════╗
* ║                   ARQUIVO /galastri/modules/Database.php                  ║
* ╚═══════════════════════════════════════════════════════════════════════════╝
* Este arquivo define a classe que serve como porta de entrada pra criar
* conexões com bancos de dados na aplicação.
* ─────────────────────────────────────────────────────────────────────────────
*/



/**
 * 🟢 NAMESPACE DA CLASSE
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * A classe `Database` faz parte dos módulos do framework.
 */

namespace galastri\modules;



/**
 * 🟢 CLASSES UTILIZADAS
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * ┌──────────────────┬────────────────────────────────────────────────────────┐
 * │ Classe           │ Uso                                                    │
 * ├──────────────────┼────────────────────────────────────────────────────────┤
 * │ `MysqlDatabase`  │ Cria e gerencia uma conexão com bancos de dados MySQL. │
 * ├──────────────────┼────────────────────────────────────────────────────────┤
 * │ `SqliteDatabase` │ Cria e gerencia uma conexão com bancos de dados        │
 * │                  │ SQLite.                                                │
 * └──────────────────┴────────────────────────────────────────────────────────┘
 */

use galastri\modules\database\MysqlDatabase;
use galastri\modules\database\SqliteDatabase;


/**
 * Esta classe funciona como ponto de partida pra trabalhar com bancos de dados
 * no framework. Em vez de instanciar diretamente a classe de cada banco,
 * usamos os métodos estáticos de `Database` pra receber o objeto pronto pro
 * driver desejado (MySQL ou SQLite).
 *
 * Ao chamar um desses métodos, podemos passar o nome de uma configuração já
 * cadastrada no arquivo `/app/config/database.php` — ou deixar sem nada e
 * preencher os dados da conexão manualmente depois, usando os métodos da
 * instância retornada.
 *
 * ✔️ EXEMPLO
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ Criando uma instância pro MySQL usando uma configuração chamada           │
 * │ `'principal'`, imaginando que ela já estivesse definida no arquivo        │
 * │ `/app/config/database.php`:                                               │
 * │                                                                           │
 * │ ┌─────────────────────────────────────────────────────┐                   │
 * │ │ $db = Database::createMysql('principal');           │                   │
 * │ └─────────────────────────────────────────────────────┘                   │
 * │                                                                           │
 * │ Ou criando uma instância vazia pra configurar na hora:                    │
 * │                                                                           │
 * │ ┌─────────────────────────────────────────────────────┐                   │
 * │ │ $db = Database::createSqlite();                     │                   │
 * │ └─────────────────────────────────────────────────────┘                   │
 * └───────────────────────────────────────────────────────────────────────────┘
 *
 * A partir do objeto retornado, fazemos a conexão, preparamos consultas,
 * enviamos os comandos e lemos os resultados.
 */

final class Database
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
     * 🟢 CLASSE ESTÁTICA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Os recursos desta classe são acessados diretamente por chamadas como
     * `Database::createMysql()`, sem precisar criar um objeto.
     *
     * Por isso, deixamos o construtor privado para impedir que a classe seja
     * instanciada com `new Database()`.
     */

    private function __construct() {}
    
    

    /**
     * 🟢 CRIAÇÃO DE INSTÂNCIA MYSQL
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método cria e retorna uma instância de `MysqlDatabase` pra trabalhar
     * com bancos de dados MySQL.
     *
     * Se passarmos o nome de uma configuração em `$dbConfigName`, ela será
     * carregada automaticamente a partir do arquivo `/app/config/database.php`.
     * Se não passarmos nada (ou `null`), recebemos uma instância vazia pra
     * preencher os dados da conexão manualmente usando os setters da classe.
     *
     * Acesse o arquivo da classe MysqlDatabase para ver os detalhes e depois
     * volte aqui.
     * 🔗 LINK: /galastri/modules/database/MysqlDatabase.php
     *
     *
     * _________________________________
     * @param ?string $dbConfigName  
     *    Nome da configuração cadastrada em `/app/config/database.php`, ou
     *    `null` pra configurar manualmente depois.
     * _________________________________
     * @return MysqlDatabase  
     *    Retorna a instância pronta pra configurar, conectar e executar
     *    consultas no MySQL.
     */
    
    public static function createMysql(
        ?string $dbConfigName = null): MysqlDatabase
    {
        return new MysqlDatabase($dbConfigName);
    }

    
    
    /**
     * 🟢 CRIAÇÃO DE INSTÂNCIA SQLITE
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método cria e retorna uma instância de `SqliteDatabase` pra trabalhar
     * com bancos de dados SQLite.
     *
     * Assim como no MySQL, se informarmos `$dbConfigName`, a configuração é
     * carregada de `/app/config/database.php`. Sem esse argumento, a instância
     * vem vazia pra que o caminho do arquivo e as opções sejam definidos pelos
     * setters da classe.
     *
     * Acesse o arquivo da classe SqliteDatabase para ver os detalhes e depois
     * volte aqui.
     * 🔗 LINK: /galastri/modules/database/SqliteDatabase.php
     *
     *
     * _________________________________
     * @param ?string $dbConfigName
     *    Nome da configuração cadastrada em `/app/config/database.php`, ou
     *    `null` pra configurar manualmente depois.
     * _________________________________
     * @return SqliteDatabase
     *    Retorna a instância pronta pra configurar, conectar e executar
     *    consultas no SQLite.
     */
    
    public static function createSqlite(
        ?string $dbConfigName = null): SqliteDatabase
    {
        return new SqliteDatabase($dbConfigName);
    }
}
