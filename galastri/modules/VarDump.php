<?php
/**
 * -----------------------------------------------------------------------------
 * 📂
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                     ARQUIVO /galastri/modules/VarDump.php                 ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 * Este arquivo define a classe responsável por exibir valores para depuração,
 * usando o resultado do `var_dump()` do PHP como base.
 *
 * A classe pode apresentar esse resultado com formatação HTML, destacando
 * tipos, valores e estruturas de forma mais legível que o `var_dump()` padrão
 * do PHP.
 * ─────────────────────────────────────────────────────────────────────────────
 */

/**
 * 🟢 NAMESPACE DA CLASSE
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * VarDump pertence ao framework e oferece uma funcionalidade de módulo.
 *
 * ℹ️ OBSERVAÇÃO
 * ┌──────────────────────────────────────────────────────────────────────────┐
 * │ No bootstrap é criado um alias global `\VarDump` para esta classe. O     │
 * │ objetivo é remover a necessidade de informar todo o namespace quando for │
 * │ necessário acessar esta classe, já que por ser uma classe de depuração,  │
 * │ entende-se que deve estar facilmente acessível para o desenvolvedor da   │
 * │ aplicação.                                                               │
 * └──────────────────────────────────────────────────────────────────────────┘
 */

namespace galastri\modules;



/**
 * 🟢 CLASSE VarDump
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Disponibiliza ferramentas para formatar a saída padrao do var_dump de forma
 * mais legível ao desenvolvedor.
 *
 * Possui dois métodos principais:
 * ┌───────────┬──────────────────────────────────────────────────────────────┐
 * │ `print()` │ exibe o resultado do `var_dump()` reformatado para facilitar │
 * │           │ a leitura da depuração.                                      │
 * ├───────────┼──────────────────────────────────────────────────────────────┤
 * │ `raw()`   │ exibe o resultado original do `var_dump()` sem acrescentar   │
 * │           │ uma nova formatação, apenas estruturando melhor a saída da   │
 * │           │ depuração.                                                   │
 * └───────────┴──────────────────────────────────────────────────────────────┘
 *
 * Para aplicar a formatação, a classe percorre o texto gerado pelo PHP,
 * identifica cada tipo de valor e monta o HTML correspondente. Arrays e
 * objetos são percorridos também por dentro, permitindo formatar estruturas
 * aninhadas.
 */

final class VarDump
{
    
    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                               CONSTANTES                              ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     * 
     * 
     * 
     * 🟢 ARQUIVO DE ESTILOS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Define o caminho absoluto da folha de estilos utilizada na apresentação
     * dos valores pelo método `print()`.
     *
     * Seu conteúdo é incluído na saída dentro de um elemento <style>.
     *
     * LINK: /galastri/misc/vardump.css
     *
     * @var string
     */
    
    const STYLESHEET_FILE = PROJECT_DIR.'/galastri/misc/vardump.css';
    
    
    
    /**
     * 🟢 LIMITE DE PROFUNDIDADE DA FORMATAÇÃO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Limita a profundidade percorrida ao formatar arrays e objetos aninhados.
     *
     * Se a leitura ultrapassar esse limite, a formatação retorna `null`.
     * Nesse caso, `print()` exibe o dump original daquele valor, escapado
     * para HTML.
     *
     * @var int
     */
    
    const MAX_FORMAT_DEPTH = 128;
    
    

    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                              PROPRIEDADES                             ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     * 
     * 
     * 
     * 🟢 NÍVEL DE CONSULTA NA PILHA DE CHAMADAS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Indica quantos níveis devem ser avançados na pilha de chamadas para
     * localizar o arquivo e a linha apresentados pelo método `print()`.
     *
     * O valor inicial, mas quando funções auxiliares, como `vardump()` chamam,
     * esta classe como uma forma de abstração ao `\VarDump::print()`, é
     * necessário incrementar esse nível, para mostrar a origem da chamada feita
     * pelo desenvolvedor e não pelo `vardump()`.
     *
     * @var int
     */
    
    private static int $backTraceLevel = 0;
    
    
    
