<?php
/**
 * -----------------------------------------------------------------------------
 * 📂
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                 ARQUIVO /galastri/core/config/Config.php                  ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 * Este arquivo reúne os recursos usados para carregar, consultar e alterar as
 * configurações do framework e da aplicação.
 *
 * As definições ficam em arquivos PHP e descrevem o valor padrão e as regras de
 * cada configuração. A classe `Config` mantém essas informações disponíveis
 * pelo nome, enquanto `Definition` confere os valores antes de armazená-los.
 * ─────────────────────────────────────────────────────────────────────────────
 */



/**
 * 🟢 NAMESPACE DA CLASSE
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Config pertence ao núcleo do framework.
 */

namespace galastri\core\config;



/**
 * 🟢 CLASSES UTILIZADAS
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Para manter as configurações disponíveis, precisamos representar suas
 * definições e informar quando os dados recebidos estão incorretos. Estas
 * classes fornecem os recursos usados nessa preparação e nas consultas.
 *
 * ┌──────────────┬────────────────────────────────────────────────────────────┐
 * │ Classe       │ Uso                                                        │
 * ├──────────────┼────────────────────────────────────────────────────────────┤
 * │ `Definition` │ Representa cada configuração e confere seus valores de     │
 * │              │ acordo com as regras definidas para ela.                   │
 * ├──────────────┼────────────────────────────────────────────────────────────┤
 * │ `Exception`  │ Permite informar falhas no carregamento ou no acesso às    │
 * │              │ configurações.                                             │
 * ├──────────────┼────────────────────────────────────────────────────────────┤
 * │ `Message`    │ Fornece as mensagens padronizadas das exceções.            │
 * ├──────────────┼────────────────────────────────────────────────────────────┤
 * │ `Tools`      │ Identifica o tipo dos dados recebidos dos arquivos de      │
 * │              │ definição.                                                 │
 * └──────────────┴────────────────────────────────────────────────────────────┘
 */

use galastri\core\config\Definition;
use galastri\extensions\Exception;
use galastri\language\Message;
use galastri\modules\Tools;



/**
 * 🟢 CLASSE Config
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * O framework e a aplicação precisam consultar configurações em diferentes
 * partes do código. Em vez de cada classe carregar os arquivos por conta
 * própria, usamos `Config` como o ponto de acesso a essas informações.
 *
 * Cada configuração é identificada por um nome exclusivo e possui um objeto
 * `Definition`, que guarda seu valor atual, suas regras de validação e seu
 * contexto. `Config` mantém esses objetos reunidos e permite trabalhar com eles
 * através de chamadas estáticas.
 *
 * Por exemplo, `Config::get('offline')` consulta o valor dessa configuração,
 * enquanto `Config::set('offline', true)` solicita sua alteração. O novo valor
 * só será armazenado se respeitar as regras da configuração.
 *
 * As definições fornecidas pela aplicação permitem acrescentar configurações às
 * do framework. Os nomes precisam ser exclusivos mesmo entre arquivos e
 * contextos diferentes; uma nova definição não substitui outra que já tenha o
 * mesmo nome.
 */

final class Config
{
    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                              CONSTANTES                               ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     */
    
    
    /**
     * 🟢 CAMINHO DAS DEFINIÇÕES DO FRAMEWORK
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Indica onde encontramos os arquivos PHP com as definições das
     * configurações fornecidas pelo framework.
     *
     * O trecho `*.php` permite localizar todos os arquivos PHP diretamente
     * nessa pasta. Assim, podemos separar as definições em arquivos diferentes
     * e carregá-las através de `run()`.
     *
     *
     * _________________________________
     * @var string
     */
    
    private const GALASTRI_DEFINITIONS =
        PROJECT_DIR.'/galastri/core/config/definitions/*.php';
        
    
        
    /**
     * 🟢 CAMINHO DAS DEFINIÇÕES DA APLICAÇÃO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Indica onde a aplicação pode colocar seus próprios arquivos de definição
     * de configurações.
     *
     * Esses arquivos são carregados junto das definições do framework,
     * permitindo acrescentar configurações com seus próprios valores padrão e
     * regras. Não é obrigatório haver arquivos nesse caminho.
     *
     * Assim como em `GALASTRI_DEFINITIONS`, usamos `*.php` para localizar os
     * arquivos PHP diretamente nessa pasta.
     *
     *
     * _________________________________
     * @var string
     */
    
