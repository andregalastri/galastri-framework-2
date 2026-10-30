<?php
/**
 * -----------------------------------------------------------------------------
 * 📂
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║               ARQUIVO /galastri/core/config/Definition.php                ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 * Este arquivo define a classe que representa uma configuração de rota do
 * framework ou da aplicação.
 *
 * Cada configuração precisa guardar seu valor e as regras que determinam o que
 * pode ser aceito. `Definition` reúne essas informações em um objeto e verifica
 * cada valor antes de armazená-lo.
 *
 * A classe `Config` cria e mantém esses objetos, permitindo que o restante do
 * código consulte ou altere uma configuração pelo nome.
 * ─────────────────────────────────────────────────────────────────────────────
 */



/**
 * 🟢 NAMESPACE DA CLASSE
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * `Definition` pertence ao núcleo do framework.
 */

namespace galastri\core\config;



/**
 * 🟢 CLASSES UTILIZADAS
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Para preparar uma configuração e conferir os valores recebidos, precisamos
 * identificar tipos de dados e informar quando alguma regra não foi atendida.
 * Estas classes fornecem os recursos usados nessas verificações.
 *
 * ┌─────────────┬─────────────────────────────────────────────────────────────┐
 * │ Classe      │ Uso                                                         │
 * ├─────────────┼─────────────────────────────────────────────────────────────┤
 * │ `Exception` │ Permite interromper a preparação ou a alteração da          │
 * │             │ configuração quando encontramos uma informação inválida.    │
 * ├─────────────┼─────────────────────────────────────────────────────────────┤
 * │ `Message`   │ Fornece as mensagens padronizadas que explicam as falhas    │
 * │             │ encontradas.                                                │
 * ├─────────────┼─────────────────────────────────────────────────────────────┤
 * │ `Tools`     │ Identifica os tipos dos dados e transforma listas de opções │
 * │             │ em textos usados nas mensagens de erro.                     │
 * └─────────────┴─────────────────────────────────────────────────────────────┘
 */

use galastri\extensions\Exception;
use galastri\language\Message;
use galastri\modules\Tools;



/**
 * 🟢 CLASSE Definition
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Quando criamos uma configuração de rota, precisamos definir o que ela aceita.
 * Podemos permitir apenas determinados tipos de dados, limitar os valores
 * disponíveis ou estabelecer um formato que o conteúdo precisa seguir.
 *
 * A classe `Definition` guarda essas regras junto do nome e do valor da
 * configuração. Cada objeto de `Definition` representa uma configuração
 * individual, tendo suas próprias regras para conferir os valores que recebe.
 *
 * Imagine uma configuração que aceite apenas booleanos. Podemos atribuir `true`
 * ou `false`, mas uma `string` como 'sim' não atende à regra. Antes de
 * substituir o valor atual, o objeto faz essa verificação e lança uma exceção
 * se encontrar algo inválido. Assim, uma tentativa de alteração rejeitada
 * preserva o valor que já estava armazenado.
 *
 * Essa conferência também acontece durante a criação do objeto: primeiro
 * preparamos as regras e depois atribuímos o valor padrão, que precisa
 * respeitá-las como qualquer outro valor.
 *
 * A classe `Config` também se utiliza de `Definition` ao alterar uma
 * configuração. Quando chamamos `Config::set()`, ela localiza o objeto
 * correspondente e entrega o novo valor para que ``Definition`` faça as
 * verificações antes de guardá-lo.
 */