    /**
     * 🟢 POSIÇÃO DE LEITURA DO DUMP
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Armazena a posição em que a próxima leitura do texto deve começar.
     *
     * O método `print()` vai processando a saída do `var_dump()` original em
     * trechos. Antes dele avançar para o próximo trecho, temos que armazenar em
     * qual caractere ele parou, para que, quando ele prosseguir com o
     * processamento, ele continue a partir da posição armazenada aqui.
     *
     * @var int
     */
    
    private static int $dumpPointerPosition = 0;
    
    
    
    /**
     * 🟢 MARCADOR DE REFERÊNCIA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Guarda o marcador `&` quando ele aparecer no `var_dump()` em valores
     * ligados por referência.
     * 
     * ✔️ EXEMPLO
     * ┌───────────────────────────────────────────────────────────────────────┐
     * │ $numero = 10;                                                         │
     * │ $outroNumero = &$numero;                                              │
     * │                                                                       │
     * │ O `&` cria uma ligação referencial entre estas variáveis, ou seja,    │
     * │ as duas compartilham as alterações de uma pra outra.                  │
     * │                                                                       │
     * │ Então, neste caso, se definirmos `$outroNumero = 20;`,                │
     * │ automáticamente, a variável `$numero` também passa a armazenar o      │
     * │ valor `20`.                                                           │
     * │                                                                       │
     * │ E o inverso também, se `$numero = 30;`, $outroNumero também retornará │
     * │ o valor `30` quando lida.                                             │
     * │                                                                       │
     * │ E isso é importante pra depuração com `var_dump()`.                   │
     * └───────────────────────────────────────────────────────────────────────┘
     *
     * Esta propriedade, portanto, armazena o `&` para ser exibida junto com um
     * valor quando ele for de referência.
     *
     * Como a saída é em HTML, usamos `&amp;` para representar o `&` e garantir
     * a exibição correta do caractere. Quando o valor não tem esse marcador,
     * deixamos a propriedade vazia.
     *
     * @var string
     */
    
    private static string $refMarker = '';
    
    
    
    /**
     * 🟢 PROFUNDIDADE ATUAL DA LEITURA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Guarda em qual nível estamos durante a leitura do dump. Um array pode ser
     * multidimensional, ou seja, dentro dele pode haver outro array, que pode
     * ter mais um array e assim por diante.
     *
     * Começamos no nível zero. Sempre que entramos para ler um valor dentro de
     * um array ou objeto, aumentamos esse contador. Quando terminamos essa
     * leitura, diminuímos para voltar ao nível anterior.
     *
     * Isso é necessário porque o var_dump() coloca mais espaços à esquerda
     * conforme entra nesses níveis e precisamos reconhecer em qual deles
     * estamos e aplicar os recuos e o fechamento de cada estrutura no texto.
     *
     * ℹ️ OBSERVAÇÃO
     * ┌───────────────────────────────────────────────────────────────────────┐
     * │ Também usamos esse contador para saber quando ultrapassamos o limite  │
     * │ definido em `MAX_FORMAT_DEPTH`. Se isso acontecer, paramos de tentar  │
     * │ formatar aquele dump e exibimos sua saída original.                   │
     * └───────────────────────────────────────────────────────────────────────┘
     *
     * @var int
     */
    
    private static int $currentDepth = 0;

    
    
    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                                 MÉTODOS                               ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     *
     *
     *
     * 🟢 CLASSE ESTÁTICA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Os recursos desta classe são acessados diretamente por chamadas como
     * `\VarDump::print($valor)`, sem precisar criar um objeto.
     *
     * Por isso, deixamos o construtor privado para impedir que a classe seja
     * instanciada com `new VarDump()`.
     */
    
    private function __construct() {}
    
    
    