    private const APP_DEFINITIONS = PROJECT_DIR.'/app/config/definitions/*.php';

    
    
    /**
     * 🟢 PROPRIEDADES OBRIGATÓRIAS DA DEFINIÇÃO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Reúne os nomes das propriedades que precisam estar presentes na definição
     * de cada configuração.
     *
     * Precisamos informar o valor padrão, as regras de tipos e valores, as
     * expressões regulares e o contexto. Mesmo quando uma regra não impõe
     * restrições, sua propriedade precisa existir com o valor que a deixa
     * desativada, como um array ou uma string vazia.
     *
     * Essa lista permite conferir se a definição está completa antes de criar o
     * objeto `Definition`. A validação do conteúdo de cada propriedade fica a
     * cargo desse objeto.
     *
     *
     * _________________________________
     * @var array<string>
     */
    
    private const REQUIRED_PROPERTIES = [
        'defaultValue',
        'validTypes',
        'invalidTypes',
        'validValues',
        'invalidValues',
        'validRegex',
        'invalidRegex',
        'context',
    ];


    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                             PROPRIEDADES                              ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     */
    
    /**
     * 🟢 CONFIGURAÇÕES CARREGADAS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Armazena os objetos `Definition` das configurações carregadas, usando o
     * nome de cada configuração como chave do array.
     *
     * É essa relação que permite localizar uma configuração quando chamamos
     * `Config::get()` ou `Config::set()`. Cada objeto mantém seu próprio valor
     * e as regras que precisam ser respeitadas nas alterações.
     *
     * A propriedade começa vazia e é preenchida durante o carregamento das
     * definições.
     *
     *
     * _________________________________
     * @var array<string,Definition>
     */
    
    private static array $config = [];

    
    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                                MÉTODOS                                ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     */
    
    
    
    /**
     * 🟢 CLASSE ESTÁTICA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Os recursos desta classe são acessados diretamente por chamadas como
     * `Config::set('offline', true)`, sem precisar criar um objeto.
     *
     * Por isso, deixamos o construtor privado para impedir que a classe seja
     * instanciada com `new Config()`.
     */
    
    private function __construct() {}
    
    
    
    /**
     * 🟢 CARREGAMENTO DAS DEFINIÇÕES DE CONFIGURAÇÃO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Prepara as configurações que ficarão disponíveis para o framework e a
     * aplicação. Cada definição informa o nome da configuração, seu valor
     * padrão, suas regras de validação e seu contexto.
     *
     * Esse carregamento transforma as definições dos arquivos PHP em
     * configurações que podemos consultar e alterar pelo nome. Os valores
     * padrão também precisam respeitar as regras estabelecidas para cada
     * configuração.
     *
     * Se encontrarmos uma definição incompleta, inválida ou com um nome já
     * utilizado, lançamos uma exceção. Assim, o restante da aplicação não
     * prossegue com uma definição que não pôde ser preparada.
     *
     *
     * _________________________________
     * @return void
     */
    
