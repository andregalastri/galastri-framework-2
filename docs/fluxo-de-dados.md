```php
/**
 * -----------------------------------------------------------------------------
 * 📂
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                              FLUXO DE DADOS                               ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 *
 * O fluxo de dados no Galastri Framework refere-se à ordem em que a requisição
 * será processada, desde o momento em que ela é recebida pelo servidor até a
 * resposta final.
 */



/**
 * 🟢 ORGANIZAÇÃO
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Abaixo está o fluxo básico dos dados desde o momento em que a requisição é
 * recebida até o retorno da resposta final.
 *
 * 1️⃣ RECEBIMENTO DA REQUISIÇÃO
 *    ──────────────────────────────────────────────────────────────────────────
 *    Quando o servidor recebe a requisição, ela é direcionada para o arquivo
 *    index.php, que realiza a inicialização básica do framework, conforme
 *    explicado na seção de Inicialização.
 *
 * 2️⃣ PROCESSAMENTO E CONFIGURAÇÃO DA ROTA
 *    ──────────────────────────────────────────────────────────────────────────
 *    O próximo passo é processar a URL recebida, a fim de determinar a rota e
 *    organizar suas partes. A definição da rota e suas configurações determinam
 *    onde e como o processamento dos dados será realizado.
 *
 *    -  A URL é dividida em parâmetros e `nodes` dinâmicos. Dados recebidos via
 *       POST não são organizados aqui, já que existe o módulo `HttpData`,
 *       responsável por capturá-los e organizá-los.
 *
 *    O processo completo de configuração e organização das rotas é detalhado no
 *    arquivo da classe Router.
 *
 *    🔗 LINK: /galastri/core/Router.php
 *
 * 3️⃣ PROCESSAMENTO DAS CONFIGURAÇÕES
 *    ──────────────────────────────────────────────────────────────────────────
 *    Após definir a rota e suas configurações, o framework realiza diversas
 *    verificações:
 *
 *    - Verifica se a configuração de redirecionamento está definida.
 *
 *    - Confirma se a opção de desativar o sistema temporariamente está ativa,
 *      interrompendo a execução se necessário.
 *
 *    Também verificamos a validade da rota e a autenticação configurada.
 *    Depois, identificamos o controller e conferimos os métodos que serão
 *    chamados antes de começar a execução da aplicação.
 *
 * 4️⃣ EXECUÇÃO DA CONTROLLER
 *    ──────────────────────────────────────────────────────────────────────────
 *    A controller executa o método associado à rota e, quando configurado,
 *    também executa o método adequado para o tipo de requisição HTTP (GET,
 *    POST, etc.).
 *
 *    A programação do usuário ocorre nesses métodos, que podem incluir
 *    importações de classes ou dados personalizados. Os dados processados devem
 *    ser retornados em um `array`, o qual será utilizada na etapa de retorno
 *    de dados.
 *
 * 5️⃣ ORDEM DOS MÉTODOS
 *    ──────────────────────────────────────────────────────────────────────────
 *    1. Execução do Método da Rota: Executamos o método escolhido para atender
 *       à rota e guardamos seu retorno.
 *
 *    2. Execução do Método para o Tipo de Requisição HTTP: Quando existe uma
 *       associação para o tipo recebido, executamos o método adicional e
 *       guardamos seu retorno separadamente.
 *
 *    O retorno do primeiro método não é passado como argumento para o segundo.
 *    A classe base `Controller` reúne os retornos após as chamadas.
 *
 *    O resultado final será um array contendo todos os dados processados,
 *    prontos para serem retornados e usados na etapa de saída.
 *
 * 6️⃣ RETORNO DOS DADOS
 *    ──────────────────────────────────────────────────────────────────────────
 *    O fluxo é finalizado com a execução da classe responsável pelo formato de
 *    saída.
 *
 *    Atualmente os formatos são `View`, `Json` e `File`.
 */



/**
 * 🟢 SAÍDA VIEW
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * A view é um tipo de saída que gera um HTML. Os dados são armazenados no
 * objeto `$g`, que oferece métodos para recuperar as informações processadas
 * pela controller, além de permitir a importação de conteúdo de outros
 * arquivos.
 *
 * ⚠️ IMPORTANTE
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ `View` é um formato de saída que exige um controller.                     │
 * └───────────────────────────────────────────────────────────────────────────┘
 *
 *
 *
 * 🔶 ARQUIVOS DE VIEW
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Os arquivos ficam, por padrão, dentro de `/app/views/`, seguindo a estrutura
 * de pastas do controller.
 *
 *
 * ✔️ EXEMPLO
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ Dada a URL: `www.dominio.com/produtos/listar`                             │
 * └───────────────────────────────────────────────────────────────────────────┘
 *
 * Espera-se que a view esteja em `/app/views/Produtos/listar.php`, sendo
 * `produtos` a parte referente ao controller e `listar` o método.
 *
 * Este é o comportamento padrão, mas configurações de rota podem modificar esse
 * comportamento.
 *
 *
 *
 * 🔶 ARQUIVOS DE TEMPLATE
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Um arquivo de template pode ser usado como estrutura básica do layout, onde a
 * view da rota é incluída.
 *
 * Quando se define um template, todas as rotas apontam primeiro para ele, que
 * contém a estrutura básica do layout. A view da rota pode, então, ser
 * importada dentro deste template para compor o conteúdo dentro de uma
 * estrutura padronizada.
 *
 *
 *
 * 🔶 MOTORES DE TEMPLATING
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Atualmente, o framework utiliza o próprio PHP para renderizar as páginas. A
 * classe `View` cria uma instância de `PhpEngine` e a disponibiliza em `$g`.
 * Esse objeto permite consultar os dados do controller e incluir outros
 * arquivos durante a montagem da página.
 *
 * ℹ️ INFORMAÇÃO
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ Motores de templating, como Twig e Latte, podem ser adicionados           │
 * │ futuramente.                                                              │
 * └───────────────────────────────────────────────────────────────────────────┘
 */



/**
 * 🟢 SAÍDA JSON
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Converte os dados processados pela controller em formato JSON e retorna o
 * resultado para o frontend.
 *
 *
 * Não são necessários arquivos adicionais, pois o processo consiste apenas na
 * conversão dos dados para JSON e na definição do cabeçalho para garantir que o
 * retorno seja interpretado corretamente.
 *
 * ⚠️ IMPORTANTE
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ `Json` é um formato de saída que exige um controller.                     │
 * └───────────────────────────────────────────────────────────────────────────┘
 */

/**
 * 🟢 SAÍDA FILE
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Retorna um arquivo com base no caminho fornecido. Se uma controller for
 * definida, ela é executada antes de retorná-lo, permitindo verificações
 * adicionais a respeito do arquivo. Pode-se retornar um arquivo diretamente ou
 * usar o caminho informado na URL para gerenciar ou até gerar um arquivo.
 *
 * ✔️ IMPORTANTE
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ `File` é um formato de saída que __NÃO__ exige um controller.             │
 * └───────────────────────────────────────────────────────────────────────────┘
 *
 * A saída possui dois comportamentos possíveis:
 *
 * -  Busca direta pelo arquivo com base no mesmo caminho informado na URL.
 *
 * -  Passagem da requisição por uma controller para pré-processamento antes da
 *    exibição do arquivo.
 *
 *
 *
 * 🔶 BUSCA DIRETA
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * O arquivo é buscado em um diretório seguindo a estrutura de caminho da URL. A
 * rota é seguida até o `endpoint`. A partir de então, todas as demais partes da
 * URL são interpretadas como sendo o caminho do arquivo.
 *
 * É importante definir a configuração `fileFolder` nas configurações de rota
 * para que o framework saiba onde estão os arquivos a serem buscados.
 *
 * ✔️ EXEMPLO
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ Dada a URL: `www.dominio.com/imagens/icones/empresa.jpg` e a seguinte     │
 * │ configuração de rotas:                                                    │
 * │ ┌────────────────────────────────────────────────────┐                    │
 * │ │ <?php                                              │                    │
 * │ │                                                    │                    │
 * │ │ return [                                           │                    │
 * │ │     '/' => [                                       │                    │
 * │ │         'projectName' => 'Galastri Framework',     │                    │
 * │ │         'output' => 'view',                        │                    │
 * │ │                                                    │                    │
 * │ │         '@main' => [                               │                    │
 * │ │             ...                                    │                    │
 * │ │         ],                                         │                    │
 * │ │                                                    │                    │
 * │ │         '/imagens' => [                            │                    │
 * │ │             'output' => 'file',                    │                    │
 * │ │             'fileFolder' => '/app/assets/imagens', │                    │
 * │ │             'notFoundRedirectTo' => '/erro',       │                    │
 * │ │         ],                                         │                    │
 * │ │                                                    │                    │
 * │ │         '/erro' => [                               │                    │
 * │ │             '@main' => [                           │                    │
 * │ │                 ...                                │                    │
 * │ │             ],                                     │                    │
 * │ │         ],                                         │                    │
 * │ │     ],                                             │                    │
 * │ │ ];                                                 │                    │
 * │ └────────────────────────────────────────────────────┘                    │
 * └───────────────────────────────────────────────────────────────────────────┘
 *
 * A classe `File` irá processar a rota da seguinte forma:
 *
 * 1. A rota `www.dominio.com/imagens` é definida como sendo uma rota que
 *    irá retornar um arquivo.
 *
 * 2. Os arquivos estão armazenados em `/app/assets/imagens`.
 *
 * 3. Se não existir a classe `app\controllers\Imagens`, o processamento
 *    continuará sem controller, pois a saída `file` permite isso.
 *
 * 4. O restante da URL `/icones/empresa.jpg` será o caminho buscado em
 *    `/app/assets/imagens`.
 *
 * 5. Se o arquivo existir, ele será exibido. Caso contrário, o usuário será
 *    redirecionado para `www.dominio.com/erro`.
 *
 *
 *
 * 🔶 PASSAGEM PELO CONTROLLER
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Neste caso, antes do arquivo ser procurado, a rota passa por um controller.
 *
 * Isso é útil para processar dados antes da exibição do arquivo, como em casos
 * de verificação de permissão de acesso, por exemplo.
 *
 * Pode-se também processar os parâmetros do endpoint para direcionar a
 * exibição, ou ainda para se criar um arquivo do zero, como no caso de um
 * gerador de imagens interno, sem um arquivo armazenado em disco.
 *
 *
 *
 * 🔶 ORGANIZAÇÃO DO FLUXO DE DADOS
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 *
 * Quando o conteúdo do arquivo já vem definido do controller ele já passa
 * diretamente para a etapa de finalização em que se define se o arquivo será do
 * tipo baixável ou se seu conteúdo será enviado para ser exibido no frontend,
 * no caso de imagens.
 *
 * Antes, porém, é necessário entender que há um fluxo de dados que ocorre caso
 * o conteúdo não venha de um controller.
 */



/**
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * ▶️ PRÓXIMO TÓPICO:
 * 🔗 LINK: /public/index.php
 */