    /**
     * 🟢 `var_dump()` COM FORMATAÇÃO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Recebe os valores que queremos depurar e exibe o resultado do
     * `var_dump()` com uma apresentação mais fácil de ler, destacando os tipos,
     * os valores e a organização de arrays e objetos.
     *
     * Para isso, capturamos o texto gerado pelo `var_dump()` e percorremos
     * esse conteúdo em partes, identificando o que precisa ser formatado.
     *
     * Os métodos identificam cada valor e montam o HTML. A folha de estilos
     * também é incluída na saída para aplicar as cores e os demais ajustes
     * visuais.
     *
     * Podemos passar vários valores na mesma chamada, igual como fazemos no
     * `var_dump()` nativo do PHP. Cada um é processado e exibido separadamente.
     *
     * ✔️ EXEMPLO
     * ┌───────────────────────────────────────────────────────────────────────┐
     * │ $dados = ['nome' => 'André', 'ativo' => true];                        │
     * │ $numero = 123;                                                        │
     * │                                                                       │
     * │ \VarDump::print($dados, $numero);                                     │
     * │                                                                       │
     * │ O array e o número são depurados separadamente e são mostrados em     │
     * │ resultados separados.                                                 │
     * └───────────────────────────────────────────────────────────────────────┘
     *
     * O resultado também apresenta um cabeçalho que mostra o nome do arquivo e
     * em qual linha a chamada foi efetuada. Isso ajuda a encontrar o trecho do
     * código que estamos depurando.
     *
     * ℹ️ OBSERVAÇÃO
     * ┌───────────────────────────────────────────────────────────────────────┐
     * │ A classe lê o texto produzido pelo var_dump() para reconhecer         │
     * │ strings, números, arrays etc. Se encontrar algo que não consiga       │
     * │ interpretar, pode acabar formatando só uma parte daquele resultado.   │
     * │                                                                       │
     * │ Nesse caso, ela descarta essa tentativa e mostra o texto completo que │
     * │ o var_dump() produziu. Você perde a formatação especial daquele       │
     * │ resultado, mas consegue consultar os dados.                           │
     * │                                                                       │
     * │ Esse texto será colocado numa página HTML. Se o valor depurado        │
     * │ contiver <b>Teste</b>, por exemplo, queremos enxergar essas tags      │
     * │ escritas, e não apenas a palavra Teste em negrito.                    │
     * │                                                                       │
     * │ Por isso, os caracteres são convertidos para aparecerem sempre como   │
     * │ texto literal.                                                        │
     * └───────────────────────────────────────────────────────────────────────┘
     *
     * @param mixed ...$valueList       Valores que serão exibidos para
     *                                  depuração.
     *
     * @return void
     */
    