final class Definition
{
    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                              CONSTANTES                               ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     *
     *
     *
     * 🟢 CONTEXTOS DE CONFIGURAÇÃO PERMITIDOS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Reúne os nomes de contexto que podemos atribuir a uma configuração.
     * Atualmente os contextos permitidos são `debug`, `route`, `node` e
     * `endpoint`.
     *
     * Precisamos dessa lista para conferir se o contexto informado na definição
     * é reconhecido pelo framework. Isso evita guardar classificações com nomes
     * incorretos ou inválidos.
     *
     * O método `setContext()` consulta essa constante antes de preencher
     * `$context`. Se o nome recebido não estiver na lista, lançamos uma
     * exceção.
     *
     *
     * ---
     * @var array
     */
    private const ALLOWED_CONFIG_CONTEXT = [
        'debug', 'route', 'node', 'endpoint'
    ];
    
    
    
    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                             PROPRIEDADES                              ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     *
     *
     *
     * 🟢 NOME DA CONFIGURAÇÃO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Armazena o nome que identifica a configuração, como `output`, `timezone`,
     * etc. É basicamente o nome que informamos na configuração de rota ou
     * através da classe Config. Cada configuração precisa ter um nome
     * exclusivo, mesmo quando pertencerem a contextos diferentes.
     *
     * Também o usamos nas mensagens de erro para indicar qual configuração
     * recebeu uma definição ou um valor inválido.
     *
     * @var string
     */
    
    private string $name;
    
    
    
    /**
     * 🟢 VALOR ATUAL DA CONFIGURAÇÃO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Armazena o valor da configuração, que será usado pelo framework ou
     * aplicação.
     *
     * Durante a criação do objeto, é atribuído o valor padrão definido no
     * arquivo de definição das configurações. Depois, pode ser atualizado
     * através de `Config::setValue()`, desde que o novo valor passe pelas
     * regras de validação.
     *
     * Usamos o tipo de dado `mixed` porque cada configuração pode trabalhar com
     * um tipo diferente. Uma configuração como `offline` guarda um booleano,
     * enquanto `projectName` guarda uma string. As regras de cada objeto é que
     * determinam quais tipos ele aceita.
     *
     * @var mixed
     */
    
    private mixed $value;
    


    /**
     * 🟢 TIPOS DE DADOS PERMITIDOS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Armazena uma lista com os tipos de dados que a configuração pode receber.
     *
     * Por exemplo, `['bool', 'string']` permite que o valor recebido seja um
     * booleano ou uma string.
     *
     * Quando a lista está preenchida, o valor precisa corresponder a um dos
     * tipos informados. Quando está vazia, essa lista não impõe uma restrição
     * de tipo.
     *
     * Passar por essa verificação ainda exige que o valor respeite as demais
     * regras da configuração.
     *
     * @var array
     */

    private array $validTypes;
    


    /**
     * 🟢 TIPOS DE DADOS PROIBIDOS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Armazena uma lista com os tipos de dados que a configuração não pode
     * receber.
     *
     * Essa lista permite proibir determinados tipos sem precisar enumerar todos
     * os tipos permitidos. Se contiver ['array', 'object'], por exemplo,
     * valores desses dois tipos serão rejeitados.
     *
     * Quando está vazia, não há tipos proibidos por essa regra. Se um tipo
     * aparecer tanto em `$validTypes` quanto aqui, seu uso será rejeitado pela
     * verificação dos tipos proibidos.
     *
     * @var array
     */

    private array $invalidTypes;
    


    /**
     * 🟢 VALORES PERMITIDOS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Armazena uma lista com os valores que a configuração pode receber.
     *
     * Aqui limitamos o conteúdo aceito. A configuração `output`, por exemplo,
     * permite o tipo `string`, mas também restringe seu valor à uma lista de
     * valores, que podem ser 'view', 'file' ou 'json'.
     *
     * A comparação considera tanto o conteúdo quanto o tipo do dado. Portanto,
     * o inteiro 1 e a string '1' são valores diferentes nessa verificação.
     *
     * Quando a lista está vazia, essa regra não limita os valores disponíveis.
     *
     * @var array
     */

    private array $validValues;
    


