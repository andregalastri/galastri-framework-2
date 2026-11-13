```php
/**
 * -----------------------------------------------------------------------------
 * 📖
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                        TUTORIAL: CLASSE Controller                        ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 *
 * Neste tutorial, vamos acompanhar casos de uso práticos da classe base
 * `Controller` dentro de uma aplicação do Galastri Framework.
 *
 * Todo controller criado na pasta `/app/controllers` precisa herdar de
 * `galastri\modules\Controller`. É através dessa herança que a aplicação recebe
 * o ciclo de execução automático, a preparação da resposta e ferramentas
 * para ler a URL, etc.
 *
 * Vamos construir esse aprendizado passo a passo, partindo de uma página
 * institucional simples até cenários mais completos com parâmetros, nós
 * dinâmicos, formulários e entrega de arquivos.
 */



/**
 * 🟢 CENÁRIO E CONFIGURAÇÃO DAS ROTAS
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Antes de criar os controllers da aplicação, configuramos no arquivo
 * `/app/config/routes.php` as rotas que vão conduzir o nosso tutorial.
 *
 * Vamos organizar as rotas em uma progressão didática:
 *
 * 🖥️ EXEMPLO DE CONFIGURAÇÃO EM /app/config/routes.php
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ <?php                                                                     │
 * │                                                                           │
 * │ return [                                                                  │
 * │     '/' => [                                                              │
 * │         'projectName' => 'Loja Modelo',                                   │
 * │         'output' => 'view',                                               │
 * │         'includeProjectMetadata' => true,                                 │
 * │                                                                           │
 * │         '@main' => [],                                                    │
 * │                                                                           │
 * │         '@termos-de-uso' => [],                                           │
 * │                                                                           │
 * │         '/produtos' => [                                                  │
 * │             '@main' => [                                                  │
 * │                 'parameters' => '/?ordem',                                │
 * │             ],                                                            │
 * │                                                                           │
 * │             '@detalhes' => [                                              │
 * │                 'parameters' => '/slug',                                  │
 * │             ],                                                            │
 * │         ],                                                                │
 * │                                                                           │
 * │         '/painel' => [                                                    │
 * │             '/?usuario' => [                                              │
 * │                 '@main' => [],                                            │
 * │                                                                           │
 * │                 '/pedidos' => [                                           │
 * │                     '@main' => [],                                        │
 * │                 ],                                                        │
 * │             ],                                                            │
 * │         ],                                                                │
 * │                                                                           │
 * │         '/contato' => [                                                   │
 * │             '@main' => [                                                  │
 * │                 'onHttpMethod' => [                                       │
 * │                     'POST' => [                                           │
 * │                         'method' => 'enviar',                             │
 * │                     ],                                                    │
 * │                 ],                                                        │
 * │             ],                                                            │
 * │         ],                                                                │
 * │                                                                           │
 * │         '/relatorios' => [                                                │
 * │             '@main' => [],                                                │
 * │                                                                           │
 * │             '@exportar' => [                                              │
 * │                 'output' => 'file',                                       │
 * │             ],                                                            │
 * │         ],                                                                │
 * │     ],                                                                    │
 * │ ];                                                                        │
 * └───────────────────────────────────────────────────────────────────────────┘
 *
 * Com essa estrutura definida:
 *
 * - A raiz `/` define padrões compartilhados: nome do projeto (`Loja Modelo`),
 *   formato de saída (`view`) e inclusão de metadados (`includeProjectMetadata`).
 *   Por padrão, ela aponta para o controller `/app/controllers/Index.php`.
 *
 * - O endpoint `@main` atende a URL raiz `/`, e `@termos-de-uso` atende a URL
 *   `/termos-de-uso` chamando o método correspondente `termosDeUso()`.
 *
 * - Em `/produtos`, temos um nó fixo apontando para o controller
 *   `/app/controllers/Produtos.php`. Seu endpoint `@main` recebe ordenação
 *   opcional (`/?ordem`), enquanto `@detalhes` exige o identificador do
 *   produto (`/slug`).
 *
 * - Em `/painel/?usuario`, usamos um nó dinâmico no meio do caminho da rota
 *   para capturar o usuário (como `/painel/andregalastri/...`). Esse valor fica
 *   armazenado e disponível via `$this->getDynamicNode('usuario')` tanto no
 *   controller `/app/controllers/Painel/Usuario.php` quanto nos controllers,
 *   como `/app/controllers/Painel/Usuario/Pedidos.php` ao acessar
 *   `/painel/andregalastri/pedidos`.
 *
 * - Em `/contato`, uma requisição `GET` exibe a tela de contato e uma
 *   requisição `POST` executa o método `enviar()`, conforme configurado em
 *   `onHttpMethod`.
 *
 * - Em `/relatorios`, o endpoint `@main` mantém a saída `view`. Já o endpoint
 *   `@exportar`, acessado por `/relatorios/exportar`, muda a saída para `file`,
 *   permitindo entregar um arquivo gerado diretamente pelo controller.
 */



/**
 * 🟢 CASO 1: ESTRUTURA BÁSICA E MÉTODOS DE ENDPOINT EM Index.php
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * O ponto de partida de qualquer aplicação é o controller da raiz, localizado
 * em `/app/controllers/Index.php`.
 *
 * Ele atende tanto à página inicial (`/`) quanto a endpoints criados na raiz,
 * como os termos de uso (`/termos-de-uso`).
 *
 * Vamos implementar os dois métodos correspondentes:
 *
 * - `main()`: atende ao endpoint padrão `@main` da página inicial.
 *
 * - `termosDeUso()`: atende ao endpoint `@termos-de-uso`.
 * 
 * ℹ️ INFORMAÇÃO
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ O controller `Index` é especial: sempre que a rota for apenas `/`, o      │
 * │ framework busca automaticamente a classe `app\controllers\Index`, a menos │
 * │ que a rota defina a configuração `controller` apontando para outro        │
 * │ controller.                                                               │
 * └───────────────────────────────────────────────────────────────────────────┘
 *
 * 🖥️ EXEMPLO DE CONTROLLER EM /app/controllers/Index.php
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ <?php                                                                     │
 * │                                                                           │
 * │ namespace app\controllers;                                                │
 * │                                                                           │
 * │ use galastri\modules\Controller;                                          │
 * │                                                                           │
 * │ final class Index extends Controller                                      │
 * │ {                                                                         │
 * │     protected function main(): array                                      │
 * │     {                                                                     │
 * │         $this->setTitle('Página Inicial');                                │
 * │                                                                           │
 * │         $nomeProjeto = $this->getProjectName();                           │
 * │                                                                           │
 * │         return [                                                          │
 * │             'boasVindas' => 'Bem-vindo à ' . $nomeProjeto . '!',          │
 * │             'destaques' => [                                              │
 * │                 'Lançamentos da Semana',                                  │
 * │                 'Mais Vendidos',                                          │
 * │             ],                                                            │
 * │         ];                                                                │
 * │     }                                                                     │
 * │                                                                           │
 * │     protected function termosDeUso(): array                               │
 * │     {                                                                     │
 * │         $this->setTitle('Termos de Uso');                                 │
 * │                                                                           │
 * │         return [                                                          │
 * │             'ultimaAtualizacao' => '13/11/2026',                          │
 * │             'secoes' => [                                                 │
 * │                 'Uso do Sistema',                                         │
 * │                 'Privacidade e Dados',                                    │
 * │                 'Condições de Compra',                                    │
 * │             ],                                                            │
 * │         ];                                                                │
 * │     }                                                                     │
 * │ }                                                                         │
 * └───────────────────────────────────────────────────────────────────────────┘
 *
 * O que acontece aqui na prática:
 *
 * 1. Todo controller da aplicação herda de `galastri\modules\Controller`. Essa
 *    herança torna obrigatória a implementação do método `main()`, que é
 *    declarado como abstrato na classe base e sempre deve retornar um `array`.
 *
 *    💡 OBSERVAÇÃO
 *    ┌──────────────────────────────────────────────────────────────────────┐
 *    │ Os outros métodos chamados pelos endpoints, como `termosDeUso()`,    │
 *    │ também precisam retornar um array. A classe base vai guardar esse    │
 *    │ retorno para montar a resposta do controller. Quando não houver      │
 *    │ dados para entregar à saída, podemos retornar apenas `[]`.           │
 *    └──────────────────────────────────────────────────────────────────────┘
 *
 * 2. Costumamos declarar os métodos do controller como `protected`. O PHP
 *    permite que o construtor herdado da classe base execute esses métodos
 *    diretamente na instância, mantendo-os protegidos de chamadas externas
 *    acidentais.
 *
 * 3. Quando o visitante acessa a raiz `/`, o framework cria a instância do
 *    controller e seu construtor chama automaticamente o método `main()`.
 *
 * 4. Quando o visitante acessa `/termos-de-uso`, o framework identifica o
 *    endpoint `@termos-de-uso`, converte o nome para camelCase (`termosDeUso`)
 *    e executa o método correspondente.
 * 
 *    💡 OBSERVAÇÃO
 *    ┌────────────────────────────────────────────────────────────────────────┐
 *    │ No PHP, nomes de métodos não aceitam hífens (`-`). Por isso, enquanto  │
 *    │ a URL usa o padrão kebab-case (como `termos-de-uso`), o framework      │
 *    │ converte o nome para camelCase (`termosDeUso`), ligando a rota ao      │
 *    │ método sem que a gente precise configurar isso manualmente.            │
 *    └────────────────────────────────────────────────────────────────────────┘
 *
 *    ⚠️ IMPORTANTE
 *    ┌─────────────────────────────────────────────────────────────────────────┐
 *    │ O Controller sempre chama apenas o método escolhido para atender à      │
 *    │ rota. Nesse acesso, portanto, ele chama `termosDeUso()` sem executar    │
 *    │ `main()` antes. Embora todo controller precise implementar `main()`,    │
 *    │ esse método não funciona como uma preparação automática para os outros  │
 *    │ endpoints.                                                              │
 *    │                                                                         │
 *    │ Por exemplo, se fizermos uma consulta ao banco de dados dentro de       │
 *    │ `main()`, ao acessar `/termos-de-uso`, essa consulta não será executada │
 *    │ automaticamente, porque ela faz parte de outro método.                  │
 *    └─────────────────────────────────────────────────────────────────────────┘
 *
 * 5. Dentro dos métodos, usamos ferramentas herdadas da classe base:
 * 
 *    - `$this->getProjectName()` consulta o nome do projeto definido nas
 *      rotas (`Loja Modelo`).
 * 
 *    - `$this->setTitle()` define o título específico daquela página.
 *
 * 6. Ao final da execução do método, a classe base reúne os dados retornados no
 *    array de resposta. Como ativamos `includeProjectMetadata => true` nas
 *    rotas, ela também anexa automaticamente a chave `meta` (contendo o título
 *    atualizado, nome do projeto, versão e caminhos base), entregando tudo
 *    pronto para a saída `View` renderizar a página.
 */



/**
 * 🟢 CASO 2: NÓ FIXO COM MÚLTIPLOS ENDPOINTS E PARÂMETROS (/produtos)
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Quando criamos uma rota com um caminho fixo como `/produtos`, o framework
 * procura pelo controller correspondente, neste caso em
 * `/app/controllers/Produtos.php`.
 *
 * Neste controller, vamos atender a dois endpoints da nossa loja:
 *
 * - `@main`: lista os produtos cadastrados e aceita uma ordenação opcional
 *   vinda pelo parâmetro `/?ordem`.
 *
 * - `@detalhes`: exibe a página individual de um produto específico, exigindo o
 *   identificador pelo parâmetro `/slug`.
 *
 * ⚠️ IMPORTANTE
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ No exemplo, métodos como `buscarProdutos()` e `buscarPorSlug()` são       │
 * │ apenas representações de consultas ao banco de dados para demonstrar o    │
 * │ fluxo. Esse tipo de regra de negócio não é oferecido pelo framework e não │
 * │ está implementado no exemplo, ficando por conta da aplicação.             │
 * └───────────────────────────────────────────────────────────────────────────┘
 *
 * 🖥️ EXEMPLO DE CONTROLLER EM /app/controllers/Produtos.php
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ <?php                                                                     │
 * │                                                                           │
 * │ namespace app\controllers;                                                │
 * │                                                                           │
 * │ use galastri\modules\Controller;                                          │
 * │                                                                           │
 * │ final class Produtos extends Controller                                   │
 * │ {                                                                         │
 * │     protected function main(): array                                      │
 * │     {                                                                     │
 * │         $ordem = $this->getParameter('ordem');                            │
 * │                                                                           │
 * │         $this->setTitle('Nossos Produtos');                               │
 * │                                                                           │
 * │         $produtos = $this->buscarProdutos($ordem);                        │
 * │                                                                           │
 * │         return [                                                          │
 * │             'ordemAtual' => $ordem,                                       │
 * │             'produtos' => $produtos,                                      │
 * │         ];                                                                │
 * │     }                                                                     │
 * │                                                                           │
 * │     protected function detalhes(): array                                  │
 * │     {                                                                     │
 * │         $slug = (string) $this->getParameter('slug');                     │
 * │                                                                           │
 * │         $produto = $this->buscarPorSlug($slug);                           │
 * │                                                                           │
 * │         if ($produto === null) {                                          │
 * │             $this->setTitle('Produto Não Encontrado');                    │
 * │                                                                           │
 * │             return [                                                      │
 * │                 'erro' => 'O produto solicitado não foi localizado.',     │
 * │             ];                                                            │
 * │         }                                                                 │
 * │                                                                           │
 * │         $this->setTitle($produto['nome'] . ' - Produtos');                │
 * │                                                                           │
 * │         return [                                                          │
 * │             'produto' => $produto,                                        │
 * │         ];                                                                │
 * │     }                                                                     │
 * │ }                                                                         │
 * └───────────────────────────────────────────────────────────────────────────┘
 *
 * O que acontece aqui na prática:
 *
 * 1. O nó `/produtos` na URL faz o framework converter o nome para PascalCase e
 *    instanciar a classe `app\controllers\Produtos`.
 *
 * 2. Ao acessar `/produtos`, o endpoint padrão executado é `main()`. Se o
 *    visitante acessar apenas `/produtos`, `$this->getParameter('ordem')`
 *    retorna `null`. Se acessar `/produtos/menor-preco`, o método recupera a
 *    string `'menor-preco'`. A interrogação na configuração (`/?ordem`) avisa ao
 *    framework que esse parâmetro não é obrigatório para abrir a página.
 *
 * 3. Já ao acessar `/produtos/detalhes/teclado-mecanico`, o framework identifica
 *    o endpoint `@detalhes` e chama diretamente o método `detalhes()`.
 *
 * 4. Dentro de `detalhes()`, usamos `$this->getParameter('slug')` para ler o
 *    valor `'teclado-mecanico'`. Como esse parâmetro foi configurado sem a
 *    interrogação (`/slug`), ele é obrigatório: se alguém tentar acessar
 *    `/produtos/detalhes` sem informar o slug, o framework interrompe a
 *    requisição antes mesmo de instanciar o controller, devolvendo erro 404.
 *
 * 5. Quando a rota aceita múltiplos parâmetros, você pode consultar todos de
 *    uma única vez através do método `$this->getParameterValues()`. Ele retorna
 *    um array associativo, usando os nomes configurados em `parameters` como
 *    chaves e os valores encontrados na URL como valores.
 *
 *    No nosso exemplo, acessar `/produtos/menor-preco` produz
 *    `['ordem' => 'menor-preco']`. Se o visitante acessar apenas `/produtos`,
 *    o resultado será `['ordem' => null]`, porque o parâmetro opcional continua
 *    no array mesmo quando não recebe um valor.
 */


/**
 * 🟢 CASO 3: NÓS DINÂMICOS NA URL (/painel/?usuario e nós posteriores)
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Enquanto os parâmetros de rota (`parameters`) vêm sempre após o endpoint para
 * entregar dados complementares, os nós dinâmicos com o prefixo `/?` funcionam
 * como dados variáveis no meio do caminho da URL.
 *
 * Imagine que cada usuário acesse seu próprio painel administrativo:
 * 
 * - O usuário `andregalastri` acessa `/painel/andregalastri`.
 *
 * - O usuário `josemaria` acessa `/painel/josemaria`.
 *
 * E ambos podem acessar a listagem de seus próprios pedidos em:
 * 
 * - `/painel/andregalastri/pedidos`
 * 
 * - `/painel/josemaria/pedidos`
 *
 * Ao invés de criar um controller para cada nome de usuário (o que seria
 * impossível), a rota `/?usuario` captura esse segmento variável da URL e
 * aponta para o controller padrão `Usuario.php` e o valor informado na parte do
 * nó dinâmico fica retido e recuperável através de
 * `$this->getDynamicNode('usuario')`.
 *
 * Além disso, o valor capturado continua armazenado na requisição e fica
 * disponível também para os controllers de nós posteriores, como o controller
 * de `/pedidos`.
 *
 * 🖥️ EXEMPLO DE CONTROLLER Usuario EM /app/controllers/Painel/Usuario.php
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ <?php                                                                     │
 * │                                                                           │
 * │ namespace app\controllers\Painel;                                         │
 * │                                                                           │
 * │ use galastri\modules\Controller;                                          │
 * │                                                                           │
 * │ final class Usuario extends Controller                                    │
 * │ {                                                                         │
 * │     protected function main(): array                                      │
 * │     {                                                                     │
 * │         $usuario = (string) $this->getDynamicNode('usuario');             │
 * │                                                                           │
 * │         $this->setTitle('Painel de ' . ucfirst($usuario));                │
 * │                                                                           │
 * │         return [                                                          │
 * │             'usuario' => $usuario,                                        │
 * │             'notificacoes' => 3,                                          │
 * │         ];                                                                │
 * │     }                                                                     │
 * │ }                                                                         │
 * └───────────────────────────────────────────────────────────────────────────┘
 *
 * 
 * ⚠️ IMPORTANTE
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ No exemplo, o método `buscarPedidosDoUsuario()` representa uma consulta   │
 * │ ao banco de dados. Essa regra de negócio fica por conta da aplicação.     │
 * └───────────────────────────────────────────────────────────────────────────┘
 *
 * 🖥️ EXEMPLO 2 DE CONTROLLER Pedidos EM /app/controllers/Painel/Usuario/
 * Pedidos.php
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ <?php                                                                     │
 * │                                                                           │
 * │ namespace app\controllers\Painel\Usuario;                                 │
 * │                                                                           │
 * │ use galastri\modules\Controller;                                          │
 * │                                                                           │
 * │ final class Pedidos extends Controller                                    │
 * │ {                                                                         │
 * │     protected function main(): array                                      │
 * │     {                                                                     │
 * │         $usuario = (string) $this->getDynamicNode('usuario');             │
 * │                                                                           │
 * │         $this->setTitle('Pedidos de ' . ucfirst($usuario));               │
 * │                                                                           │
 * │         $pedidos = $this->buscarPedidosDoUsuario($usuario);               │
 * │                                                                           │
 * │         return [                                                          │
 * │             'usuario' => $usuario,                                        │
 * │             'pedidos' => $pedidos,                                        │
 * │         ];                                                                │
 * │     }                                                                     │
 * │ }                                                                         │
 * └───────────────────────────────────────────────────────────────────────────┘
 *
 * O que acontece aqui na prática:
 *
 * 1. Ao acessar `/painel/andregalastri`, o framework entra no nó `/painel`,
 *    identifica o nó dinâmico `/?usuario` e armazena `'usuario' =>
 *    'andregalastri'`. Pela convenção, ele instancia o controller
 *    `app\controllers\Painel\Usuario`.
 *
 * 2. Dentro de `Usuario::main()`, usamos `$this->getDynamicNode('usuario')`,
 *    sem o prefixo `/?`, para ler esse nome capturado na URL.
 *
 * 3. Quando o visitante avança para `/painel/andregalastri/pedidos`, a rota
 *    continua descendo na hierarquia e instancia o controller do nó filho
 *    `app\controllers\Painel\Usuario\Pedidos`.
 *
 * 4. Aqui está o grande diferencial do nó dinâmico: dentro de
 *    `Pedidos::main()`, a chamada `$this->getDynamicNode('usuario')` continua
 *    retornando `'andregalastri'`. O dado capturado no meio do caminho da rota
 *    permanece acessível para todos os controllers dos níveis seguintes.
 *
 * 5. Se a rota passar por múltiplos nós dinâmicos no caminho, o método
 *    `$this->getDynamicNodeValues()` devolve um array associativo com todos os
 *    valores capturados até aquele ponto.
 *
 * 💡 EM RESUMO
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ Qual a diferença entre Nó Dinâmico e Parâmetro de Rota?                   │
 * │                                                                           │
 * │ - O Nó Dinâmico (`/?usuario`) atua como um dado variável no meio do       │
 * │   caminho da URL. Ele compõe a estrutura da rota e seu valor permanece    │
 * │   acessível tanto no controller atual quanto em qualquer nó posterior.    │
 * │                                                                           │
 * │ - O Parâmetro de Rota (`parameters => '/slug'`) fica associado a um       │
 * │   endpoint específico no final do caminho para receber dados              │
 * │   complementares, sem criar novos níveis na hierarquia de controllers.    │
 * └───────────────────────────────────────────────────────────────────────────┘
 */



/**
 * 🟢 CASO 4: FORMULÁRIOS E VERBOS HTTP COM onHttpMethod (/contato)
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Em aplicações web, é muito comum que uma mesma URL tenha dois papéis: exibir
 * um formulário quando o usuário abre a página (`GET`) e processar os dados do
 * formulário quando ele clica no botão de envio (`POST`).
 *
 * Em vez de criar URLs separadas ou colocar condições manuais para testar o
 * método HTTP dentro do código, o framework resolve isso nas configurações de
 * rota com `onHttpMethod`.
 *
 * Vamos implementar `/app/controllers/Contato.php`:
 *
 * - `main()`: atende à requisição `GET` que resultará no HTML do formulário na
 *   tela do usuário.
 *
 * - `enviar()`: atende à requisição `POST` configurada em `onHttpMethod`,
 *   lendo os campos recebidos e processando o envio.
 *
 * ⚠️ IMPORTANTE
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ No exemplo, o método `enviarMensagem()` representa o envio de e-mail ou   │
 * │ gravação no banco de dados. Essa lógica fica por conta da aplicação.      │
 * └───────────────────────────────────────────────────────────────────────────┘
 *
 * 🖥️ EXEMPLO DE CONTROLLER EM /app/controllers/Contato.php
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ <?php                                                                     │
 * │                                                                           │
 * │ namespace app\controllers;                                                │
 * │                                                                           │
 * │ use galastri\modules\Controller;                                          │
 * │ use galastri\modules\HttpData;                                            │
 * │                                                                           │
 * │ final class Contato extends Controller                                    │
 * │ {                                                                         │
 * │     protected function main(): array                                      │
 * │     {                                                                     │
 * │         $this->setTitle('Fale Conosco');                                  │
 * │                                                                           │
 * │         return [                                                          │
 * │             'assuntos' => [                                               │
 * │                 'Dúvidas sobre Pedidos',                                  │
 * │                 'Elogios e Sugestões',                                    │
 * │                 'Suporte Técnico',                                        │
 * │             ],                                                            │
 * │         ];                                                                │
 * │     }                                                                     │
 * │                                                                           │
 * │     protected function enviar(): array                                    │
 * │     {                                                                     │
 * │         $this->setTitle('Fale Conosco');                                  │
 * │                                                                           │
 * │         $nome = (string) HttpData::getValue('nome');                      │
 * │         $email = (string) HttpData::getValue('email');                    │
 * │         $mensagem = (string) HttpData::getValue('mensagem');              │
 * │                                                                           │
 * │         $this->enviarMensagem($nome, $email, $mensagem);                  │
 * │                                                                           │
 * │         return [                                                          │
 * │             'sucesso' => true,                                            │
 * │             'mensagem' => 'Sua mensagem foi enviada com sucesso!',        │
 * │         ];                                                                │
 * │     }                                                                     │
 * │ }                                                                         │
 * └───────────────────────────────────────────────────────────────────────────┘
 *
 * O que acontece aqui na prática:
 *
 * 1. Ao acessar `/contato` pelo navegador (requisição `GET`), o framework
 *    segue o comportamento padrão do endpoint `@main` e executa `main()`. A
 *    view recebe a lista de `assuntos` e exibe o formulário em branco.
 * 
 *    💡 OBSERVAÇÃO
 *    ┌────────────────────────────────────────────────────────────────────────┐
 *    │ Sem uma configuração específica de `onHttpMethod`, o framework segue   │
 *    │ o comportamento do endpoint, independentemente do método HTTP da       │
 *    │ requisição. Neste exemplo, esse comportamento é chamar `main()`.       │
 *    │                                                                        │
 *    │ Como configuramos apenas o POST para executar `enviar()`, qualquer     │
 *    │ outra requisição continua chamando `main()`, incluindo o GET. Por      │
 *    │ isso, não precisamos configurar o GET separadamente.                   │
 *    └────────────────────────────────────────────────────────────────────────┘
 *
 * 2. Quando o visitante envia o formulário (requisição `POST`), o framework
 *    identifica a configuração `'POST' => ['method' => 'enviar']` dentro de
 *    `onHttpMethod` e executa diretamente o método `enviar()`.
 *
 * 3. Dentro de `enviar()`, usamos o método `HttpData::getValue()` para
 *    recuperar os valores dos campos `nome`, `email` e `mensagem` enviados
 *    pelo formulário. Depois, passamos esses valores para `enviarMensagem()`.
 *
 * 4. O framework executa apenas o método definido na configuração: no envio do
 *    formulário, roda apenas `enviar()`, sem rodar `main()`.
 *
 * 💡 OBSERVAÇÃO
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ Neste exemplo, tanto GET quanto POST utilizam a mesma view                │
 * │ `Contato/main.php`.                                                       │
 * │                                                                           │
 * │ O framework define o arquivo padrão de visualização com base no nome do   │
 * │ endpoint (`@main`), e não no método interno do controller. Assim, a view  │
 * │ `Contato/main.php` consegue desenhar o formulário no GET ou exibir as     │
 * │ mensagens de erro ou de sucesso após o POST.                              │
 * └───────────────────────────────────────────────────────────────────────────┘
 */



/**
 * 🟢 CASO 5: GERAÇÃO E ENTREGA DE ARQUIVOS NA SAÍDA File (/relatorios/exportar)
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Nem toda requisição atendida por um controller resulta em uma página HTML ou
 * em JSON. Em relatórios pode haver a necessidade de gerar um arquivo, por
 * exemplo.
 *
 * Quando a rota utiliza a saída `output => 'file'`, a classe `File` é quem
 * cuida do envio. Se o arquivo já existir no disco, ela pode buscá-lo
 * diretamente, mas se o arquivo precisar ser gerado na hora pelo controller
 * (como uma planilha CSV ou um PDF), usamos os recursos da classe base:
 *
 * - `$this->setFileContents()`: entrega o conteúdo bruto do arquivo montado em
 *   memória, seu nome sugerido e o tipo MIME.
 *
 * - `$this->setDownloadableFile()`: define se o arquivo deve ser baixado pelo
 *   navegador ou exibido diretamente na janela.
 *
 * Vamos implementar `/app/controllers/Relatorios.php`:
 *
 * ⚠️ IMPORTANTE
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ No exemplo, o método `gerarDadosDoRelatorio()` representa a busca dos     │
 * │ registros no banco de dados. Essa lógica fica por conta da aplicação.     │
 * └───────────────────────────────────────────────────────────────────────────┘
 *
 * 🖥️ EXEMPLO DE CONTROLLER EM /app/controllers/Relatorios.php
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ <?php                                                                     │
 * │                                                                           │
 * │ namespace app\controllers;                                                │
 * │                                                                           │
 * │ use galastri\modules\Controller;                                          │
 * │                                                                           │
 * │ final class Relatorios extends Controller                                 │
 * │ {                                                                         │
 * │     protected function main(): array                                      │
 * │     {                                                                     │
 * │         $this->setTitle('Central de Relatórios');                         │
 * │                                                                           │
 * │         return [                                                          │
 * │             'opcoes' => [                                                 │
 * │                 'Vendas do Mês',                                          │
 * │                 'Clientes Ativos',                                        │
 * │             ],                                                            │
 * │         ];                                                                │
 * │     }                                                                     │
 * │                                                                           │
 * │     protected function exportar(): array                                  │
 * │     {                                                                     │
 * │         $dados = $this->gerarDadosDoRelatorio();                          │
 * │                                                                           │
 * │         $csv = "ID;Produto;Quantidade;Valor\n";                           │
 * │                                                                           │
 * │         foreach ($dados as $linha) {                                      │
 * │             $csv .= implode(';', $linha) . "\n";                          │
 * │         }                                                                 │
 * │                                                                           │
 * │         $this->setFileContents($csv, 'relatorio-vendas.csv', 'text/csv'); │
 * │         $this->setDownloadableFile(true);                                 │
 * │                                                                           │
 * │         return [];                                                        │
 * │     }                                                                     │
 * │ }                                                                         │
 * └───────────────────────────────────────────────────────────────────────────┘
 *
 * O que acontece aqui na prática:
 *
 * 1. Ao acessar `/relatorios`, o endpoint executado é `main()`. Como esse
 *    endpoint não alterou `output`, ele herda `view` da rota raiz e entrega o
 *    array com as opções de relatórios para desenhar o HTML na tela.
 *
 * 2. Ao acessar `/relatorios/exportar`, o endpoint `@exportar` ativa
 *    `output => 'file'` e o framework executa o método `exportar()`.
 *
 * 3. Dentro de `exportar()`, a aplicação busca os dados e monta a string do
 *    CSV em memória.
 *
 * 4. A chamada `$this->setFileContents()` guarda o conteúdo, o nome do arquivo
 *    (`relatorio-vendas.csv`) e o tipo MIME (`text/csv`) na instância do
 *    controller.
 *
 * 5. Chamamos `$this->setDownloadableFile(true)` para indicar que esse arquivo
 *    deve ser baixado. A saída `File` usará essa instrução para enviar os
 *    cabeçalhos HTTP de download (`Content-Disposition: attachment`).
 *
 * 6. Mesmo na saída `file`, o método do controller precisa retornar um
 *    `array`. Como os dados do arquivo já foram guardados por
 *    `setFileContents()`, retornamos um array vazio `[]`.
 *
 * 💡 OBSERVAÇÃO
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ Qual o papel de `setDownloadableFile()`?                                  │
 * │                                                                           │
 * │ - Com `true`: a saída `File` envia o cabeçalho `Content-Disposition` como │
 * │ `attachment`, forçando o navegador a baixar o arquivo para o disco.       │
 * │                                                                           │
 * │ - Com `false`: é a configuração padrão. A saída envia o conteúdo com seu  │
 * │ tipo MIME, sem solicitar o download. Se o navegador tiver suporte ao      │
 * │ formato do arquivo, como PDF ou imagem, poderá exibi-lo diretamente.      │
 * └───────────────────────────────────────────────────────────────────────────┘
 */



/**
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * ◀️ VOLTAR AO TÓPICO ANTERIOR:
 * 🔗 LINK: /galastri/modules/Controller.php
 */