    public static function print(mixed ...$valueList): void
    {
        /**
         * Começamos guardando em $backtraceLevel o nível que será usado para
         * localizar a origem da chamada. Essa informação vai ajudar a montar o
         * cabeçalho com o arquivo e a linha onde chamamos o `VarDump::print()`.
         *
         * Depois, reiniciamos a propriedade da classe em zero para que esse
         * ajuste não influencie as próximas chamadas de `print()`. Como já
         * guardamos uma cópia na variável local, continuamos com o nível
         * necessário para esta execução.
         */
        
        $backtraceLevel = self::$backTraceLevel;
        self::resetBacktraceLevel();
        
        
        
        /**
         * Aqui pedimos ao PHP o histórico de chamadas que nos trouxe até o
         * ponto do código em que `print()` foi chamado e guardamos o resultado
         * em $debugBacktrace.
         *
         * O resultado é um array, no qual cada posição representa um nível
         * desse histórico. Usamos $backtraceLevel para escolher o nível que
         * queremos consultar.
         *
         * O DEBUG_BACKTRACE_IGNORE_ARGS pede para não incluir nesse histórico
         * os parâmetros passados às funções, pois só precisamos localizar a
         * chamada.
         *
         * O trecho `$backtraceLevel + 1` limita quantos níveis serão
         * recuperados: como as posições começam em zero, precisamos de dois
         * níveis para acessar a posição 1, por exemplo.
         */
        
        $debugBacktrace = debug_backtrace(
            DEBUG_BACKTRACE_IGNORE_ARGS, $backtraceLevel + 1
        );
        
        
        
        /**
         * Aqui, pegamos os campos `file` e `line`, que informam o arquivo e a
         * linha da chamada, informando também qual é o nível do histórico que
         * queremos. Se essas informações não estiverem disponíveis, usamos
         * 'Desconhecido' e `0` respectivamente.
         * 
         * Tais valores são armazenados nas variáveis $debugFile e $debugLine.
         */
        
        $debugFile = $debugBacktrace[$backtraceLevel]['file'] ?? 'Desconhecido';
        $debugLine = $debugBacktrace[$backtraceLevel]['line'] ?? 0;
        
        
        
        /**
         * Agora passamos os valores para o método `getVarDumpOutput()`, que irá
         * executar a função `var_dump()` do PHP para cada valor recebido.
         *
         * O método `getVarDumpOutput()` guarda as saídas de cada `var_dump()`
         * em um array, com um resultado separado para cada valor. Assim,
         * podemos trabalhar na apresentação de cada um antes de exibi-los.
         */
        
        $varDumpOutput = self::getVarDumpOutput(...$valueList);

        
        
        /**
         * Lemos o arquivo CSS que define a aparência da saída formatada.
         *
         * Seu conteúdo será colocado junto com o HTML dos resultados. Assim, a
         * saída já leva os estilos de que precisa, sem exigir que a aplicação
         * inclua esse arquivo CSS na página.
         */
        
        $varDumpCss = file_get_contents(self::STYLESHEET_FILE);
        
        
        
        /**
         * Preparamos o nome do arquivo e o número da linha para colocá-los no
         * HTML. O escapeHtml() faz caracteres especiais aparecerem como texto,
         * evitando que sejam interpretados como parte da página.
         *
         * Depois, acrescentamos <wbr> antes de cada / do caminho. Essa tag
         * permite que o navegador quebre a linha nesses pontos, caso o caminho
         * seja muito comprido para caber no espaço disponível.
         */
        
        $currentFile = self::escapeHtml($debugFile);
        $currentLine = self::escapeHtml($debugLine);
        
        $origin = str_replace('/', '<wbr>/', $currentFile);

        
        
        /**
         * Finalmente, começamos a montar a apresentação do dump.
         *
         * 1. Colocamos o conteúdo CSS dentro de uma tag <style>.
         *
         * 2. Abrimos a <div class="vardump"> que vai agrupar o cabeçalho e
         *    todos os resultados. A classe CSS `vardump` permite direcionar
         *    os estilos para essa apresentação.
         */
        
        echo '<style>'.$varDumpCss.'</style>';
        echo '<div class="vardump">';
        echo '<h2><b>var_dump</b></h2>';
        echo '<div><b>ORIGIN: </b>' . $origin . '</div>';
        echo '<div><b>LINE: </b>'.$currentLine. '</div>';
        echo '<br>';
        echo '<div><b>VALUES: </b></div>';
        
        

        /**
         * O que construímos até então é a estrutura inicial. Agora precisamos
         * percorrer todos os resultados capturados.
         *
         * ✔️ EXEMPLO
         * ┌───────────────────────────────────────────────────────────────────┐
         * │ Se passamos três valores para `print()`, esse loop será           │
         * │ executado três vezes, uma para cada dump.                         │
         * └───────────────────────────────────────────────────────────────────┘
         *
         * Neste `foreach()`, a variável `$currentOutput` contém a saída do
         * `var_dump()` que vamos processar em cada passagem e $key será usada
         * para numerar sua apresentação.
         */
        
        foreach ($varDumpOutput as $key => $currentOutput) {
            
            /**
             * Antes de começar a leitura da saída de cada `var_dump()`, zeramos
             * a profundidade e a posição de leitura. Isso é necessário pois
             * cada dump precisa ser processado do início do texto, sem
             * continuar da posição em que paramos no dump anterior.
             */
            
            self::$currentDepth = 0;
            self::resetDumpPointerPosition();
            
            
            
            /**
             * Passamos o texto para `applyFormats()`, que identifica os valores
             * e monta a versão em HTML do dump. Guardamos esse resultado em
             * $formattedDump.
             */
            $formattedDump = self::applyFormats($currentOutput);
            
            
            
            /**
             * Depois, verificamos se a formatação retornou `null`, o que
             * indicaria que `applyFormats()` não conseguiu processar o
             * conteúdo.
             */
            $isFormattedDumpNull = $formattedDump === null;
            
            
            
            /**
             * E também testamos se a posição de leitura chegou ao final do
             * texto. Essa segunda verificação é importante porque podemos ter
             * conseguido formatar o começo do dump, mas deixado algum trecho
             * sem ler.
             */
            $doesPointerDiff = self::$dumpPointerPosition !== strlen($currentOutput);

            
            
            /**
             * Se a formatação falhou ou deixou parte do texto sem ler,
             * abandonamos a tentativa atual e usamos o dump original completo
             * daquele valor.
             *
             * Assim, o usuário ainda consegue consultar os dados, mesmo que não
             * se tenha conseguido aplicar a formatação especial.
             *
             * O escapeHtml() prepara esse texto para aparecer no navegador,
             * incluindo possíveis tags HTML que façam parte dos valores. Se
             * algum valor contiver <b>Teste</b>, iremos enxergar essas tags
             * escritas na depuração, e não apenas "Teste" em negrito.
             */
            if ($isFormattedDumpNull || $doesPointerDiff) {
                $formattedDump = self::escapeHtml($currentOutput);
            }

            
            
            /**
             * Imprimimos o resultado, que é formado pelo número do resultado e,
             * abaixo dele, o conteúdo do var_dump() estruturado que acabamos de
             * preparar.
             *
             * Somamos 1 ao valor de $key porque o array começa na posição zero,
             * mas queremos apresentar RESULT 1, RESULT 2 e assim por diante.
             *
             * A tag <pre> preserva os espaços e as quebras de linha, mantendo a
             * organização visual dos dados. Depois disso, o laço segue para o
             * próximo resultado e segue assim até o último `var_dump()`
             * processado.
             */
            echo '<div class="division">RESULT '.($key + 1).'</div>';
            echo '<pre>'.$formattedDump.'</pre>';
        }

        
        
        /**
         * Por fim, fechamos a <div> aberta no início da apresentação, depois de
         * exibir todos os valores recebidos.
         */
        echo '</div>';
    }

    
    