    public static function run(): void
    {
        /**
         * Localizamos os arquivos de definição nos dois caminhos. Se não houver
         * arquivos correspondentes em uma das pastas, sua lista ficará vazia.
         */

        $galastriConfigDefinitions = glob(self::GALASTRI_DEFINITIONS);
        $appConfigDefinitions = glob(self::APP_DEFINITIONS);
        
        /**
         * Reunimos os caminhos em uma única lista, com os arquivos do framework
         * antes dos arquivos da aplicação.
         *
         * Essa ordem não permite sobrescrever definições: create() rejeita
         * qualquer nome que já tenha sido registrado.
         */

        $configDefinitions = array_merge(
            $galastriConfigDefinitions, $appConfigDefinitions
        );
        
        foreach($configDefinitions as $definitionFile) {
            /**
             * Cada arquivo deve retornar um array com as configurações que
             * define. Conferimos esse retorno antes de percorrer seu conteúdo.
             */

            $definitions = require($definitionFile);

            if (Tools::typeOf($definitions) !== 'array') {
                throw new Exception(
                    Message::get("CONFIG_FILE_RETURNED_INVALID_DATA"),
                    [
                        PROJECT_DIR.'/'.$definitionFile,
                    ]
                );
            }

            foreach ($definitions as $name => $definitions) {
                /**
                 * Dentro do arquivo, cada chave identifica uma configuração, e
                 * seu valor precisa ser um array com a definição
                 * correspondente.
                 */

                if (Tools::typeOf($definitions) !== 'array') {
                    throw new Exception(
                        Message::get("DEFINITION_INVALID_PROPERTY_VALUE"),
                        [
                            $name
                        ]
                    );
                }

                /**
                 * Conferimos se todas as propriedades obrigatórias estão
                 * presentes. Usamos array_key_exists() porque uma chave com
                 * valor null também existe; a validade do conteúdo será
                 * conferida por Definition.
                 */

                foreach (self::REQUIRED_PROPERTIES as $requiredProperty) {
                    if (!array_key_exists($requiredProperty, $definitions)) {
                        throw new Exception(
                            Message::get("DEFINITION_MISSING_REQUIRED_PROPERTY"),
                            [
                                $name,
                                $requiredProperty,
                            ]
                        );
                    }
                }

                /**
                 * Com a estrutura completa, solicitamos o registro da
                 * configuração. Definition prepara as regras e valida o valor
                 * padrão durante sua construção.
                 */

                self::create(
                    $name,
                    $definitions,
                );
            }
        }
    }

    
    
    /**
     * 🟢 REGISTRO DE UMA CONFIGURAÇÃO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Acrescenta uma configuração ao conjunto mantido por `Config`, deixando
     * seu valor e suas regras disponíveis através do nome informado.
     *
     * Esse nome precisa ser exclusivo. Se ele já estiver em uso, lançamos uma
     * exceção para impedir que uma definição substitua outra, mesmo que tenham
     * vindo de arquivos ou contextos diferentes.
     *
     * A preparação das regras e a validação do valor padrão são
     * responsabilidade de `Definition`. A configuração só fica registrada se
     * seu objeto puder ser construído com sucesso.
     *
     *
     * _________________________________
     * @param string $name  
     *    Nome exclusivo que identifica a configuração.
     *
     * @param array<string,mixed> $definitions  
     *    Definição da configuração, com as propriedades obrigatórias de
     *    `REQUIRED_PROPERTIES` e seus respectivos valores.
     * _________________________________
     * @return void
     */
    
    private static function create(string $name, array $definitions): void
    {
        /**
         * O nome identifica a configuração em todas as consultas. Impedimos sua
         * repetição para preservar a definição já registrada.
         */

        if (array_key_exists($name, self::list())) {
            throw new Exception(
                Message::get("DEFINITION_CONFIG_NAME_ALREADY_IN_USE"),
                [
                    $name,
                ]
            );
        }

        /**
         * Construímos o objeto antes de guardá-lo no array. Se suas
         * verificações lançarem uma exceção, esta configuração não será
         * acrescentada.
         */

        self::$config[$name] = new Definition($name, $definitions);
    }

    
    
    /**
     * 🟢 ALTERAÇÃO DO VALOR DE UMA CONFIGURAÇÃO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Permite atualizar o valor de uma configuração já carregada. Usamos esse
     * recurso quando o framework ou a aplicação precisa ajustar uma opção, como
     * ativar `offline` ou alterar `title`.
     *
     * O valor recebido precisa respeitar as regras da configuração. Uma
     * tentativa rejeitada lança uma exceção e preserva o valor que estava
     * armazenado.
     *
     * Este método trabalha com configurações existentes. O registro de novas
     * configurações depende das definições carregadas por `run()`.
     *
     *
     * _________________________________
     * @param string $name  
     *    Nome da configuração que terá seu valor alterado.
     *
     * @param mixed $value  
     *    Novo valor da configuração. Os tipos e valores aceitos dependem das
     *    regras do objeto `Definition` correspondente.
     * _________________________________
     * @return void
     */
    
    public static function set(string $name, mixed $value): void
    {
        /**
         * Exigimos que a configuração exista antes de acessar seu objeto.
         * Depois, Definition aplica suas regras ao novo valor e só o armazena
         * se todas forem atendidas.
         */

        self::exists($name, true);
        self::$config[$name]->setValue($value);
    }

    
    