    /**
     * 🟢 VALORES PROIBIDOS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Armazena uma lista com os valores que devem ser rejeitados pela
     * configuração.
     *
     * Podemos usar essa regra quando queremos impedir valores específicos sem
     * precisar listar todas as possibilidades aceitas. Por exemplo, uma lista
     * contendo `[0, 1, 2]` proíbe os inteiro zero, um e dois.
     *
     * Assim como em `$validValues`, a comparação considera o tipo do dado.
     * Nesse exemplo, a `string` `'0'` não seria rejeitada por essa lista.
     *
     * Quando está vazia, não há valores proibidos por essa regra.
     *
     * @var array
     */

    private array $invalidValues;
       
    
    
    /**
     * 🟢 EXPRESSÃO REGULAR PARA O FORMATO PERMITIDO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Armazena uma expressão regular REGEX que o valor precisa corresponder
     * para ser aceito.
     *
     * Essa regra é útil quando precisamos verificar um formato, como o caminho
     * de um arquivo PHP, sem enumerar todos os caminhos possíveis. A definição
     * de `templateFile`, por exemplo, utiliza uma expressão para conferir o
     * formato do caminho recebido.
     *
     * A verificação acontece quando a expressão está preenchida e o valor
     * recebido é diferente de uma string vazia. Se o valor NÃO corresponder ao
     * que foi definido, lançamos uma exceção.
     *
     * @var string
     */
    
    private string $validRegex;
       
    
    
    /**
     * 🟢 EXPRESSÃO REGULAR PARA O FORMATO PROIBIDO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Armazena uma expressão regular REGEX que identifica conteúdos que a
     * configuração deve rejeitar.
     *
     * Podemos usá-la, por exemplo, para procurar um trecho ou uma sequência de
     * caracteres proibida. Se o valor corresponder ao que foi definido,
     * lançamos uma exceção.
     *
     * Essa verificação também depende de a expressão estar preenchida e de o
     * valor recebido ser diferente de uma string vazia.
     *
     * @var string
     */
    
    private string $invalidRegex;
       
    
    
    /**
     * 🟢 CONTEXTO DA CONFIGURAÇÃO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Armazena o contexto ao qual a configuração pertence, usando um dos nomes
     * reconhecidos por `ALLOWED_CONFIG_CONTEXT`.
     *
     * Essa informação classifica a configuração dentro da organização do
     * framework. Por exemplo, `output` pertence ao contexto `route`, enquanto
     * `httpMethod` pertence ao contexto `endpoint`.
     *
     * @var string
     */
    
    private string $context;
    
    
    
    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                                MÉTODOS                                ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     *
     *
     *
     * 🟢 PREPARAÇÃO DA CONFIGURAÇÃO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * O construtor prepara o objeto que representará uma configuração.
     * Recebemos seu nome e o `array` com as regras, o contexto e o valor padrão
     * que ela deve usar.
     *
     * A classe `Config` fornece essas informações ao criar cada objeto
     * `Definition`. A partir delas, precisamos deixar a configuração pronta para
     * receber alterações e fornecer seu valor quando for consultada.
     * 
     * 
     * ---
     * @param string $name
     * :: Nome que identifica a configuração, como `output` ou `offline`.
     * 
     * @param array<string,mixed> $definitions
     * :: Array com as regras de validação, o contexto e o valor padrão da
     *    configuração.
     */
    