    /**
     * 🟢 SAÍDA ORIGINAL DO var_dump()
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Exibe o texto produzido pelo `var_dump()`, preservando a organização
     * original dos valores mas sem passar pelos métodos de formatação da
     * classe.
     *
     * Apenas colocamos esse texto dentro de uma tag <pre> para que os espaços e
     * as quebras de linha sejam respeitados pelo navegador. Isso mantém os
     * recuos que ajudam a enxergar a estrutura de arrays e objetos.
     *
     * Podemos passar vários valores na mesma chamada, como fazemos com o
     * var_dump() do PHP.
     *
     * @param mixed ...$valueList       Valores que serão exibidos para
     *                                  depuração.
     * @return void
     */
    public static function raw(mixed ...$valueList): void
    {
        /**
         * Abrimos o bloco <pre> que vai preservar a organização do texto
         * enquanto os resultados forem exibidos.
         */
        echo '<pre>';
        
        
        
        /**
         * Primeiro, getVarDumpOutput() captura a saída do var_dump() para cada
         * valor recebido e devolve esses resultados em um array.
         *
         * O implode() junta esses textos, colocando uma quebra de linha entre
         * eles para separar os resultados.
         *
         * Depois, escapeHtml() prepara o conteúdo para aparecer como texto no
         * navegador. Se um valor contiver <b>Teste</b>, por exemplo, veremos
         * essas tags escritas, em vez de apenas "Teste" em negrito.
         *
         * Por fim, o echo exibe o conteúdo preparado.
         */
        echo self::escapeHtml(
            implode("\n", self::getVarDumpOutput(...$valueList))
        );
        
        
        
        /**
         * Fechamos o bloco <pre> depois de exibir todos os resultados.
         */
        echo '</pre>';
    }
    
    
    
    public static function addBacktraceLevel(): void
    {
        self::$backTraceLevel++;
    }

