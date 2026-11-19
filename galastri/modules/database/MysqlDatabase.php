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
    
    /**
     * 🟢 ESTRUTURA COMPARTILHADA DE BANCO DE DADOS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Esta trait reúne toda a lógica compartilhada pelas classes de banco de
     * dados do framework, evitando repetir esse código em cada driver e deixando
     * nesta classe apenas o que é específico do MySQL.
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
     * conexões MySQL.
     *
     * Ao abrir a conexão, precisamos informar ao `PDO` qual driver usar e os
     * dados de localização do servidor e do banco. Reunimos essas informações
     * em uma string de conexão chamada DSN, em que esse identificador fica como
     * prefixo, como em `'mysql:host=localhost;dbname=minha_aplicacao'`.
     *
     *
     * _________________________________
     * @var string
     */
    private const DRIVER = 'mysql';



    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                             PROPRIEDADES                              ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     */

    /**
     * 🟢 CHAVES OBRIGATÓRIAS NA CONFIGURAÇÃO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Esta propriedade lista os nomes das chaves que precisam estar declaradas
     * na configuração do MySQL em `/app/config/database.php`.
     *
     * Quando carregamos uma conexão desse arquivo, conferimos por esta lista se
     * o bloco correspondente informou as chaves obrigatórias para abrir o
     * banco. As chaves `port` e `password` precisam estar declaradas, mas podem
     * ficar vazias. A chave `'options'` fica fora porque é opcional na
     * configuração.
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
    
    private array $requiredConfigKeys = [
        'host', 'port', 'username', 'password', 'database'
    ];
    
    
    
    /**
     * 🟢 PROPRIEDADES OBRIGATÓRIAS NA CONEXÃO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Esta propriedade lista os nomes das propriedades que não podem estar
     * vazias no momento de abrir a conexão com o MySQL.
     *
     * Antes de instanciar o `PDO`, conferimos por esta lista se as propriedades
     * obrigatórias foram preenchidas, seja pelo arquivo
     * `/app/config/database.php` ou pelos setters da instância. As propriedades
     * `$port`, `$password` e `$options` ficam fora porque podem permanecer
     * vazias.
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
    private array $requiredProperties = ['host', 'username', 'database'];


    
    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                                MÉTODOS                                ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     */

    /**
     * 🟢 INICIALIZAÇÃO DA INSTÂNCIA MYSQL
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método inicializa a instância de `MysqlDatabase`. Existem duas
     * formas de inicializar:
     * 
     * 1. Carregando automaticamente uma configuração de
     *    `/app/config/database.php`, informando a chave da configuração do
     *    banco de dados que se quer acessar no parâmetro `$dbConfigName`, como
     *    `new MysqlDatabase('nome_da_config')`.
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
         * Se o nome não existir ou faltar alguma chave listada em
         * `$requiredConfigKeys`, uma exceção é lançada.
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
         * Quando `$config` for, de fato, uma configuração válida, preenchemos
         * os dados da conexão pelos setters da instância. Como `options` é
         * opcional no arquivo de configuração, usamos um array vazio como
         * fallback quando ela não estiver definida.
         */
        
        $this->setHost($config['host']);
        $this->setPort($config['port']);
        $this->setDatabase($config['database']);
        $this->setUsername($config['username']);
        $this->setPassword($config['password']);
        $this->setOptions($config['options'] ?? []);
    }

    
    
    /**
     * 🟢 DEFININDO A PORTA DE CONEXÃO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método define a porta de conexão com o servidor MySQL na propriedade
     * `$port`, já adaptada ao formato usado no DSN do driver.
     *
     * Sobrescrevemos o `setPort()` de `CoreDatabase` porque, no DSN do MySQL, o
     * preenchimento da porta é diferente e precisa aparecer no formato
     * `port=<numero>`.
     *
     * Ao final, retornamos a própria instância para permitir encadear outros
     * métodos na mesma chamada.
     *
     *
     * _________________________________
     * @param string $value  
     *    Porta de conexão com o servidor MySQL, ou string vazia para usar a
     *    porta padrão.
     * _________________________________
     * @return self  
     *    Retorna a própria instância para encadeamento de chamadas.
     */
    public function setPort(string $value): self
    {
        
        /**
         * Se o valor vier vazio, armazenamos apenas uma string vazia `''` para
         * que `connect()` omita a porta e use o padrão do servidor.
         */
        
        $this->port = empty($value) ? '' : 'port='.$value;
        return $this;
    }

    
    
    /**
     * 🟢 ABERTURA DA CONEXÃO MYSQL
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método abre a conexão com o banco de dados MySQL usando os dados e
     * as opções configuradas na instância.
     *
     * Como cada driver monta seu próprio DSN e passa argumentos específicos ao
     * `PDO`, preparamos aqui apenas a criação da instância `PDO` pro MySQL e
     * delegamos as validações, a configuração de atributos e o tratamento de
     * erros para `establishConnection()` do `CoreDatabase`.
     *
     *
     * _________________________________
     * @return self  
     *    Retorna a própria instância para encadeamento de chamadas. Lança uma
     *    exceção se já houver uma conexão ativa, se alguma propriedade
     *    obrigatória em `$requiredProperties` estiver vazia ou se o `PDO`
     *    falhar ao conectar ao servidor.
     */
    
    public function connect(): self
    {
        /**
         * Montamos a base do DSN usando a constante `DRIVER` como prefixo e as
         * demais configurações para formar a string.
         */
        
        $dsn = self::DRIVER.':host='.$this->host.';dbname='.$this->database;

        
        
        /**
         * Se uma porta foi configurada por `setPort()`, acrescentamos esse
         * trecho ao final do DSN. Como `setPort()` já guarda o valor no formato
         * `'port=<numero>'`, basta concatená-lo. Quando `$this->port` estiver
         * vazio, omitimos essa parte e o `PDO` usa a porta padrão do MySQL.
         */
        
        if ($this->port !== '') {
            $dsn .= ';'.$this->port;
        }

        
        
        /**
         * Preparamos a criação do `PDO` dentro de uma função anônima, sem
         * executá-la agora, porque é `establishConnection()`, da trait
         * `CoreDatabase`, quem faz as validações iniciais e executa essa
         * abertura dentro do seu `try/catch`. Assim, qualquer falha do `PDO` ao
         * abrir a conexão é capturada lá para aplicar os tratamentos de
         * `onError()`, se houver, sem precisarmos repetir essa estrutura aqui.
         *
         * Passamos ao construtor do `PDO` o DSN montado acima, as credenciais
         * em `$this->username` e `$this->password` e as opções extras em
         * `$this->options`.
         */
        $createPdo = function () use ($dsn): \PDO {
            return new \PDO(
                $dsn,
                $this->username,
                $this->password,
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
 * ◀️ VOLTAR AO TÓPICO ANTERIOR:
 * 🔗 LINK: /docs/estrutura-e-funcionamento/modulos-extensoes-e-outros.md
 */