    public function __construct(string $name, array $definitions)
    {
        /**
         * Começamos pelo nome porque ele será usado nas mensagens de erro das
         * próximas verificações. Assim, se alguma informação estiver incorreta,
         * conseguimos identificar a configuração que estamos preparando.
         */
        
        $this->setName($name);
        
        
        
        /**
         * Depois, preparamos as regras e o contexto através dos métodos
         * responsáveis por cada propriedade. Esses métodos conferem as
         * informações recebidas antes de guardá-las. Por exemplo, as listas de
         * tipos precisam ser `arrays` contendo os tipos, e o contexto
         * precisa estar entre aqueles reconhecidos pelo framework.
         */
        $this->setValidTypes($definitions['validTypes']);
        $this->setInvalidTypes($definitions['invalidTypes']);
        $this->setValidValues($definitions['validValues']);
        $this->setInvalidValues($definitions['invalidValues']);
        $this->setValidRegex($definitions['validRegex']);
        $this->setInvalidRegex($definitions['invalidRegex']);
        $this->setContext($definitions['context']);
        
        
        
        /**
         * O valor padrão é atribuído por último porque sua validação depende
         * das regras que acabamos de preparar. Usamos setValue() para que ele
         * passe pelas mesmas verificações que serão aplicadas às alterações
         * posteriores. Afinal, o valor inicial também precisa respeitar a
         * definição da configuração.
         */
        $this->setValue($definitions['defaultValue']);
        
        /**
         * Se alguma das etapas acima falhar, lançamos uma exceção e a
         * construção do objeto é interrompida.
         */
    }

    
    
    /**
     * 🟢 DEFINIÇÃO DO NOME DA CONFIGURAÇÃO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Cada objeto precisa guardar o nome da configuração que representa. É esse
     * nome que informaremos quando precisarmos recuperar ou atualizar dados da
     * configuração, ou em mensagens de erro para identificar qual configuração
     * recebeu uma definição ou um valor inválido.
     *
     * 💡 OBSERVAÇÃO
     * ┌───────────────────────────────────────────────────────────────────────┐
     * │ Este é o motivo do construtor chamar setName() antes de preparar as   │
     * │ demais propriedades: se alguma das próximas verificações falhar, já   │
     * │ teremos o nome da configuração disponível para informar na mensagem   │
     * │ de erro.                                                              │
     * └───────────────────────────────────────────────────────────────────────┘
     *
     *
     * ---
     * @param mixed<string> $value  
     *   Nome que será atribuído à configuração. O parâmetro aceita `mixed` para
     *   que possamos validar o tipo dentro do próprio método, sem conversões ou
     *   erros de tipo gerados pelo PHP. Neste caso aqui, precisa ser uma
     *   `string`.
     *
     * @return void
     */
    
    private function setName(mixed $value): void
    {
        
        /**
         * Checa se o tipo está correto antes de guardarmos o nome na
         * propriedade `$name`.
         *
         * Aqui verificamos apenas o tipo do dado; não há uma regra adicional
         * para restringir o conteúdo desse nome.
         */
        
        if (!$this->isOfType('string', $value)) {
            throw new Exception(
                Message::get("DEFINITION_INVALID_NAME"),
                [
                    'string',
                    $value,
                    Tools::typeOf($value),
                ]
            );
        }

        $this->name = $value;
    }
    
    /**
     * 🔶 CONSULTA DO NOME DA CONFIGURAÇÃO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Retorna o nome guardado na propriedade `$name`.
     *
     * Como o nome já foi conferido durante sua atribuição, aqui apenas
     * recuperamos o valor armazenado.
     *
     *
     * ---
     * @return string Retorna uma `string` com o nome da configuração.
     */

    public function getName(): string
    {
        return $this->name;
    }

    

    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * ---
     * @param mixed $values  
     *   xxx
     *
     * @return void
     */
    
    private function setValidTypes(mixed $values): void
    {
        if (!$this->isOfType('array', $values)) {
            throw new Exception(
                Message::get("DEFINITION_INVALID_TYPE"),
                [
                    $this->getName(),
                    'validTypes',
                    'array',
                    Tools::typeOf($values),
                ]
            );
        }

        foreach ($values as $value) {
            if (!$this->isOfType('string', $value)) {
                throw new Exception(
                    Message::get("DEFINITION_INVALID_TYPE_IN_ARRAY"),
                    [
                        $this->getName(),
                        'validTypes',
                        'string',
                        Tools::typeOf($value),
                    ]
                );
            }
        }

        $this->validTypes = $values;
    }
    