    private static function getVarDumpOutput(mixed ...$valueList): array
    {
        $varDumpOutput = [];
        ob_start();

        try {
            foreach ($valueList as $value) {
                var_dump($value);
                $varDumpOutput[] = ob_get_contents();
                ob_clean();
            }
        } finally {
            ob_end_clean();
        }

        return $varDumpOutput;
    }
    
    private static function getMatches(string $regex, string $currentOutput): array | bool
    {
        $hasMatch = preg_match($regex, $currentOutput, $matches, 0, self::$dumpPointerPosition) === 1;
        
        if ($hasMatch) {
            return $matches;
        }
        
        return false;
    }
    
    private static function applyFormats(string $dump): ?string
    {
        if (self::$currentDepth > self::MAX_FORMAT_DEPTH) {
            return null;
        }

        self::$refMarker = '';

        if (substr($dump, self::$dumpPointerPosition, 1) === '&') {
            self::$refMarker = '&amp;';
            self::moveDumpPointerForward(1);
        }

        $regex = [
            'string'        => '/\G(string\((\d+)\)) "/',
            'array'         => '/\G(array\((\d+)\)) \{\n/',
            'object'        => '/\G((?:lazy (?:ghost|proxy) )?object\([^\r\n]*\)#\d+ \((\d+)\)) \{\n/',
            'numeric'       => '/\G(int|float)\(([^()\r\n]*)\)\n/',
            'boolean'       => '/\Gbool\((true|false)\)\n/',
            'recursion'     => '/\G\*RECURSION\*\n/',
            'uninitialized' => '/\Guninitialized\(([^\r\n]*)\)\n/',
            'resource'      => '/\Gresource\((\d+)\) of type \(([^\r\n]*)\)\n/',
            'enum'          => '/\Genum\(([^\r\n]*)\)\n/',
            'unknown'       => '/\GUNKNOWN:0\n/',
        ];

        if ($matches = self::getMatches($regex['string'], $dump)) {
            return self::stringFormatting($dump, $matches);
        }
        
        if ($matches = self::getMatches($regex['array'], $dump)) {
            return self::arrayFormatting($dump, $matches);
        }

        if ($matches = self::getMatches($regex['object'], $dump)) {
            return self::objectFormatting($dump, $matches);
        }
        
        if ($matches = self::getMatches($regex['numeric'], $dump)) {
            return self::numericFormatting($matches);
        }

        if ($matches = self::getMatches($regex['boolean'], $dump)) {
            return self::booleanFormatting($matches);
        }

        if ($matches = self::getMatches($regex['recursion'], $dump)) {
            return self::recursionFormatting($matches);
        }

        if ($matches = self::getMatches($regex['uninitialized'], $dump)) {
            return self::uninitializedFormatting($matches);
        }

        if ($matches = self::getMatches($regex['resource'], $dump)) {
            return self::resourceFormatting($matches);
        }

        if ($matches = self::getMatches($regex['enum'], $dump)) {
            return self::enumFormatting($matches);
        }

        if ($matches = self::getMatches($regex['unknown'], $dump)) {
            return self::unknownFormatting($matches);
        }
        
        $nullMatches = substr($dump, self::$dumpPointerPosition, 5) === "NULL\n";

        if ($nullMatches) {
            return self::nullFormatting();
        }
        
        return null;
    }
    
    private static function stringFormatting(string $currentoutput, array $matches): ?string
    {
        $refMarker = self::$refMarker;

        $matchLength = strlen($matches[0]);
        $outputlength = $matches[2];

        self::moveDumpPointerForward($matchLength);

        if ($outputlength > strlen($currentoutput) - self::$dumpPointerPosition - 2) {
            return null;
        }

        $value = substr($currentoutput, self::$dumpPointerPosition, $outputlength);

        self::moveDumpPointerForward($outputlength);

        if (substr($currentoutput, self::$dumpPointerPosition, 2) !== "\"\n") {
            return null;
        }

        self::moveDumpPointerForward(2);

        return $refMarker.self::applyHtml('string-value', '"'.$value.'"').' '.self::applyHtml('string-type', $matches[1])."\n";
    }

