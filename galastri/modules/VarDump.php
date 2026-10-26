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
     * A folha de estilos fica neste arquivo abaixo:
     * 🔗 LINK: /galastri/misc/vardump.css
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
     * O valor inicial é zero, mas quando funções auxiliares, como `vardump()`
     * chamam esta classe como uma forma de abstração ao `\VarDump::print()`, é
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
     * qual byte ele parou, para que, quando ele prosseguir com o
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
     * │ automaticamente, a variável `$numero` também passa a armazenar o      │
     * │ valor `20`.                                                           │
     * │                                                                       │
     * │ E o inverso também, se `$numero = 30;`, $outroNumero também retornará │
     * │ o valor `30` quando lida.                                             │
     * │                                                                       │
     * │ E isso é importante pra depuração com `var_dump()`.                   │
     * └───────────────────────────────────────────────────────────────────────┘
     *
     * Esta propriedade, portanto, armazena o `&` para ser exibido junto com um
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
     * 
     * ---
     * @param mixed ...$valueList  
     * :: Valores que serão exibidos para depuração.
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
        echo '  <h2><b>var_dump</b></h2>';
        echo '  <div><b>ORIGIN: </b>'.$origin.'</div>';
        echo '  <div><b>LINE: </b>'.$currentLine.'</div>';
        echo '  <br>';
        echo '  <div><b>VALUES: </b></div>';
        
        

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
            
            echo '  <div class="division">RESULT '.($key + 1).'</div>';
            echo '  <pre>'.$formattedDump.'</pre>';
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
     * 
     * ---
     * @param mixed ...$valueList  
     * :: Valores que serão exibidos para depuração.
     * 
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
        
        $varDumpOutput = self::getVarDumpOutput(...$valueList);
        $varDumpOutput = implode("\n", $varDumpOutput);
        
        echo self::escapeHtml($varDumpOutput);
        
        
        
        /**
         * Fechamos o bloco <pre> depois de exibir todos os resultados.
         */
        
        echo '</pre>';
    }
    
    
    
    /**
     * 🟢 AJUSTANDO A ORIGEM DA CHAMADA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método aumenta o nível consultado no histórico para passar por
     * chamadas intermediárias e encontrar quem chamou a função auxiliar.
     * 
     * ✔️ EXEMPLO
     * ┌───────────────────────────────────────────────────────────────────────┐
     * │ A função auxiliar `vardump()` chama `\VarDump::print()` por dentro.   │
     * │ Se consultarmos a origem sem nenhum ajuste, o cabeçalho vai apontar   │
     * │ para essa chamada, como se o usuário tivesse executado a `print()` a  │
     * │ partir de `vardump()` e não a partir do local onde ele chamou         │
     * │ `vardump()` que é o trecho que o desenvolvedor quer depurar.          │
     * └───────────────────────────────────────────────────────────────────────┘
     *
     * Cada chamada acrescenta um nível. O próximo `print()` usa esse ajuste e
     * depois reinicia a propriedade para não afetar as chamadas seguintes.
     *
     * 
     * ---
     * @return void
     */
    
    public static function addBacktraceLevel(): void
    {
        self::$backTraceLevel++;
    }



    /**
     * 🟢 CAPTURANDO O TEXTO DO var_dump()
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * O `var_dump()` do PHP normalmente escreve seu resultado diretamente na
     * saída. Aqui precisamos guardar esse texto primeiro, pois `print()` ainda
     * vai trabalhar na formatação antes de exibi-lo.
     *
     * Para isso, abrimos um buffer que segura temporariamente o que o
     * `var_dump()` produzir. Depois, recolhemos esse conteúdo e armazenamos ele
     * em um array. Fazemos isso para cada resultado do `var_dump()`.
     *
     * O método também é usado por `raw()`, que aproveita esses mesmos textos
     * para montar sua apresentação.
     *
     * 
     * ---
     * @param mixed ...$valueList  
     * :: Valores que serão depurados pelo var_dump().
     * 
     * @return string[] Textos produzidos, na ordem dos valores recebidos.
     */
    
    private static function getVarDumpOutput(mixed ...$valueList): array
    {
        /**
         * Preparamos o array que vai armazenar os resultados e abrimos nosso
         * buffer. A partir daqui, a saída do `var_dump()` vai sendo
         * guardada neste buffer, enquanto não encerrarmos essa captura.
         */
        
        $varDumpOutput = [];
        
        ob_start();

        
        
        /**
         * Colocamos a captura dentro de um try/finally para garantir que o
         * buffer seja encerrado mesmo se alguma exceção interromper o
         * processamento. Isso evita deixar essa captura aberta para o restante
         * da aplicação.
         */
        
        try {
            foreach ($valueList as $value) {
                
                /**
                 * Para cada valor a ser depurado, executamos um `var_dump()`.
                 * O texto produzido fica no buffer. Em seguida, pegamos esse
                 * conteúdo com `ob_get_contents()` e guardamos dentro do array.
                 *
                 * Em seguida limpamos o buffer com `ob_clean()` para que o
                 * próximo resultado não venha misturado com o anterior.
                 * 
                 * ℹ️ INFORMAÇÃO
                 * ┌───────────────────────────────────────────────────────────┐
                 * │ O ob_clean() apaga o conteúdo, mas mantém o buffer aberto │
                 * │ para a próxima passagem do loop.                          │
                 * └───────────────────────────────────────────────────────────┘
                 */
                
                var_dump($value);
                $varDumpOutput[] = ob_get_contents();
                ob_clean();
            }
            
            
            
        } finally {
            
            /**
             * Encerramos o buffer. Os textos que precisamos já foram copiados
             * para o array.
             */
            
            ob_end_clean();
        }

        
        
        /**
         * Devolvemos os resultados para quem chamou o método decidir como
         * exibi-los.
         */
        
        return $varDumpOutput;
    }
    


    /**
     * 🟢 RECONHECENDO UM TRECHO DO DUMP
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Durante a leitura, precisamos descobrir o que começa na posição atual:
     * uma string, um array, um número ou algum outro conteúdo conhecido.
     *
     * Este método recebe uma expressão regular, que descreve o formato
     * procurado, e testa se o dump combina com ela. Se combinar, devolvemos os
     * trechos que corresponderam ao regex para que o método de formatação possa
     * usá-los.
     *
     * Por exemplo, ao reconhecer `string(5)`, podemos separar a indicação do
     * tipo e o tamanho `5`, que será necessário para ler o valor da string.
     *
     * 
     * ---
     * @param string $regex  
     * :: Expressão que descreve o trecho procurado.
     * 
     * @param string $currentOutput  
     * :: Texto do dump que estamos lendo.
     * 
     * @return array<int, string>|false Trechos encontrados ou false.
     */
    
    private static function getMatches(string $regex, string $currentOutput): array|bool
    {
        /**
         * Começamos executando um `preg_match()` para buscar se existe algum
         * valor de `$currentOutput` que corresponda ao `$regex` recebido.
         *
         * A busca é feita a partir da posição em que paramos uma leitura
         * anterior, caso já tenha ocorrido. As expressões usadas aqui começam
         * com `\G`, que exige encontrar o trecho exatamente nessa posição.
         * Assim, não pulamos um pedaço desconhecido para reconhecer outro mais
         * adiante.
         *
         * O retorno 1 indica que houve correspondência. O texto encontrado e
         * suas partes ficam no array $matches.
         */
        
        $hasMatch = preg_match(
            $regex, $currentOutput, $matches, 0, self::$dumpPointerPosition
        );
        
        
        
        /**
         * Se houver correspondências, devolvemos as capturas armazenadas na
         * array `$matches`, que é formada da seguinte forma:
         * 
         * ┌──────────────────────┬───────────────────────────────────────────┐
         * │ `$matches[0]`          │ Fica a correspondência completa           │
         * ├──────────────────────┼───────────────────────────────────────────┤
         * │ `$matches[1]` e demais │ Ficam as partes separadas pelos grupos da │
         * │                      │ expressão regular.                        │
         * └──────────────────────┴───────────────────────────────────────────┘
         */
        
        if ($hasMatch === 1) {
            return $matches;
        }
        
        
        
        /**
         * Se não houver correspondência, devolvemos `false` para que a leitura
         * possa tentar outro formato. O ponteiro da posição de busca se mantém
         * para que outro regex posterior também teste o mesmo trecho.
         */
        
        return false;
    }
    


    /**
     * 🟢 RECONHECENDO O TIPO E APLICANDO A FORMATAÇÃO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Este método olha o texto da saída do `var_dump()` recebido a partir da
     * posição atual, reconhece qual o tipo de dado o valor possui e chama o
     * método que sabe formatá-lo.
     *
     * ✔️ EXEMPLO
     * ┌───────────────────────────────────────────────────────────────────────┐
     * │ Se encontrar `string(...)`, por exemplo, ele passa o trecho para      │
     * │ `stringFormatting()`, que vai aplicar a formatação padrão de strings. │
     * │                                                                       │
     * │ Se encontrar `array(...)`, então chama a formatação de arrays com     │
     * │ `arrayFormatting()`.                                                  │
     * │                                                                       │
     * │ E assim por diante...                                                 │
     * └───────────────────────────────────────────────────────────────────────┘
     *
     * Isso também acontece dentro de arrays e objetos. Ao chegar no valor de
     * uma entrada, chamamos este método novamente para descobrir como lê-lo.
     * Todos acompanham a mesma posição no texto, então cada leitura continua de
     * onde a anterior parou.
     *
     * Quando não conseguimos reconhecer o conteúdo ou ultrapassamos o limite de
     * profundidade, retornamos null.
     *
     * 
     * ---
     * @param string $dump  
     * :: Texto completo do dump que estamos lendo.
     *
     * @return string|null HTML do valor lido ou null se não pudermos formatá-lo.
     */
    
    private static function applyFormats(string $dump): ?string
    {
        /**
         * Antes de entrar em uma leitura, conferimos até onde já fomos, caso
         * esta não seja a primeira vez que o loop tenha passado por este
         * método.
         *
         * Se ultrapassamos o limite, devolvemos null para interromper a
         * tentativa de formatação, inclusive nos arrays e objetos que contêm
         * esse valor.
         */
        
        if (self::$currentDepth > self::MAX_FORMAT_DEPTH) {
            return null;
        }

        
        
        /**
         * Limpamos o marcador da leitura para esta execução para garantir que
         * uma execução anterior não contamine a atual. Se o próximo valor
         * começar com &, guardamos sua representação em HTML e avançamos uma
         * posição. Assim, o marcador será preservado na apresentação e a
         * identificação do tipo poderá começar logo depois dele.
         */
        
        self::$refMarker = '';
        
        $firstChar = substr($dump, self::$dumpPointerPosition, 1);

        if ($firstChar === '&') {
            self::$refMarker = '&amp;';
            self::moveDumpPointerForward(1);
        }

        
        
        /**
         * Cada expressão descreve como um tipo aparece no texto do
         * `var_dump()`. Além de reconhecer o tipo, elas separam as informações
         * de que os formatadores precisam, como o tamanho da string ou o valor
         * numérico.
         *
         * O `\G` no começo exige que o conteúdo esteja na posição atual. Nos
         * tipos simples, a expressão já lê o valor inteiro. Em strings, arrays
         * e objetos, ela lê apenas o cabeçalho; o restante será lido pelo
         * método responsável por aquele tipo.
         */
        
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

        
        
        /**
         * Tentamos os formatos até encontrar um que combine com o texto do
         * `var_dump()` recebido. Quando encontramos, passamos as capturas para
         * o método correspondente e devolvemos o resultado que ele produzir.
         *
         * Strings, arrays e objetos também recebem o dump completo porque ainda
         * precisam continuar a leitura depois do cabeçalho reconhecido.
         */
        
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
        
        
        
        /**
         * O null tem uma representação fixa: NULL seguido de uma quebra de
         * linha. Podemos conferir esse trecho diretamente, sem uma expressão
         * regular.
         */
        
        $nullMatches = substr($dump, self::$dumpPointerPosition, 5) === "NULL\n";

        if ($nullMatches) {
            return self::nullFormatting();
        }
        
        
        
        /**
         * Se nenhum formato conhecido combinou com o texto nessa posição,
         * avisamos a falha retornando NULL em vez de pular o trecho e correr o
         * risco de ocultar algum dado.
         */
        
        return null;
    }
    


    /**
     * 🟢 FORMATANDO STRINGS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Quando chegamos aqui, já reconhecemos o cabeçalho da string, como
     * `string(5)`, por exemplo. Esse número informa o tamanho do conteúdo
     * em bytes, que é a medida usada pelo PHP para percorrer esse texto.
     *
     * Isso importa porque uma letra com acento ou um emoji pode ocupar mais
     * de um byte. Usando o tamanho informado pelo próprio var_dump(),
     * conseguimos ler todo o conteúdo sem precisar contar essas letras.
     *
     * Usamos esse tamanho porque uma string pode conter aspas e quebras de
     * linha. Se tentássemos encontrar o fim procurando apenas a próxima aspa,
     * poderíamos encerrar a leitura antes da hora, no meio do valor.
     *
     * Depois de ler o conteúdo, confirmamos seu fechamento e montamos o HTML
     * com estilos separados para o valor e para a indicação do tipo.
     *
     * 
     * ---
     * @param string $currentoutput  
     * :: Texto completo do dump que estamos lendo.
     *
     * @param array<int, string> $matches  
     * :: Capturas do cabeçalho da string.
     *
     * @return string|null HTML da string ou null se a leitura não conferir.
     */
    
    private static function stringFormatting(string $currentoutput, array $matches): ?string
    {
        /**
         * Guardamos o marcador de referência e recuperamos o tamanho do
         * cabeçalho e o tamanho da string. A posição zero das capturas contém
         * todo o cabeçalho reconhecido; a posição dois contém o tamanho
         * informado pelo PHP.
         */
        
        $refMarker = self::$refMarker;

        $matchLength = strlen($matches[0]);
        $outputlength = $matches[2];

        
        
        /**
         * Movemos o ponteiro para depois do cabeçalho, incluindo a aspa de
         * abertura, para chegar ao primeiro caractere do conteúdo. Antes de
         * ler, conferimos se o tamanho anunciado cabe no texto restante,
         * reservando dois bytes para a aspa final e a quebra de linha. Se
         * não couber, essa leitura não será considerada confiável e retornará
         * `null`.
         */
        
        self::moveDumpPointerForward($matchLength);

        $currentLength = strlen($currentoutput) - self::$dumpPointerPosition - 2;
        
        if ($outputlength > $currentLength) {
            return null;
        }

        
        
        /**
         * Lemos exatamente a quantidade de bytes informada e avançamos até o
         * ponto em que esperamos encontrar o fechamento. Esse tamanho também
         * funciona quando o texto contém acentos e emojis.
         */
        
        $value = substr($currentoutput, self::$dumpPointerPosition, $outputlength);

        self::moveDumpPointerForward($outputlength);

        
        
        /**
         * Depois do conteúdo, precisam existir uma aspa e uma quebra de linha.
         * Se não encontrarmos esse fechamento, essa leitura não será
         * considerada confiável e retornará `null`. Se encontramos,
         * reposicionamos o ponteiro após ele para deixar a leitura pronta para
         * o próximo trecho.
         */
        
        if (substr($currentoutput, self::$dumpPointerPosition, 2) !== "\"\n") {
            return null;
        }

        self::moveDumpPointerForward(2);

        
        
        /**
         * Formatamos o valor entre aspas e a indicação de tipo e tamanho. O
         * marcador de referência, quando existir, fica antes do valor. O
         * applyHtml() cuida de exibir o conteúdo como texto, mesmo que ele
         * contenha tags HTML.
         */
        
        $stringValueHtml = self::applyHtml('string-value', '"'.$value.'"');
        $stringTypeHtml = self::applyHtml('string-type', $matches[1]);
        
        return $refMarker.$stringValueHtml.' '.$stringTypeHtml."\n";
    }



    /**
     * 🟢 FORMATANDO ARRAYS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * O dump de um array começa informando quantas entradas ele possui. Usamos
     * essa quantidade para percorrer suas chaves e os valores de cada uma.
     *
     * Cada valor pode ser de um tipo diferente, inclusive outro array ou um
     * objeto. Por isso, depois de ler a chave, chamamos `applyFormats()` para
     * descobrir como continuar a leitura daquele valor.
     *
     * Ao terminar as entradas, conferimos o fechamento do array. Se alguma
     * parte não puder ser lida como esperado, algo errado aconteceu, então
     * devolvemos null para que `print()` possa apresentar o dump sem
     * formatação.
     *
     *
     * ---
     * @param string $currentOutput  
     * :: Texto completo do dump que estamos lendo.
     *
     * @param array<int, string> $matches  
     * :: Capturas do cabeçalho do array.
     *
     * @return string|null HTML do array ou null se a leitura falhar.
     */
    
    private static function arrayFormatting(string $currentOutput, array $matches): ?string
    {
        /**
         * Guardamos o marcador de referência em uma variável local porque a
         * leitura dos valores internos pode alterar a propriedade $refMarker da
         * classe. Assim, preservamos o marcador deste array até a montagem do
         * resultado.
         *
         * Também recuperamos o cabeçalho e a quantidade de entradas. Depois,
         * posicionamos o ponteiro para começar a leitura do conteúdo do array.
         */
        
        $refMarker = self::$refMarker;
        
        $matchLength = strlen($matches[0]);
        $arrayHeader = $matches[1];
        $keyCount = $matches[2];

        self::moveDumpPointerForward($matchLength);

        
        
        /**
         * O `var_dump()` acrescenta dois espaços de recuo para cada chave de
         * uma array. Calculamos o recuo deste array e o de suas entradas para
         * reconhecer o texto e manter essa mesma organização na apresentação.
         */
        
        $parentIndent = str_repeat(' ', self::$currentDepth * 2);
        $childIndent = $parentIndent.'  ';

        
        
        /**
         * Separamos a montagem em cabeçalho, conteúdo e fechamento. O
         * fechamento também serve para conferir se encontramos o fim do array
         * no recuo certo.
         */
        
        $htmlHeader = self::applyHtml('array-type', $arrayHeader)." {\n";
        $htmlMiddle = '';
        $htmlClosing = $parentIndent."}\n";
        
        
        
        /**
         * Esta expressão reconhece a chave da próxima entrada, seguida de => e
         * dos recuos que vêm antes do valor. A chave pode ser numérica ou
         * textual; não lemos o valor aqui, apenas as chaves.
         */
        
        $keyRegex = '/\G'.$childIndent.'(\[(?:-?\d+|"[\s\S]*?")\])=>\n'.$childIndent.'/';

        
        
        /**
         * Percorremos a quantidade de entradas anunciada no cabeçalho
         */
        
        for ($index = 0; $index < $keyCount; $index++) {
            
            /**
             * Em cada passagem, precisamos encontrar a próxima chave onde a
             * leitura parou. Se isso não acontecer, interrompemos a formatação
             * deste array.
             */
            
            if (!($keyMatches = self::getMatches($keyRegex, $currentOutput))) {
                return null;
            }

            
            
            /**
             * Guardamos a chave e posicionamos o ponteiro sobre o trecho
             * reconhecido. Agora a posição de leitura está no começo do valor
             * que pertence a essa chave.
             */
            
            $keyMatchLength = strlen($keyMatches[0]);
            $keyValue = $keyMatches[1];

            self::moveDumpPointerForward($keyMatchLength);

            
            
            /**
             * Entramos um nível na array para ler esse valor. O
             * `applyFormats()` pode chamar outros formatadores, conforme o tipo
             * de valor que encontrar. Quando ele termina, voltamos à
             * profundidade deste array.
             *
             * Se a leitura interna falhar, precisamos desistir da formatação do
             * array inteiro, pois não conseguimos montar seu conteúdo completo.
             */
            
            self::$currentDepth++;
            $nextKeyContent = self::applyFormats($currentOutput);
            self::$currentDepth--;

            if ($nextKeyContent === null) {
                return null;
            }

            
            
            /**
             * Acrescentamos a entrada ao resultado, com a chave formatada, o
             * sinal => e o valor que acabamos de ler. A posição já foi avançada
             * pelo formatador desse valor, ficando pronta para a próxima chave.
             */
            
            $arrayKeyHtml = self::applyHtml('array-key', $keyValue);
            
            $htmlMiddle .= $childIndent.$arrayKeyHtml.' =&gt; '.$nextKeyContent;
        }

        
        
        /**
         * Com todas as entradas lidas, conferimos se o próximo trecho é o sinal
         * } que fecha este array, no recuo esperado e com uma quebra de linha.
         * 
         * Se não for, retornamos `null`.
         */
        
        $currentText = substr(
            $currentOutput, self::$dumpPointerPosition, strlen($htmlClosing)
        );

        if ($currentText !== $htmlClosing) {
            return null;
        }
        
        
        
        /**
         * Como o fechamento conferiu, avançamos sobre ele e reunimos as partes.
         * Quem chamou este método poderá continuar a leitura depois do array.
         */
        
        $htmlClosingLength = strlen($htmlClosing);
        self::moveDumpPointerForward($htmlClosingLength);

        return $refMarker.$htmlHeader.$htmlMiddle.$htmlClosing;
    }



    /**
     * 🟢 FORMATANDO OBJETOS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Lemos o cabeçalho do objeto e percorremos as entradas que o var_dump()
     * apresentou para ele. Cada valor é passado para `applyFormats()`, que
     * descobre como lê-lo, inclusive quando há outros objetos ou arrays dentro.
     *
     * Continuamos até encontrar o fechamento deste objeto no recuo esperado. Se
     * alguma entrada não puder ser lida, retornamos null para que o resultado
     * original seja exibido por `print()`.
     *
     * Também aproveitamos as indicações de propriedades privadas e protegidas
     * para aplicar os estilos que ajudam a diferenciá-las na apresentação.
     *
     * 
     * ---
     * @param string $currentOutput  
     * :: Texto completo do dump que estamos lendo.
     * 
     * @param array<int, string> $matches  
     * :: Capturas do cabeçalho do objeto.
     * 
     * @return string|null HTML do objeto ou null se a leitura falhar.
     */
    
    private static function objectFormatting(string $currentOutput, array $matches): ?string
    {
            
        /**
         * Guardamos o marcador de referência em uma variável local porque a
         * leitura dos valores internos pode alterar a propriedade $refMarker da
         * classe. Assim, preservamos o marcador deste objeto até a montagem do
         * resultado.
         *
         * Também recuperamos o cabeçalho e a quantidade de entradas. Depois,
         * posicionamos o ponteiro para começar a leitura do conteúdo do objeto.
         */
        
        $refMarker = self::$refMarker;

        $matchLength = strlen($matches[0]);
        $objectHeader = $matches[1];

        self::moveDumpPointerForward($matchLength);

        
        
        /**
         * O `var_dump()` acrescenta dois espaços de recuo para cada propriedade
         * de um objeto. Calculamos o recuo deste objeto e o de suas entradas
         * para reconhecer o texto e manter essa mesma organização na
         * apresentação.
         */
        
        $parentIndent = str_repeat(' ', self::$currentDepth * 2);
        $childIndent = $parentIndent.'  ';

        
        
        /**
         * Preparamos o cabeçalho, o espaço onde vamos juntar as entradas e o
         * fechamento esperado para este nível.
         */
        
        $htmlHeader = self::applyHtml('object-header', $objectHeader)." {\n";
        $htmlMiddle = '';
        $htmlClosing = $parentIndent."}\n";

        
        
        /**
         * A primeira expressão reconhece o nome da próxima entrada e os recuos
         * até o começo de seu valor. Ela também aceita as indicações de acesso
         * private e protected que podem acompanhar os nomes das propriedades.
         *
         * A segunda expressão será usada depois para identificar qual dessas
         * indicações apareceu e escolher seu estilo.
         */
        
        $keyRegex =
            '/\G'.$childIndent.'(\[(?:-?\d+|"[\s\S]*?"(?::(?:private|protected))?)\])=>\n'.$childIndent.'/';
        
        $propertyRegex = '/:(private|protected)\]$/';

        
        
        /**
         * Para cada propriedade vamos ter que aplicar formatações tanto nos
         * nomes quanto nos valores. Por isso, iniciamos um loop.
         *
         * Esse loop é infinito apenas nesta declaração, mas ele é encerrado
         * através de um break interno ou de um return. Não criamos uma condição
         * específica pois em caso de problemas, o loop precisa ser encerrado
         * imediatamente, sem prosseguir com os demais comandos.
         */
        
        while (true) {
        
            /**
             * Antes de procurar outra entrada, verificamos se chegamos ao
             * fechamento deste objeto. Se chegamos, encerramos o laço. Caso
             * contrário, precisamos reconhecer uma entrada nessa posição para
             * poder continuar.
             */
            
            $currentText = substr(
                $currentOutput, self::$dumpPointerPosition, strlen($htmlClosing)
            );

            if ($currentText === $htmlClosing) {
                break;
            }

            if (!($matches = self::getMatches($keyRegex, $currentOutput))) {
                return null;
            }

            
            
            /**
             * Guardamos o nome encontrado e movemos o ponteiro pra leitura até
             * o valor da entrada.
             */
            
            $keyMatchLength = strlen($matches[0]);
            $propertyValue = $matches[1];

            self::moveDumpPointerForward($keyMatchLength);

            
            /**
             * Entramos um nível no objeto para ler o valor. O `applyFormats()`
             * pode chamar outros formatadores, conforme o tipo de valor que
             * encontrar. Quando ele termina, voltamos subimos um nível de
             * profundidade deste objeto novamente.
             *
             * Se a leitura interna falhar, precisamos desistir da formatação do
             * objeto inteiro, pois não conseguimos montar seu conteúdo
             * completo.
             */
            
            self::$currentDepth++;
            
            $nextKeyContent = self::applyFormats($currentOutput);
            
            self::$currentDepth--;

            if ($nextKeyContent === null) {
                return null;
            }

            
            
            /**
             * Com o valor formatado, aplicamos a formatação da propriedade. Se
             * o nome trouxer private ou protected, acrescentamos essa indicação
             * às classes CSS.
             */
            
            $cssKeyClassName = 'object-key';

            if (preg_match($propertyRegex, $propertyValue, $visibility) === 1) {
                $cssKeyClassName .= ' '.$visibility[1];
            }

            /**
             * Acrescentamos a entrada ao resultado, com a propriedade
             * formatada, o sinal => e o valor que acabamos de ler. A posição já
             * foi avançada pelo formatador desse valor, ficando pronta para a
             * próxima chave.
             */
            
            $htmlMiddle .= $childIndent.self::applyHtml($cssKeyClassName, $propertyValue).' =&gt; '.$nextKeyContent;
        }

        
        
        /**
         * Como o fechamento já foi reconhecido no loop, movemos o pointer de
         * leitura e devolvemos o objeto completo, com seu marcador de
         * referência quando houver.
         */
        
        self::moveDumpPointerForward(strlen($htmlClosing));

        return $refMarker.$htmlHeader.$htmlMiddle.$htmlClosing;
    }



    /**
     * 🟢 FORMATANDO NÚMEROS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Recebemos o tipo e o valor numérico que já foram reconhecidos no dump.
     * Montamos a apresentação com estilos separados para o número e para a
     * indicação de `int` ou `float`.
     *
     * Mantemos o valor escrito pelo PHP. Isso também preserva representações
     * como `INF`, `-INF` e `NAN`, sem tentar convertê-las em outro número.
     *
     * 
     * ---
     * @param array<int, string> $matches
     * :: Capturas do tipo e do valor numérico.
     * 
     * @return string HTML do valor numérico.
     */
    
    private static function numericFormatting(array $matches): ?string
    {
        /**
         * Guardamos o marcador numa variável local para seguir o mesmo padrão
         * dos formatadores de arrays e objetos. Aqui, também poderíamos usar
         * a propriedade diretamente, pois não lemos valores internos que
         * possam alterar seu conteúdo.
         */
        
        $refMarker = self::$refMarker;

        
        
        /**
         * Separamos as capturas, em variáveis para facilitar a leitura.
         */
        
        $matchLength = strlen($matches[0]);
        $numericType = $matches[1];
        $numericValue = $matches[2];

        
        
        /**
         * Como a expressão já leu o número inteiro, avançamos o ponteiro sobre
         * todo esse trecho. Depois, montamos o HTML com o valor seguido de seu
         * tipo.
         */
        
        self::moveDumpPointerForward($matchLength);
        
        $numericValueHtml = self::applyHtml('numeric-value', $numericValue);
        $numericTypeHtml = self::applyHtml('numeric-type', $numericType);

        return $refMarker.$numericValueHtml.' '.$numericTypeHtml."\n";
    }



    /**
     * 🟢 FORMATANDO BOOLEANOS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Formata o valor `true` ou `false` e a indicação do tipo bool.
     *
     * Usamos o próprio valor nos nomes das classes CSS para que `true` e
     * `false` possam ter aparências diferentes. Isso facilita distinguir os
     * dois ao conferir os resultados da depuração.
     *
     *
     * ---
     * @param array<int, string> $matches  
     * :: Capturas do valor booleano.
     *
     * @return string HTML do valor booleano.
     */
    
    private static function booleanFormatting(array $matches): ?string
    {
        /**
         * Guardamos o marcador numa variável local para seguir o mesmo padrão
         * dos formatadores de arrays e objetos. Aqui, também poderíamos usar
         * a propriedade diretamente, pois não lemos valores internos que
         * possam alterar seu conteúdo.
         */
        
        $refMarker = self::$refMarker;

        
        
        /**
         * Separamos as capturas, em variáveis para facilitar a leitura.
         */
        
        $matchLength = strlen($matches[0]);
        $booleanValue = $matches[1];

        
        /**
         * A correspondência completa inclui o tipo, o valor e a quebra de
         * linha, então seu tamanho já permite avançar até o próximo trecho.
         */
        
        self::moveDumpPointerForward($matchLength);

        
        
        /**
         * Colocamos o valor entre parênteses e acrescentamos o tipo bool.
         * Conforme o valor recebido, os estilos CSS serão `true-value` e
         * `true-type`, ou `false-value` e `false-type`.
         */
        
        $booleanValueHtml = self::applyHtml(
            $booleanValue.'-value', '('.$booleanValue.')'
        );
        
        $booleanTypeHtml = self::applyHtml($booleanValue.'-type', 'bool');
        
        return $refMarker.$booleanValueHtml.' '.$booleanTypeHtml."\n";
    }



    /**
     * 🟢 FORMATANDO REFERÊNCIAS RECURSIVAS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Um array ou objeto pode ter uma ligação que leve de volta a ele mesmo. Se
     * continuássemos seguindo esse caminho, visitaríamos os mesmos dados
     * repetidamente.
     *
     * O próprio `var_dump()` identifica essa situação e escreve `*RECURSION*`
     * no lugar de repetir o conteúdo. Aqui apenas preservamos esse aviso e
     * aplicamos seu estilo para deixá-lo visível na depuração.
     *
     *
     * ---
     * @param array<int, string> $matches  
     * :: Capturas do aviso de recursão.
     *
     * @return string HTML do aviso de recursão.
     */
    
    private static function recursionFormatting(array $matches): ?string
    {
        
        /**
         * Separamos as capturas, em variáveis para facilitar a leitura.
         */
        
        $matchLength = strlen($matches[0]);

        
        
        /**
         * A expressão já reconheceu todo o aviso, então avançamos pelo
         * tamanho do trecho lido.
         */
        
        self::moveDumpPointerForward($matchLength);

        
        
        /**
         * Exibimos o aviso com seu estilo, sem tentar entrar novamente na
         * estrutura que o var_dump() indicou como recursiva.
         */
        
        return self::applyHtml('recursion', '*RECURSION*')."\n";
    }



    /**
     * 🟢 FORMATANDO PROPRIEDADES NÃO INICIALIZADAS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Uma propriedade pode ter um tipo declarado e ainda não ter recebido um
     * valor. Nesse caso, o `var_dump()` apresenta algo como
     * `uninitialized(string)`. Isso permite distinguir uma propriedade não
     * inicializada de uma que já recebeu um valor.
     * 
     * ℹ️ OBSERVAÇÃO
     * ┌───────────────────────────────────────────────────────────────────────┐
     * │ Não estamos tratando de valores nulos ou vazios, mas sim de           │
     * │ propriedades declaradas mas não inicializadas.                        │
     * └───────────────────────────────────────────────────────────────────────┘
     *
     * Aqui formatamos esse aviso junto do tipo declarado. O tipo pode ter mais
     * de uma parte, como string|int, então também precisamos preservar os
     * sinais que ligam essas partes.
     *
     *
     * ---
     * @param array<int, string> $matches  
     * :: Capturas do aviso e do tipo declarado.
     *
     * @return string|null HTML do aviso ou null se a separação do tipo falhar.
     */
    
    private static function uninitializedFormatting(array $matches): ?string
    {
        
        /**
         * Separamos as capturas, em variáveis para facilitar a leitura.
         */
        
        $matchLength = strlen($matches[0]);
        $declaredType = $matches[1];
        
        
        
        /**
         * Os tipos precisam seguir o estilo CSS apropriado. A lista abaixo
         * associa os tipos conhecidos aos estilos que já usamos na apresentação
         * dos demais valores.
         */
        
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

        
        
        /**
         * A leitura já reconheceu o aviso completo, então podemos avançar no
         * ponteiro do dump.
         */
        
        self::moveDumpPointerForward($matchLength);

        
        
        /**
         * Separamos o tipo nos sinais ?, &, | e nos parênteses, mantendo esses
         * sinais na lista.
         * 
         * ✔️ EXEMPLO
         * ┌───────────────────────────────────────────────────────────────┐
         * │ Uma declaração `string|int` é dividida em três partes:        │
         * │                                                               │
         * │ `string`, `|` e `int`                                         │
         * │                                                               │
         * │ Assim, podemos aplicar um estilo a cada tipo sem perder forma │
         * │ como eles foram declarados.                                   │
         * └───────────────────────────────────────────────────────────────┘
         */
        
        $partList = preg_split(
            '/([?&|()])/', $declaredType, -1, 
            PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
        );

        
        
        /**
         * Se a separação falhar, avisamos quem chamou o método para que a
         * tentativa de formatação seja abandonada.
         */
        
        if ($partList === false) {
            return null;
        }

        
        
        /**
         * Os sinais entram como texto, preservando a declaração. Para cada
         * tipo, procuramos um estilo na lista. Os valores que não são tipos ou
         * que não estão nela, como os de classes e interfaces, recebem o estilo
         * de objeto.
         */
        
        foreach ($partList as $part) {
            
            if (in_array($part, ['?', '&', '|', '(', ')'], true)) {
                $partListAsHtml[] = self::escapeHtml($part);
                
            } else {
                $class = $classes[$part] ?? 'object-header';
                $partListAsHtml[] = self::applyHtml($class, $part);
            }
        }

        /**
         * Finalizamos reunindo as partes e colocamos o aviso `uninitialized`
         * antes do tipo.
         */
        
        $uninitializedHtml = self::applyHtml('uninitialized', 'uninitialized');
        $partListAsHtml = implode($partListAsHtml);
        
        return $uninitializedHtml.' '.$partListAsHtml."\n";
    }



    /**
     * 🟢 FORMATANDO RECURSOS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Algumas operações do PHP devolvem recursos para representar algo que está
     * em uso, como um arquivo aberto. No dump, eles aparecem com um
     * identificador e uma indicação do tipo de recurso.
     *
     * ✔️ EXEMPLO
     * ┌───────────────────────────────────────────────────────────────────────┐
     * │ Em `resource(5) of type (stream)`, por exemplo, `5` é o identificador │
     * │ e `stream` é o tipo informado pelo PHP.                               │
     * │                                                                       │
     * │ Separamos essas informações para apresentá-las com seus próprios      │
     * │ estilos.                                                              │
     * └───────────────────────────────────────────────────────────────────────┘
     *
     *
     * ---
     * @param array<int, string> $matches  
     * :: Capturas das informações do recurso.
     *
     * @return string HTML do recurso.
     */
    
    private static function resourceFormatting(array $matches): ?string
    {
        
        /**
         * Recuperamos o identificador e o tipo de recurso das capturas. Também
         * guardamos o marcador de referência e o tamanho total do trecho lido.
         */
        
        $refMarker = self::$refMarker;

        $matchLength = strlen($matches[0]);
        $resourceId = $matches[1];
        $resourceKind = $matches[2];

        
        
        /**
         * Avançamos sobre o recurso reconhecido e montamos a apresentação com o
         * identificador, o rótulo resource e o tipo do recurso entre
         * parênteses.
         */
        
        self::moveDumpPointerForward($matchLength);
        
        $resourceValueHtml = self::applyHtml('resource-value', $resourceId);
        $resourceTypeHtml = self::applyHtml('resource-type', 'resource');
        $resourceKindHtml = self::applyHtml('resource-kind', '('.$resourceKind.')');

        return
            $refMarker.$resourceValueHtml.' '.$resourceTypeHtml.' '.$resourceKindHtml
            ."\n";
    }



    /**
     * 🟢 FORMATANDO ENUMS
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Um enum reúne opções nomeadas, como os estados de um cadastro. Quando
     * depuramos uma dessas opções, o var_dump() informa o nome do enum e o caso
     * selecionado, como enum(Status::Ativo).
     *
     * Aqui preservamos essa identificação e acrescentamos o rótulo enum,
     * aplicando os estilos próprios dessa apresentação.
     *
     * 
     * ---
     * @param array<int, string> $matches
     * :: Capturas da identificação do caso.
     * 
     * @return string HTML do caso de enum.
     */
    
    private static function enumFormatting(array $matches): ?string
    {
        /**
         * Guardamos o marcador numa variável local para seguir o mesmo padrão
         * dos formatadores de arrays e objetos. Aqui, também poderíamos usar
         * a propriedade diretamente, pois não lemos valores internos que
         * possam alterar seu conteúdo.
         */
        
        $refMarker = self::$refMarker;

        
        
        /**
         * Separamos as capturas, em variáveis para facilitar a leitura.
         */
        
        $matchLength = strlen($matches[0]);
        $enumCase = $matches[1];
        
        
        
        /**
         * A correspondência completa inclui o tipo, o valor e a quebra de
         * linha, então seu tamanho já permite avançar até o próximo trecho.
         */
        
        self::moveDumpPointerForward($matchLength);

        
        
        /**
         * Exibimos a identificação recebida seguida do rótulo enum.
         */
        
        $enumValueHtml = self::applyHtml('enum-value', $enumCase);
        $enumTypeHtml = self::applyHtml('enum-type', 'enum');
        
        return $refMarker.$enumValueHtml.' '.$enumTypeHtml."\n";
    }



    /**
     * 🟢 FORMATANDO INDICADORES DE VALOR DESCONHECIDO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Se o var_dump() produzir o marcador UNKNOWN:0, mantemos essa informação
     * na saída e aplicamos um estilo próprio para destacá-la.
     *
     * Não tentamos adivinhar o valor que ele representa. A depuração precisa
     * continuar mostrando a indicação que veio do PHP.
     *
     * 
     * ---
     * @param array<int, string> $matches  
     * :: Capturas do marcador UNKNOWN:0.
     * 
     * @return string HTML do marcador de valor desconhecido.
     */
    
    private static function unknownFormatting(array $matches): ?string
    {
        /**
         * Guardamos o marcador numa variável local para seguir o mesmo padrão
         * dos formatadores de arrays e objetos. Aqui, também poderíamos usar
         * a propriedade diretamente, pois não lemos valores internos que
         * possam alterar seu conteúdo.
         */
        
        $refMarker = self::$refMarker;

        
        
        /**
         * Separamos as capturas, em variáveis para facilitar a leitura.
         */
        
        $matchLength = strlen($matches[0]);
        
        

        /**
         * A correspondência completa inclui o tipo, o valor e a quebra de
         * linha, então seu tamanho já permite avançar até o próximo trecho.
         */
        
        self::moveDumpPointerForward($matchLength);

        
        
        /**
         * Mantemos o texto UNKNOWN:0 na apresentação com o estilo unknown.
         */
        
        $unknownValueHtml = self::applyHtml('unknown', 'UNKNOWN:0');
        
        return $refMarker.$unknownValueHtml."\n";
    }



    /**
     * 🟢 FORMATANDO O VALOR null
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Apresenta o NULL que já foi reconhecido por `applyFormats()` com seu
     * próprio estilo, preservando o marcador de referência quando houver.
     *
     * O texto desse valor é sempre o mesmo, então não precisamos receber
     * capturas de uma expressão regular para montar sua apresentação.
     *
     * 
     * ---
     * @return string HTML do valor null.
     */
    
    private static function nullFormatting(): ?string
    {
        /**
         * Guardamos o marcador numa variável local para seguir o mesmo padrão
         * dos formatadores de arrays e objetos. Aqui, também poderíamos usar
         * a propriedade diretamente, pois não lemos valores internos que
         * possam alterar seu conteúdo.
         */
        $refMarker = self::$refMarker;
        
        
        
        /**
         * Avançamos cinco bytes: quatro correspondem a NULL e o último à
         * quebra de linha que acompanha o valor no dump.
         */
        
        self::moveDumpPointerForward(5);

        /**
         * Devolvemos o texto NULL com o estilo definido para esse tipo.
         */
        
        $nullValueHtml = self::applyHtml('null', 'NULL');
        
        return $refMarker.$nullValueHtml."\n";
    }



    /**
     * 🟢 APLICANDO O ESTILO A UM TRECHO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Os formatadores precisam destacar várias partes do dump, como um número,
     * o nome de uma propriedade ou a indicação de um tipo. Este método monta o
     * elemento <span> que será usado para aplicar o estilo a cada uma dessas
     * partes.
     *
     * Recebemos o texto e as classes CSS escolhidas pelo formatador. Antes de
     * colocar o texto no HTML, passamos por `escapeHtml()` para que o próprio
     * conteúdo depurado não seja interpretado como HTML da página.
     *
     *
     * ---
     * @param string $class  
     * :: Classes CSS que serão aplicadas ao trecho.
     *
     * @param string $value  
     * :: Texto que será exibido dentro do elemento.
     *
     * @return string Elemento span com as classes e o texto preparado.
     */
    
    private static function applyHtml(string $class, string $value): string
    {
        /**
         * Montamos o elemento com as classes fornecidas internamente e o
         * conteúdo escapado. O HTML devolvido será juntado ao restante da
         * apresentação.
         */
        
        return '<span class="'.$class.'">'.self::escapeHtml($value).'</span>';
    }



    /**
     * 🟢 PREPARANDO O CONTEÚDO PARA APARECER COMO TEXTO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Um valor depurado pode conter tags HTML. Se colocarmos esse conteúdo
     * diretamente na página, o navegador poderá interpretá-lo e mudar a
     * apresentação, em vez de mostrar o que realmente está no valor.
     *
     * Este método converte os caracteres especiais para suas representações em
     * HTML.
     *
     * ✔️ EXEMPLO
     * ┌───────────────────────────────────────────────────────────────────────┐
     * │ O caractere `<` seria interpretado como a abertura de uma tag HTML.   │
     * │ Este método converte ela em `&lt;`, que é escrito no HTML como um     │
     * │ caractere `<` literalmente, sem interpretação.                        │
     * └───────────────────────────────────────────────────────────────────────┘
     *
     * Assim, conseguimos ler até as próprias tags que estejam dentro do
     * conteúdo depurado.
     *
     *
     * ---
     * @param string $value  
     * :: Texto que será preparado para exibição.
     *
     * @return string Texto com os caracteres especiais convertidos.
     */
    
    private static function escapeHtml(string $value): string
    {
        /**
         * ENT_QUOTES inclui as aspas simples e duplas nessa conversão.
         * 
         * ENT_SUBSTITUTE pede um caractere de substituição para trechos
         * inválidos em UTF-8, permitindo apresentar o restante do texto mesmo
         * nesse caso.
         */
        
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
    


    /**
     * 🟢 AVANÇANDO A LEITURA DO DUMP
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Depois de ler um trecho, precisamos continuar logo após ele. Este método
     * soma à posição atual a quantidade de bytes que acabamos de consumir.
     *
     * Como todos os formatadores usam essa mesma posição, a leitura de um valor
     * interno também deixa o caminho pronto para quem continuar depois dele.
     *
     *
     * ---
     * @param int $moves  
     * :: Quantidade de bytes que será acrescentada à posição atual.
     *
     * @return void
     */
    
    private static function moveDumpPointerForward(int $moves): void
    {
        self::$dumpPointerPosition += $moves;
    }



    /**
     * 🟢 VOLTANDO AO INÍCIO DO DUMP
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Coloca a posição de leitura em zero para começarmos um novo dump.
     *
     * O método `print()` pode ter que gerar vários dumps, de vários valores.
     * Cada um gera seu próprio texto, então a leitura seguinte precisa começar
     * do início, sem aproveitar a posição em que terminamos o resultado
     * anterior.
     *
     *
     * ---
     * @return void
     */
    
    private static function resetDumpPointerPosition(): void
    {
        self::$dumpPointerPosition = 0;
    }



    /**
     * 🟢 REINICIANDO O NÍVEL DE CONSULTA DA ORIGEM
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Zera o ajuste feito para localizar a origem de uma chamada intermediária,
     * como a que acontece quando usamos a função auxiliar `vardump()`.
     *
     * O método `print()` guarda o nível em uma variável local antes de chamar
     * este reset. Assim, continua usando o ajuste de que precisa agora, mas
     * deixa a propriedade pronta para as próximas chamadas.
     *
     * 
     * ---
     * @return void
     */
    
    private static function resetBacktraceLevel(): void
    {
        self::$backTraceLevel = 0;
    }
}

// -----------------------------------------------------------------------------
// ◀️ VOLTAR A UM TÓPICO ANTERIOR:
// 🔗 LINK: /galastri/bootstrap/bootstrap.php