    /**
     * 🔶 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * ---
     * @return array xxx
     */

    public function getValidTypes(): array
    {
        return $this->validTypes;
    }


    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * ---
     * @param mixed $values  
     *   xxx
     *
     * @return void
     */
    
    private function setInvalidTypes(mixed $values): void
    {
        if (!$this->isOfType('array', $values)) {
            throw new Exception(
                Message::get("DEFINITION_INVALID_TYPE"),
                [
                    $this->getName(),
                    'invalidTypes',
                    'array',
                    Tools::typeOf($values),
                ]
            );
        }

        foreach ($values as $value) {
            if (!$this->isOfType('string', $value)) {
                throw new Exception(
                    Message::get("DEFINITION_INVALID_TYPE_IN_ARRAY"),
                    [
                        $this->getName(),
                        'invalidTypes',
                        'string',
                        Tools::typeOf($value),
                    ]
                );
            }
        }

        $this->invalidTypes = $values;
    }

    /**
     * 🔶 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * ---
     * @return array xxx
     */
    
    public function getInvalidTypes(): array
    {
        return $this->invalidTypes;
    }


    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * ---
     * @param mixed $values  
     *   xxx
     *
     * @return void
     */
    
    private function setValidValues(mixed $values): void
    {
        if (!$this->isOfType('array', $values)) {
            throw new Exception(
                Message::get("DEFINITION_INVALID_TYPE"),
                [
                $this->getName(),
                    'validValues',
                    'array',
                    Tools::typeOf($values),
                ]
            );
        }

        $this->validValues = $values;
    }

    /**
     * 🔶 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * ---
     * @return array xxx
     */
    
    public function getValidValues(): array
    {
        return $this->validValues;
    }

    

    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * ---
     * @param mixed $values  
     *   xxx
     *
     * @return void
     */

    private function setInvalidValues(mixed $values): void
    {
        if (!$this->isOfType('array', $values)) {
            throw new Exception(
                Message::get("DEFINITION_INVALID_TYPE"),
                [
                $this->getName(),
                    'invalidValues',
                    'array',
                    Tools::typeOf($values),
                ]
            );
        }

        $this->invalidValues = $values;
    }

    /**
     * 🔶 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * ---
     * @return array xxx
     */
    
    public function getInvalidValues(): array
    {
        return $this->invalidValues;
    }


    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * ---
     * @param mixed $value  
     *   xxx
     *
     * @return void
     */
    
    private function setValidRegex(mixed $value): void
    {
        if (!$this->isOfType('string', $value)) {
            throw new Exception(
                Message::get("DEFINITION_INVALID_TYPE"),
                [
                    $this->getName(),
                    'validRegex',
                    'string',
                    Tools::typeOf($value),
                ]
            );
        }

        $this->validRegex = $value;
    }

    /**
     * 🔶 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * ---
     * @return string xxx
     */
    
    public function getValidRegex(): string
    {
        return $this->validRegex;
    }


    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * ---
     * @param mixed $value  
     *   xxx
     *
     * @return void
     */

    private function setInvalidRegex(mixed $value): void
    {
        if (!$this->isOfType('string', $value)) {
            throw new Exception(
                Message::get("DEFINITION_INVALID_TYPE"),
                [
                $this->getName(),
                    'invalidRegex',
                    'string',
                    Tools::typeOf($value),
                ]
            );
        }

        $this->invalidRegex = $value;
    }

    /**
     * 🔶 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * ---
     * @return string xxx
     */
    
    public function getInvalidRegex(): string
    {
        return $this->invalidRegex;
    }
    
    
    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * ---
     * @param mixed $value  
     *   xxx
     *
     * @return void
     */