    private static function arrayFormatting(string $currentOutput, array $matches): ?string 
    {
        $refMarker = self::$refMarker;
        
        $matchLength = strlen($matches[0]);
        $arrayHeader = $matches[1];
        $keyCount = $matches[2];

        self::moveDumpPointerForward($matchLength);

        $parentIndent = str_repeat(' ', self::$currentDepth * 2);
        $childIndent = $parentIndent.'  ';

        $htmlHeader = self::applyHtml('array-type', $arrayHeader)." {\n";
        $htmlMiddle = '';
        $htmlClosing = $parentIndent."}\n";
        
        $keyRegex = '/\G'.$childIndent.'(\[(?:-?\d+|"[\s\S]*?")\])=>\n'.$childIndent.'/';

        for ($index = 0; $index < $keyCount; $index++) {
            if (!($keyMatches = self::getMatches($keyRegex, $currentOutput))) {
                return null;
            }

            $keyMatchLength = strlen($keyMatches[0]);
            $keyValue = $keyMatches[1];

            self::moveDumpPointerForward($keyMatchLength);

            self::$currentDepth++;
            $nextKeyContent = self::applyFormats($currentOutput);
            self::$currentDepth--;

            if ($nextKeyContent === null) {
                return null;
            }

            $htmlMiddle .= ($childIndent. self::applyHtml('array-key', $keyValue). ' =&gt; '.$nextKeyContent);
        }

        $currentText = substr($currentOutput, self::$dumpPointerPosition, strlen($htmlClosing));

        if ($currentText !== $htmlClosing) {
            return null;
        }
        
        $htmlClosingLength = strlen($htmlClosing);
        self::moveDumpPointerForward($htmlClosingLength);

        return $refMarker.$htmlHeader.$htmlMiddle.$htmlClosing;
    }

    private static function objectFormatting(string $currentOutput, array $matches): ?string
    {
        $refMarker = self::$refMarker;

        $matchLength = strlen($matches[0]);
        $objectHeader = $matches[1];

        self::moveDumpPointerForward($matchLength);

        $parentIndent = str_repeat(' ', self::$currentDepth * 2);
        $childIndent = $parentIndent.'  ';

        $htmlHeader = self::applyHtml('object-header', $objectHeader)." {\n";
        $htmlMiddle = '';
        $htmlClosing = $parentIndent."}\n";

        $keyRegex = '/\G'.$childIndent.'(\[(?:-?\d+|"[\s\S]*?"(?::(?:private|protected))?)\])=>\n'.$childIndent.'/';
        $propertyRegex = '/:(private|protected)\]$/';

        while (true) {
            $currentText = substr($currentOutput, self::$dumpPointerPosition, strlen($htmlClosing));

            if ($currentText === $htmlClosing) {
                break;
            }

            if (!($matches = self::getMatches($keyRegex, $currentOutput))) {
                return null;
            }

            $keyMatchLength = strlen($matches[0]);
            $propertyValue = $matches[1];

            self::moveDumpPointerForward($keyMatchLength);

            self::$currentDepth++;
            $nextKeyContent = self::applyFormats($currentOutput);
            self::$currentDepth--;

            if ($nextKeyContent === null) {
                return null;
            }

            $cssKeyClassName = 'object-key';

            if (preg_match($propertyRegex, $propertyValue, $visibility) === 1) {
                $cssKeyClassName .= ' '.$visibility[1];
            }

            $htmlMiddle .= $childIndent.self::applyHtml($cssKeyClassName, $propertyValue).' =&gt; '.$nextKeyContent;
        }

        self::moveDumpPointerForward(strlen($htmlClosing));

        return $refMarker.$htmlHeader.$htmlMiddle.$htmlClosing;
    }

    private static function numericFormatting(array $matches): ?string
    {
        $refMarker = self::$refMarker;

        $matchLength = strlen($matches[0]);
        $numericType = $matches[1];
        $outputlength = $matches[2];

        self::moveDumpPointerForward($matchLength);

        return $refMarker.self::applyHtml('numeric-value', $outputlength).' '.self::applyHtml('numeric-type', $numericType)."\n";
    }