    /**
     * 🟢 CONSULTA DO VALOR DE UMA CONFIGURAÇÃO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Retorna o valor atual de uma configuração pelo nome. O tipo do retorno
     * depende da configuração consultada: `offline`, por exemplo, fornece um
     * booleano, enquanto `title` fornece uma string.
     *
     * Podemos informar um segundo argumento para usar como valor alternativo
     * caso a configuração não exista. Sem esse argumento, a ausência da
     * configuração gera uma exceção.
     *
     * Por exemplo, `Config::get('tema', 'claro')` retorna 'claro' se não houver
     * uma configuração chamada 'tema'.
     *
     * O valor alternativo não substitui um valor existente. Se a configuração
     * estiver carregada e seu valor for `false`, `null` ou uma string vazia,
     * esse será o retorno da consulta.
     *
     *
     * _________________________________
     * @param mixed ...$parameters  
     *    Argumentos da consulta. O primeiro é o nome da configuração, que
     *    precisa ser uma string. O segundo é opcional e pode ter qualquer tipo:
     *    será o retorno alternativo se a configuração não existir.
     * _________________________________
     * @return mixed
     *    Retorna o valor atual da configuração ou o valor alternativo, quando
     *    ele tiver sido informado e a configuração não existir.
     */
    
    public static function get(...$parameters): mixed
    {
        try {
            /**
             * O primeiro argumento identifica a configuração. Exigimos sua
             * existência para recuperar o valor do objeto correspondente.
             */

            $name = $parameters[0];

            self::exists($name, true);
            return self::$config[$name]->getValue();

        } catch (Exception $e) {
            /**
             * Se a consulta lançar uma exceção do framework, verificamos se o
             * segundo argumento foi informado.
             *
             * Usamos array_key_exists() para distinguir a ausência do argumento
             * de um valor alternativo null. Assim, null também pode ser um
             * retorno escolhido por quem chamou o método.
             */

            if (array_key_exists(1, $parameters)) {
                return $parameters[1];
            }

            /**
             * Sem um valor alternativo, repassamos a mesma exceção para que o
             * código responsável pela chamada possa tratar a falha.
             */

            throw $e;
        }
    }

    
    
    /**
     * 🟢 CONSULTA DAS CONFIGURAÇÕES CARREGADAS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Retorna o conjunto de configurações mantido por `Config`, com cada nome
     * associado ao seu objeto `Definition`.
     *
     * Essa consulta permite acessar tanto o valor quanto as regras e o contexto
     * de cada configuração através dos métodos de seu objeto. Para obter apenas
     * os valores associados aos nomes, usamos `listValues()`.
     *
     *
     * _________________________________
     * @return array<string,Definition>
     *    Retorna um array com os objetos `Definition`, identificados pelo nome
     *    de cada configuração. O array estará vazio se nenhuma configuração
     *    tiver sido carregada.
     */
    
    public static function list(): array
    {
        return self::$config;
    }

    
    
    /**
     * 🟢 CONSULTA DOS VALORES DAS CONFIGURAÇÕES
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Retorna os valores atuais das configurações em um único array, mantendo o
     * nome de cada configuração como chave.
     *
     * Essa forma de consulta é útil quando precisamos reunir os valores sem
     * consultar as regras e o contexto de cada objeto `Definition`. Os tipos
     * dos valores são preservados.
     *
     *
     * _________________________________
     * @return array<string,mixed>
     *    Retorna um array com os nomes das configurações e seus valores atuais.
     *    O array estará vazio se nenhuma configuração tiver sido carregada.
     */
    
    public static function listValues(): array
    {
        $valueList = [];

        /**
         * Montamos outro array com o valor atual de cada objeto, preservando os
         * nomes das configurações como chaves.
         */

        foreach (self::$config as $propertyName => $properties) {
            $valueList[$propertyName] = $properties->getValue();
        }

        return $valueList;
    }

    
    
