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
 * o ciclo de execução automático, a fusão de retornos e ferramentas para ler a
 * URL, alterar configurações e controlar saídas.
 *
 * Vamos criar um cenário contínuo de um sistema de catálogo e relatórios,
 * exercitando cada recurso oferecido pela classe base.
 */



/**
 * 🟢 CENÁRIO E CONFIGURAÇÃO DAS ROTAS
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Antes de criar os controllers da aplicação, configuramos no arquivo
 * `/app/config/routes.php` as rotas que vão exercitar as ferramentas da classe
 * `Controller`:
 *
 * O arquivo de configuração de rotas a seguir possui:
 * - Rotas com métodos adicionais via `httpMethod` (POST).
 * 
 * - Rotas com nós dinâmicos e parâmetros obrigatórios e opcionais.
 * 
 * - Rotas com saída `view` que demandam templates alternativos.
 * 
 * - Rotas com saída `file` pra gerar arquivos na hora ou entregar arquivos do
 *   disco.
 *
 * 🖥️ EXEMPLO DE CONFIGURAÇÃO EM /app/config/routes.php
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ <?php                                                                     │
 * │                                                                           │
 * │ return [                                                                  │
 * │     '/' => [                                                              │
 * │         'projectName' => 'Loja Modelo',                                   │
 * │         'output' => 'view',                                               │
 * │                                                                           │
 * │         '@main' => [],                                                    │
 * │                                                                           │
 * │         '/catalogo' => [                                                  │
 * │             '/?categoria' => [                                            │
 * │                 '@main' => [                                              │
 * │                     'parameters' => '/?ordem',                            │
 * │                 ],                                                        │
 * │                 '@produto' => [                                           │
 * │                     'parameters' => '/slug',                              │
 * │                 ],                                                        │
 * │                 '@novo' => [                                              │
 * │                     'httpMethod' => [                                     │
 * │                         'POST' => '@salvar',                              │
 * │                     ],                                                    │
 * │                 ],                                                        │
 * │             ],                                                            │
 * │         ],                                                                │
 * │                                                                           │
 * │         '/relatorios' => [                                                │
 * │             '@main' => [],                                                │
 * │             '@painel' => [],                                              │
 * │                                                                           │
 * │             '@exportar' => [                                              │
 * │                 'output' => 'file',                                       │
 * │             ],                                                            │
 * │             '@manual' => [                                                │
 * │                 'output' => 'file',                                       │
 * │             ],                                                            │
 * │         ],                                                                │
 * │     ],                                                                    │
 * │ ];                                                                        │
 * └───────────────────────────────────────────────────────────────────────────┘
 *
 * Com essas rotas definidas:
 *
 * - A rota `/catalogo/?categoria` usa um nó dinâmico. O framework direciona o
 *   acesso para a classe controller `Categoria`, guardando o valor passado na
 *   URL (por exemplo, `eletronicos`) pra ser consultado no controller.
 *
 * - O endpoint `@main` aceita um parâmetro opcional (`/?ordem`), enquanto
 *   `@produto` exige um parâmetro obrigatório (`/slug`).
 *
 * - O endpoint `@novo` usa `httpMethod` pra executar um método adicional
 *   `salvar()` quando receber uma requisição POST.
 *
 * - A rota `/relatorios` contém endpoints com saída padrão `view` (onde vamos
 *   trocar template e pasta de view dinamicamente) e endpoints com saída
 *   `file` (onde vamos gerar um arquivo na hora e entregar outro do disco).
 */



/**
 * 🟢 CASO 1: O CICLO DE VIDA DO CONTROLLER E MÉTODOS HTTP
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Para entender como a classe `Controller` trabalha, precisamos olhar para o
 * construtor dela. Quando o framework atende a uma rota, ele cria uma instância
 * do nosso controller:
 *
 * 1. O construtor herdado de `Controller` roda automaticamente.
 * 
 * 2. Ele chama o método associado ao endpoint da rota (por exemplo, `main()` ou
 *    `novo()`).
 * 
 * 3. Se a rota tiver uma configuração `httpMethod` compatível com a requisição
 *    (como um `POST`), ele chama também esse método adicional.
 * 
 * 4. Por fim, reúne os arrays retornados em uma única resposta.
 *
 * Todo controller precisa obrigatoriamente implementar o método `main()`, que
 * deve sempre retornar um `array`. Os demais métodos criados para outros
 * endpoints também devem retornar um `array`.
 *
 * Vamos criar o controller em `/app/controllers/catalogo/Categoria.php` e
 * implementar o endpoint `@novo`, que recebe requisições GET e POST:
 *
 * 🖥️ EXEMPLO EM /app/controllers/catalogo/Categoria.php
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ <?php                                                                     │
 * │                                                                           │
 * │ namespace app\controllers\catalogo;                                       │
 * │                                                                           │
 * │ use galastri\modules\Controller;                                          │
 * │ use galastri\modules\HttpData;                                            │
 * │                                                                           │
 * │ final class Categoria extends Controller                                  │
 * │ {                                                                         │
 * │     protected function main(): array                                      │
 * │     {                                                                     │
 * │         return [];                                                        │
 * │     }                                                                     │
 * │                                                                           │
 * │     protected function novo(): array                                      │
 * │     {                                                                     │
 * │         return [                                                          │
 * │             'tituloFormulario' => 'Cadastrar Novo Produto',               │
 * │             'marcasDisponiveis' => ['Marca A', 'Marca B'],                │
 * │         ];                                                                │
 * │     }                                                                     │
 * │                                                                           │
 * │     protected function salvar(): array                                    │
 * │     {                                                                     │
 * │         $nome = (string) HttpData::getValue('nome');                      │
 * │                                                                           │
 * │         if ($nome === '') {                                               │
 * │             return [                                                      │
 * │                 'erro' => 'O nome do produto é obrigatório.',             │
 * │             ];                                                            │
 * │         }                                                                 │
 * │                                                                           │
 * │         // A aplicação realiza o salvamento no banco de dados.            │
 * │                                                                           │
 * │         return [                                                          │
 * │             'sucesso' => true,                                            │
 * │             'mensagem' => 'Produto cadastrado com sucesso!',              │
 * │         ];                                                                │
 * │     }                                                                     │
 * │ }                                                                         │
 * └───────────────────────────────────────────────────────────────────────────┘
 *
 * Veja o que acontece em cada tipo de requisição ao acessar
 * `/catalogo/eletronicos/novo`:
 *
 * - Numa requisição GET: o framework chama apenas o método da rota (`novo()`).
 *   A view recebe `tituloFormulario` e `marcasDisponiveis` pra desenhar o
 *   formulário na tela.
 *
 * - Numa requisição POST: o construtor executa primeiro `novo()` e, em seguida,
 *   executa `salvar()`. Os dois retornos são combinados com `array_merge()`. A
 *   view recebe os dados do formulário e também as chaves `sucesso` e
 *   `mensagem` retornadas por `salvar()`.
 *
 * 💡 OBSERVAÇÃO
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ Se o método da rota e o método HTTP retornarem uma chave com o mesmo      │
 * │ nome (por exemplo, `mensagem`), o valor do método HTTP tem precedência    │
 * │ e substitui o anterior na resposta final.                                 │
 * └───────────────────────────────────────────────────────────────────────────┘
 */



/**
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * ◀️ VOLTAR AO TÓPICO ANTERIOR:
 * 🔗 LINK: /galastri/modules/Controller.php
 */