    private static function booleanFormatting(array $matches): ?string
    {
        $refMarker = self::$refMarker;

        $matchLength = strlen($matches[0]);
        $booleanValue = $matches[1];

        self::moveDumpPointerForward($matchLength);

        return $refMarker.self::applyHtml($booleanValue.'-value', '('.$booleanValue.')').' '.self::applyHtml($booleanValue.'-type', 'bool')."\n";
    }

    private static function recursionFormatting(array $matches): ?string
    {
        $refMarker = self::$refMarker;

        $matchLength = strlen($matches[0]);

        self::moveDumpPointerForward($matchLength);

        return $refMarker.self::applyHtml('recursion', '*RECURSION*')."\n";
    }

    private static function uninitializedFormatting(array $matches): ?string
    {
        $refMarker = self::$refMarker;

        $matchLength = strlen($matches[0]);
        $declaredType = $matches[1];
        
        $partListAsHtml = [];
        $classes = [
            'int' => 'numeric-type',
            'float' => 'numeric-type',
            'string' => 'string-type',
            'bool' => 'boolean-type',
            'true' => 'true-type',
            'false' => 'false-type',
            'null' => 'null-type',
            'array' => 'array-type',
            'object' => 'object-header',
            'mixed' => 'generic-type',
            'iterable' => 'generic-type'
        ];

        self::moveDumpPointerForward($matchLength);

        // Separar os tipos, preservando os operadores e os parênteses.
        $partList = preg_split('/([?&|()])/', $declaredType, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        if ($partList === false) {
            return null;
        }

        foreach ($partList as $part) {
            if (in_array($part, ['?', '&', '|', '(', ')'], true)) {
                $partListAsHtml[] = self::escapeHtml($part);
            } else {
                // Nomes de classes e interfaces recebem o estilo de objeto.
                $class = $classes[$part] ?? 'object-header';
                $partListAsHtml[] = self::applyHtml($class, $part);
            }
        }

        return $refMarker.self::applyHtml('uninitialized', 'uninitialized').' '.implode($partListAsHtml)."\n";
    }

    private static function resourceFormatting(array $matches): ?string
    {
        $refMarker = self::$refMarker;

        $matchLength = strlen($matches[0]);
        $resourceId = $matches[1];
        $resourceKind = $matches[2];

        self::moveDumpPointerForward($matchLength);

        return $refMarker.self::applyHtml('resource-value', $resourceId).' '.self::applyHtml('resource-type', 'resource').' '.self::applyHtml('resource-kind', '('.$resourceKind.')')."\n";
    }

    private static function enumFormatting(array $matches): ?string
    {
        $refMarker = self::$refMarker;

        $matchLength = strlen($matches[0]);
        $enumCase = $matches[1];

        self::moveDumpPointerForward($matchLength);

        return $refMarker.self::applyHtml('enum-value', $enumCase).' '.self::applyHtml('enum-type', 'enum')."\n";
    }

    private static function unknownFormatting(array $matches): ?string
    {
        $refMarker = self::$refMarker;

        $matchLength = strlen($matches[0]);

        self::moveDumpPointerForward($matchLength);

        return $refMarker.self::applyHtml('unknown', 'UNKNOWN:0')."\n";
    }

    private static function nullFormatting(): ?string
    {
        $refMarker = self::$refMarker;

        self::moveDumpPointerForward(5);

        return $refMarker.self::applyHtml('null', 'NULL')."\n";
    }

    private static function applyHtml(string $class, string $value): string
    {
        return '<span class="'.$class.'">'.self::escapeHtml($value).'</span>';
    }

    private static function escapeHtml(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
    
    private static function moveDumpPointerForward(int $moves): void
    {
        self::$dumpPointerPosition += $moves;
    }

    private static function resetDumpPointerPosition(): void
    {
        self::$dumpPointerPosition = 0;
    }

    private static function resetBacktraceLevel(): void
    {
        self::$backTraceLevel = 0;
    }
}