    /**
     * 🟢 VERIFICAÇÃO DA EXISTÊNCIA DE UMA CONFIGURAÇÃO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Permite conferir se uma configuração está carregada antes de trabalhar
     * com ela. A existência depende de seu nome estar registrado,
     * independentemente do valor armazenado.
     *
     * Normalmente, a consulta retorna um booleano. Quando a configuração é
     * obrigatória para continuar, podemos ativar `$throwException` para que sua
     * ausência gere uma exceção em vez de retornar `false`.
     *
     *
     * _________________________________
     * @param string $name  
     *    Nome da configuração cuja existência será verificada.
     *
     * @param bool $throwException  
     *    Define se a ausência da configuração deve lançar uma exceção. O padrão
     *    é `false`, permitindo consultar o resultado como um booleano.
     * _________________________________
     * @return bool
     *    Retorna `true` se a configuração existir ou `false` se não existir.
     *    Com `$throwException` ativado, a ausência lança uma exceção.
     */
    
    public static function exists(string $name, bool $throwException = false): bool
    {
        if (array_key_exists($name, self::list())) {
            return true;
        }

        /**
         * Só chegamos aqui quando o nome não foi encontrado. Se a chamada
         * exigir essa configuração, informamos sua ausência pela exceção.
         */

        if ($throwException) {
            throw new Exception(
                Message::get("CONFIG_DOESNT_EXIST"),
                [
                    $name,
                ]
            );
        }

        return false;
    }

    
    
    /**
     * 🟢 VERIFICAÇÃO DA AUSÊNCIA DE UMA CONFIGURAÇÃO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Permite conferir se um nome ainda não está registrado entre as
     * configurações carregadas.
     *
     * A consulta retorna `true` quando a configuração não existe. Quando
     * precisamos exigir essa ausência para continuar, podemos ativar
     * `$throwException`: nesse caso, encontrar uma configuração com esse nome
     * gera uma exceção em vez de retornar `false`.
     *
     *
     * _________________________________
     * @param string $name  
     *    Nome da configuração cuja ausência será verificada.
     *
     * @param bool $throwException  
     *    Define se a existência da configuração deve lançar uma exceção. O
     *    padrão é `false`, permitindo consultar o resultado como um booleano.
     * _________________________________
     * @return bool
     *    Retorna `true` se a configuração não existir ou `false` se existir.
     *    Com `$throwException` ativado, a existência lança uma exceção.
     */
    
    public static function notExists(string $name, bool $throwException = false): bool
    {
        if (!array_key_exists($name, self::list())) {
            return true;
        }

        /**
         * Só chegamos aqui quando o nome já está registrado. Se a chamada
         * exigir sua ausência, informamos esse conflito pela exceção.
         */

        if ($throwException) {
            throw new Exception(
                Message::get("CONFIG_EXISTS"),
                [
                    $name,
                ]
            );
        }

        return false;
    }

    
    
    /**
     * 🟢 IMPORTAÇÃO DE UM ARQUIVO DE CONFIGURAÇÃO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Disponibiliza o array retornado por um arquivo PHP de configuração, como
     * o arquivo de rotas utilizado por `Router`.
     *
     * Esse recurso permite obter os dados do arquivo para que a classe
     * responsável por utilizá-los possa interpretá-los. A importação, por si
     * só, não registra configurações no conjunto de objetos `Definition`.
     *
     * O arquivo precisa existir e retornar um array. Se ele não for encontrado,
     * lançamos uma exceção com a mensagem do framework. A exigência de retorno
     * como array é estabelecida pelo tipo de retorno deste método.
     *
     *
     * _________________________________
     * @param string $path  
     *    Caminho do arquivo PHP que será executado para obter o array de
     *    configuração.
     * _________________________________
     * @return array<mixed>
     *    Retorna o array fornecido pelo arquivo, preservando suas chaves e seus
     *    valores.
     */
    
    public static function importConfig(string $path): array
    {
        /**
         * Conferimos a existência do arquivo para informar o caminho ausente
         * com a mensagem padronizada do framework.
         */

        if (!file_exists($path)) {
            throw new Exception(
                Message::get("CONFIG_FILE_NOT_FOUND"),
                [
                    $path
                ]
            );
        }

        /**
         * Executamos o arquivo e devolvemos o resultado de seu return. O tipo
         * de retorno deste método exige que esse resultado seja um array.
         */

        return require($path);
    }
}
