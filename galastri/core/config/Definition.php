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
 * Definition pertence ao núcleo do framework.
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
     */

    /**
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
     * _________________________________
     * @var array
     */
    private const ALLOWED_CONFIG_CONTEXT = [
        'debug', 'route', 'node', 'endpoint'
    ];
    
    
    
    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                             PROPRIEDADES                              ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     */

    /**
     * 🟢 NOME DA CONFIGURAÇÃO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Armazena o nome que identifica a configuração, como `output`, `timezone`,
     * etc. É basicamente o nome que informamos na configuração de rota ou
     * através da classe Config. Cada configuração precisa ter um nome
     * exclusivo, mesmo quando pertencer a contextos diferentes.
     *
     * Também o usamos nas mensagens de erro para indicar qual configuração
     * recebeu uma definição ou um valor inválido.
     *
     * 
     * _________________________________
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
     * através de `Config::set()`, desde que o novo valor passe pelas regras de
     * validação.
     *
     * Usamos o tipo de dado `mixed` porque cada configuração pode trabalhar com
     * um tipo diferente. Uma configuração como `offline` guarda um booleano,
     * enquanto `projectName` guarda uma string. As regras de cada objeto é que
     * determinam quais tipos ele aceita.
     *
     * 
     * _________________________________
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
     * 
     * _________________________________
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
     * 
     * _________________________________
     * @var array
     */

    private array $invalidTypes;
    


    /**
     * 🟢 VALORES PERMITIDOS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Armazena uma lista com os valores que a configuração pode receber.
     *
     * Aqui limitamos o conteúdo aceito. A configuração `output`, por exemplo,
     * permite o tipo `string`, mas também restringe seu valor a uma lista de
     * valores, que podem ser 'view', 'file' ou 'json'.
     *
     * A comparação considera tanto o conteúdo quanto o tipo do dado. Portanto,
     * o inteiro 1 e a string '1' são valores diferentes nessa verificação.
     *
     * Quando a lista está vazia, essa regra não limita os valores disponíveis.
     *
     * 
     * _________________________________
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
     * contendo `[0, 1, 2]` proíbe os inteiros zero, um e dois.
     *
     * Assim como em `$validValues`, a comparação considera o tipo do dado.
     * Nesse exemplo, a `string` `'0'` não seria rejeitada por essa lista.
     *
     * Quando está vazia, não há valores proibidos por essa regra.
     *
     * 
     * _________________________________
     * @var array
     */

    private array $invalidValues;
       
    
    
    /**
     * 🟢 EXPRESSÃO REGULAR PARA O FORMATO PERMITIDO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Armazena uma expressão regular REGEX à qual o valor precisa corresponder
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
     * 
     * _________________________________
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
     * 
     * _________________________________
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
     * 
     * _________________________________
     * @var string
     */
    
    private string $context;
    
    
    
    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                                MÉTODOS                                ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     */

    /**
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
     * _________________________________
     * @param string $name
     *    Nome que identifica a configuração, como `output` ou `offline`.
     * 
     * @param array<string,mixed> $definitions
     *    Array com as regras de validação, o contexto e o valor padrão da
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
     * _________________________________
     * @param mixed<string> $value  
     *     Nome que será atribuído à configuração. O parâmetro aceita `mixed`
     *     para que possamos validar o tipo dentro do próprio método, sem
     *     conversões ou erros de tipo gerados pelo PHP. Neste caso aqui,
     *     precisa ser uma `string`.
     * _________________________________
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
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Retorna o nome guardado na propriedade `$name`.
     *
     * Como o nome já foi conferido durante sua atribuição, aqui apenas
     * recuperamos o valor armazenado.
     *
     *
     * _________________________________
     * @return string  
     *    Retorna uma `string` com o nome da configuração.
     */

    public function getName(): string
    {
        return $this->name;
    }

    

    /**
     * 🟢 DEFINIÇÃO DOS TIPOS DE DADOS PERMITIDOS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método prepara a lista de tipos que a configuração poderá receber.
     * Essa lista será consultada por setValue() para conferir o tipo de cada
     * valor antes de armazená-lo.
     * 
     * ✔️ EXEMPLO
     * ┌──────────────────────────────────────────────────────────────────────┐
     * │ Por exemplo, se for definido `['bool', 'string']`, isso indicará que │
     * │ a configuração pode receber um booleano ou uma string. Guardamos os  │
     * │ nomes dos tipos porque eles serão comparados com o tipo identificado │
     * │ no valor recebido.                                                   │
     * └──────────────────────────────────────────────────────────────────────┘
     *
     *
     * _________________________________
     * @param mixed<array> $values  
     *     Lista dos tipos de dados permitidos. O parâmetro aceita `mixed` para
     *     que possamos validar o tipo dentro do próprio método, sem conversões
     *     ou erros de tipo gerados pelo PHP. Neste caso aqui, precisa ser um
     *     `array`.
     * _________________________________
     * @return void
     */
    
    private function setValidTypes(mixed $values): void
    {
        /**
         * Precisamos conferir a estrutura em dois níveis. Primeiro verificamos
         * se recebemos um `array`, pois precisamos de uma lista que possamos
         * percorrer.
         */
        
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

        
        
        /**
         * Depois, conferimos se cada elemento do `array` é uma string, já que
         * cada posição deve conter o nome de um tipo.
         */
        
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

        
        
        /**
         * Se qualquer uma dessas verificações falhar, lançamos uma exceção. A
         * propriedade só armazena a lista depois que todos os elementos
         * passarem pela conferência.
         *
         * Um array vazio também é permitido. Nesse caso, `setValue()` não
         * restringirá o tipo através dessa lista, mas continuará aplicando as
         * demais regras da configuração.
         */
        
        $this->validTypes = $values;
    }
    
    /**
     * 🔶 CONSULTA DOS TIPOS DE DADOS PERMITIDOS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Retorna a lista guardada em $validTypes, permitindo que `setValue()`
     * consulte quais tipos a configuração pode receber ou se não há restrição.
     *
     *
     * _________________________________
     * @return array<string>  
     *    Retorna um `array` com os nomes dos tipos de dados permitidos ou um
     *    array vazio quando não houver restrições.
     */

    public function getValidTypes(): array
    {
        return $this->validTypes;
    }


    
    /**
     * 🟢 DEFINIÇÃO DOS TIPOS DE DADOS PROIBIDOS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método prepara a lista de tipos que a configuração NÃO poderá
     * receber. Essa lista será consultada por setValue() para conferir o tipo
     * de cada valor antes de armazená-lo.
     *
     * ✔️ EXEMPLO
     * ┌──────────────────────────────────────────────────────────────────────┐
     * │ Por exemplo, se for definido `['bool', 'string']`, isso indicará que │
     * │ a configuração NÃO PODERÁ receber um booleano ou uma string.         │
     * │ Guardamos os nomes dos tipos porque eles serão comparados com o tipo │
     * │ identificado no valor recebido.                                      │
     * └──────────────────────────────────────────────────────────────────────┘
     *
     *
     * _________________________________
     * @param mixed<array> $values  
     *   Lista dos tipos de dados proibidos. O parâmetro aceita `mixed` para
     *   que possamos validar o tipo dentro do próprio método, sem conversões
     *   ou erros de tipo gerados pelo PHP. Neste caso aqui, precisa ser um
     *   `array`.
     * _________________________________
     * @return void
     */
    
    private function setInvalidTypes(mixed $values): void
    {
        /**
         * Precisamos conferir a estrutura em dois níveis. Primeiro verificamos
         * se recebemos um `array`, pois precisamos de uma lista que possamos
         * percorrer.
         */
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

        /**
         * Depois, conferimos se cada elemento do `array` é uma string, já que
         * cada posição deve conter o nome de um tipo.
         */
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
        
        /**
         * Se qualquer uma dessas verificações falhar, lançamos uma exceção. A
         * propriedade só armazena a lista depois que todos os elementos
         * passarem pela conferência.
         *
         * Um array vazio também é permitido. Nesse caso, `setValue()` não
         * restringirá o tipo através dessa lista, mas continuará aplicando as
         * demais regras da configuração.
         */

        $this->invalidTypes = $values;
    }

    /**
     * 🔶 CONSULTA DOS TIPOS DE DADOS PROIBIDOS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Retorna a lista guardada em $invalidTypes, permitindo que `setValue()`
     * consulte quais tipos a configuração NÃO PODE receber ou se não há
     * restrição.
     *
     *
     * _________________________________
     * @return array<string>  
     *    Retorna um `array` com os nomes dos tipos de dados proibidos ou um
     *    array vazio quando não houver restrições.
     */
    
    public function getInvalidTypes(): array
    {
        return $this->invalidTypes;
    }


    
    /**
     * 🟢 DEFINIÇÃO DOS VALORES PERMITIDOS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Prepara a lista de valores que a configuração pode receber. Essa regra
     * permite limitar as opções disponíveis, como acontece em `output`, que
     * aceita apenas 'view', 'file' ou 'json' como valores válidos.
     *
     *
     * _________________________________
     * @param mixed<array> $values
     *    Lista dos valores permitidos. O parâmetro aceita `mixed` para que
     *    possamos validar o tipo dentro do próprio método, sem conversões ou
     *    erros de tipo gerados pelo PHP. Neste caso aqui, precisa ser um
     *    `array`.
     * 
     * _________________________________
     * @return void
     */
    
    private function setValidValues(mixed $values): void
    {
        /**
         * Precisamos conferir se recebemos um array, pois setValue() percorrerá
         * essa lista para procurar o valor informado. Os elementos podem ter
         * tipos diferentes: podemos definir uma configuração que aceite, por
         * exemplo, tanto `false` quanto uma `string` específica.
         *
         * Por isso, aqui verificamos apenas se a lista é um `array`. Se não
         * for, lançamos uma exceção; se for, guardamos a lista em $validValues.
         *
         * Um array vazio deixa essa regra sem restringir os valores
         * disponíveis. As demais regras da configuração continuam sendo
         * aplicadas.
         */
        
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
     * 🔶 CONSULTA DOS VALORES PERMITIDOS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Retorna a lista guardada em $validValues, permitindo que setValue()
     * confira se o valor recebido está entre as opções permitidas.
     *
     *
     * _________________________________
     * @return array<mixed>  
     *    Retorna um `array` com os valores permitidos ou um `array` vazio
     *    quando essa regra não impõe restrições.
     */
    
    public function getValidValues(): array
    {
        return $this->validValues;
    }

    

    /**
     * 🟢 DEFINIÇÃO DOS VALORES PROIBIDOS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Prepara a lista de valores que a configuração NÃO PODE receber. Essa
     * regra permite definir as opções não disponíveis.
     *
     * ✔️ EXEMPLO
     * ┌─────────────────────────────────────────────────────────────┐
     * │ Por exemplo, se for definido `[0, 1, 2]`, isso indicará que │
     * │ a configuração NÃO PODERÁ receber tais valores.             │
     * └─────────────────────────────────────────────────────────────┘
     *
     *
     * _________________________________
     * @param mixed<array> $values
     *    Lista dos valores proibidos. O parâmetro aceita `mixed` para que
     *    possamos validar o tipo dentro do próprio método, sem conversões ou
     *    erros de tipo gerados pelo PHP. Neste caso aqui, precisa ser um
     *    `array`.
     * _________________________________
     * @return void
     */

    private function setInvalidValues(mixed $values): void
    {
        /**
         * Precisamos conferir se recebemos um array, pois setValue() consultará
         * essa lista para procurar o valor informado. Os elementos podem ter
         * tipos diferentes: podemos proibir, por exemplo, tanto false quanto
         * uma string específica.
         *
         * Por isso, aqui verificamos apenas se a lista é um `array`. Se não
         * for, lançamos uma exceção; se for, guardamos a lista em
         * $invalidValues.
         *
         * Um array vazio deixa essa regra sem restringir valores. As demais
         * regras da configuração continuam sendo aplicadas.
         */
        
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
     * 🔶 CONSULTA DOS VALORES PROIBIDOS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Retorna a lista guardada em $invalidValues, permitindo que setValue()
     * confira se o valor recebido está entre as opções proibidas.
     *
     *
     * _________________________________
     * @return array<mixed>  
     *    Retorna um `array` com os valores proibidos ou um `array` vazio
     *    quando essa regra não impõe restrições.
     */
    
    public function getInvalidValues(): array
    {
        return $this->invalidValues;
    }


    
    /**
     * 🟢 DEFINIÇÃO DA EXPRESSÃO REGULAR PARA O FORMATO PERMITIDO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Prepara a expressão regular que será usada para conferir o formato dos
     * valores recebidos pela configuração.
     *
     * Essa regra é útil quando queremos aceitar conteúdos com determinada
     * estrutura, como um caminho de arquivo PHP, sem precisar listar todas as
     * possibilidades.
     *
     *
     * _________________________________
     * @param mixed<string> $value  
     *    Expressão regular que define o formato permitido. O parâmetro aceita
     *    `mixed` para que possamos validar o tipo dentro do próprio método, sem
     *    conversões ou erros de tipo gerados pelo PHP. Neste caso aqui, precisa
     *    ser uma `string`.
     * _________________________________
     * @return void
     */
    
    private function setValidRegex(mixed $value): void
    {
        /**
         * Aqui conferimos se a expressão foi informada como uma `string` antes
         * de guardá-la em $validRegex. Se recebermos outro tipo, lançamos uma
         * exceção.
         *
         * Uma string vazia também é permitida e deixa essa regra desativada. A
         * sintaxe da expressão não é validada neste método; sua aplicação ao
         * valor acontece depois, em `setValue()`, através de `preg_match()`.
         */
        
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
     * 🔶 CONSULTA DA EXPRESSÃO REGULAR PARA O FORMATO PERMITIDO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Retorna a expressão guardada em $validRegex, permitindo que setValue()
     * consulte o padrão ao qual o valor precisa corresponder.
     *
     *
     * _________________________________
     * @return string  
     *    Retorna uma `string` com a expressão regular do formato permitido ou
     *    uma `string` vazia quando esta regra está desativada.
     */
    
    public function getValidRegex(): string
    {
        return $this->validRegex;
    }


    
    /**
     * 🟢 DEFINIÇÃO DA EXPRESSÃO REGULAR PARA O FORMATO PROIBIDO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Prepara a expressão regular que será usada para conferir o formato dos
     * valores recebidos pela configuração.
     *
     * Essa regra é útil quando NÃO queremos aceitar conteúdos com determinada
     * estrutura, como um caminho de arquivo PHP, sem precisar listar todas as
     * possibilidades.
     *
     *
     * _________________________________
     * @param mixed<string> $value  
     *    Expressão regular que define o formato proibido. O parâmetro aceita
     *    `mixed` para que possamos validar o tipo dentro do próprio método, sem
     *    conversões ou erros de tipo gerados pelo PHP. Neste caso aqui, precisa
     *    ser uma `string`.
     * _________________________________
     * @return void
     */

    private function setInvalidRegex(mixed $value): void
    {
        /**
         * Aqui conferimos se a expressão foi informada como uma `string` antes
         * de guardá-la em $invalidRegex. Se recebermos outro tipo, lançamos uma
         * exceção.
         *
         * Uma string vazia também é permitida e deixa essa regra desativada. A
         * sintaxe da expressão não é validada neste método; sua aplicação ao
         * valor acontece depois, em `setValue()`, através de `preg_match()`.
         */
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
     * 🔶 CONSULTA DA EXPRESSÃO REGULAR PARA O FORMATO PROIBIDO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Retorna a expressão guardada em $invalidRegex, permitindo que setValue()
     * consulte o padrão que identifica conteúdos proibidos.
     *
     *
     * _________________________________
     * @return string  
     *    Retorna uma `string` com a expressão regular do formato proibido ou
     *    uma `string` vazia quando esta regra está desativada.
     */
    
    public function getInvalidRegex(): string
    {
        return $this->invalidRegex;
    }
    
    
    
    /**
     * 🟢 DEFINIÇÃO DO CONTEXTO DA CONFIGURAÇÃO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Prepara a classificação da configuração dentro da organização do
     * framework. Para isso, precisamos receber um nome de contexto reconhecido.
     *
     * Se alguma verificação falhar, lançamos uma exceção. Quando o nome está
     * correto, guardamos o contexto em `$context`, deixando essa classificação
     * disponível para consulta.
     *
     *
     * _________________________________
     * @param mixed<string> $value  
     *    Nome do contexto da configuração. Precisa ser uma string presente em
     *    ALLOWED_CONFIG_CONTEXT.
     *
     *    O parâmetro aceita `mixed` para que possamos validar o tipo dentro do
     *    próprio método, sem conversões ou erros de tipo gerados pelo PHP.
     *    Neste caso aqui, precisa ser uma `string`.
     * _________________________________
     * @return void
     */

    private function setContext(mixed $value): void
    {
        /**
         * Fazemos duas verificações antes de guardar essa informação: primeiro
         * conferimos se o valor é uma string.
         */
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

        /**
         * Depois, se ele está entre os nomes de ALLOWED_CONFIG_CONTEXT. Essa
         * segunda verificação restringe a lista de possibilidades apenas à
         * lista permitida.
         */
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
     * 🔶 CONSULTA DO CONTEXTO DA CONFIGURAÇÃO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Retorna o nome do contexto guardado em $context, permitindo consultar a
     * classificação atribuída à configuração.
     *
     *
     * _________________________________
     * @return string  
     *    Retorna uma `string` com o nome do contexto da configuração.
     */
    
    public function getContext(): string
    {
        return $this->context;
    }


    
    /**
     * 🟢 VALIDAÇÃO E ATRIBUIÇÃO DO VALOR DA CONFIGURAÇÃO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método aplica as regras que preparamos para decidir se o valor
     * recebido pode ser armazenado na configuração.
     *
     * Ele é usado tanto pelo construtor, ao atribuir o valor padrão, quanto por
     * `Config::set()`, em possíveis alterações posteriores. Assim, o valor
     * inicial e os novos valores passam pelas mesmas verificações.
     *
     *
     * _________________________________
     * @param mixed $value  
     *    Valor que será atribuído à configuração. Pode ter qualquer tipo, desde
     *    que respeite todas as regras definidas para este objeto.
     * _________________________________
     * @return void
     */

    public function setValue(mixed $value): void
    {
        /**
         * Começamos pelos tipos de dados. Se houver uma lista de tipos
         * permitidos, o valor é testado e precisa corresponder a um deles.
         */
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

        /**
         * Depois, verificamos se seu tipo está entre os proibidos.
         */
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

        /**
         * Em seguida, conferimos as listas de valores permitidos e proibidos.
         * Essas comparações são estritas, ou seja, consideram tanto o conteúdo
         * quanto o tipo do dado.
         *
         * Por isso, o inteiro 1 e a string '1' são tratados como valores
         * diferentes.
         *
         * As regras são aplicadas em conjunto. Estar na lista de valores
         * permitidos não dispensa as outras verificações. Se o mesmo valor
         * também estiver entre os proibidos, ele será rejeitado.
         */
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

        /**
         * Depois, quando o valor recebido for diferente de uma string vazia,
         * aplicamos as expressões regulares que estiverem preenchidas. Primeiro
         * verificamos se ele corresponde ao padrão proibido; em seguida, se
         * atende ao padrão permitido.
         *
         * A string vazia pula apenas as verificações por expressão regular. Ela
         * ainda precisa respeitar as regras anteriores de tipos e valores.
         */
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
        
        /**
         * Quando uma regra não é atendida, lançamos uma exceção que identifica
         * a configuração e a falha encontrada. Também guardamos o valor
         * rejeitado nos dados da exceção, com a chave testedValue, para que o
         * código que capturar essa falha possa consultá-lo.
         *
         * Só atribuímos o valor à propriedade `$value` depois de concluir todas
         * as verificações. Se já houver um valor armazenado e a tentativa de
         * alteração for rejeitada, o valor anterior permanece.
         */

        $this->value = $value;
    }
    
    /**
     * 🔶 CONSULTA DO VALOR DA CONFIGURAÇÃO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Retorna o valor atual guardado em `$value`. É através deste método que
     * `Config::get()` recupera o conteúdo da configuração.
     *
     * O tipo do retorno depende da configuração consultada. Como o valor já
     * passou pelas verificações durante sua atribuição, aqui apenas recuperamos
     * o conteúdo armazenado.
     * 
     * 
     * _________________________________
     * @return mixed  
     *    Retorna o valor atual da configuração.
     */

    public function getValue(): mixed
    {
        return $this->value;
    }

    
    
    /**
     * 🟢 VERIFICAÇÃO DO TIPO DE DADO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método faz a comparação de tipos, usada nas verificações da classe.
     * Com ele, podemos conferir se um valor corresponde a um único tipo ou a
     * qualquer um dos tipos de uma lista.
     *
     * ✔️ EXEMPLO
     * ┌──────────────────────────────────────────────────────────────────────┐
     * │ Por exemplo, podemos informar `'string'`, que faz com que ele teste  │
     * │ se o valor é exclusivamente uma `string`. Mas se informarmos         │
     * │ `['bool', 'string']` então o teste aceita que o valor seja tanto uma │
     * │ string quanto um booleano.                                           │
     * └──────────────────────────────────────────────────────────────────────┘
     * 
     * 
     * ___________________________________________________________
     * @param array<string>|string $types
     *    Nome de um tipo de dado ou um array com os nomes dos tipos que
     *    queremos comparar, seguindo a identificação de `Tools::typeOf()`.
     *
     * @param mixed $value
     *    Valor cujo tipo será verificado.
     * ___________________________________________________________
     * @return bool
     *    Retorna `true` se o valor corresponder a pelo menos um dos tipos
     *    informados ou `false`, se nenhum corresponder.
     */

    private function isOfType(array|string $types, mixed $value): bool
    {
        /**
         * O método aceita que o parâmetro `$types` receba apenas um tipo
         * solto ou um array com vários. Por isso, testamos o que foi recebido
         * neste parâmetro: se foi uma string solta, convertemos ela em um array
         * com um único valor. Se ele já for um array, então já estará tudo
         * certo.
         *
         * É feito assim para que os dois formatos de entrada sigam a mesma
         * lógica de comparação a seguir.
         */
        
        if (Tools::typeOf($types) !== 'array') {
            $types = [$types];
        }

        
        
        /**
         * Comparamos o tipo do valor recebido com cada nome de tipo informado.
         * 
         * 💡 OBSERVAÇÃO
         * ┌─────────────────────────────────────────────────────────────┐
         * │ O termo da identificação segue os nomes utilizados pelo     │
         * │ framework, como 'bool', 'int', 'float' e 'string', conforme │
         * │ descrito no método `typeOf()` da classe `Tools`.            │
         * └─────────────────────────────────────────────────────────────┘
         * 
         * Ao encontrar uma correspondência, retornamos `true` imediatamente. Se
         * nenhum nome corresponder, retornamos `false`. Uma lista vazia também
         * resulta em `false`, pois não há nenhum tipo com o qual comparar.
         * 
         * ⚠️ IMPORTANTE
         * ┌─────────────────────────────────────────────────────────────────┐
         * │ A comparação não converte o valor recebido. Uma string contendo │
         * │ '123', por exemplo, continua sendo identificada como 'string'.  │
         * └─────────────────────────────────────────────────────────────────┘
         */
        
        foreach ($types as $type) {
            if (Tools::typeOf($value) === $type) {
                return true;
                break;
            }
        }

        return false;
    }
}