    private function setContext(mixed $value): void
    {
        if (!$this->isOfType('string', $value)) {
            throw new Exception(
                Message::get("DEFINITION_INVALID_TYPE"),
                [
                    $this->getName(),
                    'context',
                    'string',
                    Tools::typeOf($value),
                ]
            );
        }

        if (empty(self::ALLOWED_CONFIG_CONTEXT) || !in_array($value, self::ALLOWED_CONFIG_CONTEXT, true)) {
            throw new Exception(
                Message::get("DEFINITION_INVALID_VALUE_IN_ARRAY"),
                [
                    $this->getName(),
                    'context',
                    Tools::readableImplode(', ', ' ou ', self::ALLOWED_CONFIG_CONTEXT),
                    $value,
                ]
            );
        }

        $this->context = $value;
    }

    /**
     * 🔶 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * ---
     * @return string xxx
     */
    
    public function getContext(): string
    {
        return $this->context;
    }


    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * ---
     * @param mixed $value  
     *   xxx
     *
     * @return void
     */

    public function setValue(mixed $value): void
    {
        if (!empty($this->getValidTypes()) && !$this->isOfType($this->getValidTypes(), $value)) {
            throw new Exception(
                Message::get("CONFIG_INVALID_TYPE"),
                [
                    $this->getName(),
                    Tools::readableImplode(', ', ' ou ', $this->getValidTypes()),
                    Tools::typeOf($value),
                ],
                [
                    'testedValue' => $value,
                ]
            );
        }

        if (!empty($this->getInvalidTypes()) && $this->isOfType($this->getInvalidTypes(), $value)) {
            throw new Exception(
                Message::get("CONFIG_INVALID_TYPE_NOT"),
                [
                    $this->getName(),
                    Tools::readableImplode(', ', ' ou ', $this->getInvalidTypes()),
                ],
                [
                    'testedValue' => $value,
                ]
            );
        }

        if (!empty($this->getValidValues()) && !in_array($value, $this->getValidValues(), true)) {
            throw new Exception(
                Message::get("CONFIG_INVALID_VALUE"),
                [
                    $this->getName(),
                    Tools::readableImplode(', ', ' ou ', $this->getValidValues()),
                    $value,
                ],
                [
                    'testedValue' => $value,
                ]
            );
        }

        if (!empty($this->getInvalidValues()) && in_array($value, $this->getInvalidValues(), true)) {
            throw new Exception(
                Message::get("CONFIG_INVALID_VALUE_NOT"),
                [
                    $this->getName(),
                    Tools::readableImplode(', ', ' ou ', $this->getInvalidValues()),
                ],
                [
                    'testedValue' => $value,
                ]
            );
        }

        if ($value !== '') {
            if (!empty($this->getInvalidRegex()) && preg_match($this->getInvalidRegex(), $value)) {
                throw new Exception(
                    Message::get("CONFIG_VALUE_MATCHED_REGEX"),
                    [
                        $this->getName(),
                        $value,
                        $this->getInvalidRegex(),
                    ],
                    [
                        'testedValue' => $value,
                    ]
                );
            }

            if (!empty($this->getValidRegex()) && !preg_match($this->getValidRegex(), $value)) {
                throw new Exception(
                    Message::get("CONFIG_VALUE_MATCHED_REGEX_NOT"),
                    [
                        $this->getName(),
                        $value,
                        $this->getValidRegex(),
                ],
                    [
                        'testedValue' => $value,
                    ]
                );
            }
        }

        $this->value = $value;
    }
    
    /**
     * 🔶 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * ---
     * @return mixed xxx
     */

    public function getValue(): mixed
    {
        return $this->value;
    }

    
    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * ---
     * @param array|string $types  
     *   xxx
     * 
     * @param mixed $value  
     *   xxx
     *
     * @return bool xxx
     */

    private function isOfType(array|string $types, mixed $value): bool
    {
        if (Tools::typeOf($types) !== 'array') {
            $types = [$types];
        }

        foreach ($types as $type) {
            if (Tools::typeOf($value) === $type) {
                return true;
                break;
            }
        }

        return false;
    }
}
