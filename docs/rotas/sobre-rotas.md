```php
/**
 * -----------------------------------------------------------------------------
 * 📂
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                                SOBRE ROTAS                                ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 *
 * Uma rota nada mais é do que o caminho de uma URL. No framework, este caminho
 * deve, via de regra, apontar para uma classe controller e definir qual método
 * desta classe será executado.
 *
 * A URL é processada ignorando o domínio/subdomínio e dividindo o restante em
 * partes fundamentais que determinam o comportamento do framework. Esse
 * comportamento é configurado no arquivo de rotas.
 *
 * As partes da URL operam em contextos definidos no arquivo de configuração de
 * rotas. Existem três contextos principais:
 *
 * ┌────────────┬──────────────────────────────────────────────────────────────┐
 * │ Contexto   │ Descrição                                                    │
 * ├────────────┼──────────────────────────────────────────────────────────────┤
 * │ `node`     │ Representa a parte da URL que define a controller a ser      │
 * │            │ chamada. Por padrão, a `string` do `node` tem o mesmo nome   │
 * │            │ da classe correspondente, convertida para PascalCase. No     │
 * │            │ arquivo de configuração de rotas, toda chave iniciada com    │
 * │            │ `/` indica um `node`.                                        │
 * ├────────────┼──────────────────────────────────────────────────────────────┤
 * │ `endpoint` │ Representa o final da rota, indicando o método da            │
 * │            │ controller que será executado. Por padrão, a `string` do     │
 * │            │ `endpoint` tem o mesmo nome do método correspondente,        │
 * │            │ convertido para camelCase. No arquivo de configuração de     │
 * │            │ rotas, toda chave iniciada com `@` indica um `endpoint`.     │
 * ├────────────┼──────────────────────────────────────────────────────────────┤
 * │ `route`    │ Representa toda a URL (exceto o domínio), partindo do        │
 * │            │ primeiro `node` até o `endpoint`.                            │
 * └────────────┴──────────────────────────────────────────────────────────────┘
 *
 * ✔️ EXEMPLO
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ Dada a URL a seguir: `www.dominio.com/produtos/listar` e a seguinte       │
 * │ configuração de rotas:                                                    │
 * │ ┌────────────────────────────────────────────────┐                        │
 * │ │ <?php                                          │                        │
 * │ │                                                │                        │
 * │ │ return [                                       │                        │
 * │ │     '/' => [                                   │                        │
 * │ │         'projectName' => 'Galastri Framework', │                        │
 * │ │         'output' => 'view',                    │                        │
 * │ │                                                │                        │
 * │ │         '@main' => [                           │                        │
 * │ │             ...                                │                        │
 * │ │         ],                                     │                        │
 * │ │                                                │                        │
 * │ │         '/produtos' => [                       │                        │
 * │ │             '@main' => [                       │                        │
 * │ │                 ...                            │                        │
 * │ │             ],                                 │                        │
 * │ │             '@listar' => [                     │                        │
 * │ │                 ...                            │                        │
 * │ │             ],                                 │                        │
 * │ │         ],                                     │                        │
 * │ │     ],                                         │                        │
 * │ │ ];                                             │                        │
 * │ └────────────────────────────────────────────────┘                        │
 * └───────────────────────────────────────────────────────────────────────────┘
 *
 * O framework processará a URL da seguinte forma:
 *
 * 1. IDENTIFICA O NODE  
 *    A primeira parte da URL é `produtos`. O framework busca no arquivo de
 *    configuração e encontra a chave `/produtos`.
 *
 *    O caractere `/` indica que `produtos` é um `node`, ou seja, corresponde a
 *    uma classe controller chamada `Produtos`.
 *
 * 2. IDENTIFICA O ENDPOINT  
 *    A segunda parte da URL é `listar`. Dentro do `node` `/produtos`, o
 *    framework busca pela chave correspondente e encontra `@listar`.
 *
 *    O caractere `@` indica que `listar` é um `endpoint`, ou seja, um método
 *    dentro da classe `Produtos`, chamado `listar()`.
 *
 * 3. EXECUTA O MÉTODO LISTAR()
 *    O framework executa o método `listar()` da classe `Produtos`,
 *    processando os dados e retornando o resultado ao usuário.
 *
 *    Esse mecanismo permite um roteamento flexível e organizado, facilitando a
 *    estruturação das rotas dentro do framework.
 *
 *
 *
 * 🔶 PARTES ADICIONAIS
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Apesar do endpoint representar o final da rota, a URL pode conter partes
 * adicionais, como parâmetros e query strings. São elas:
 *
 * ┌─────────────┬─────────────────────────────────────────────────────────────┐
 * │ NOME        │ DESCRIÇÃO                                                   │
 * ├─────────────┼─────────────────────────────────────────────────────────────┤
 * │ parameters  │ Representam valores dinâmicos passados diretamente na URL,  │
 * │             │ separados por `/`. Esses valores podem ser armazenados e    │
 * │             │ utilizados pelo _controller_.                               │
 * ├─────────────┼─────────────────────────────────────────────────────────────┤
 * │ querystring │ Representam valores passados após um `?`, geralmente usados │
 * │             │ para filtros e configurações. A estrutura padrão é          │
 * │             │ `?chave1=valor1&chave2=valor2`.                             │
 * └─────────────┴─────────────────────────────────────────────────────────────┘
 *
 * ✔️ EXEMPLO
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ Dada a URL a seguir: `www.dominio.com/produtos/listar/todos` e a seguinte │
 * │ configuração de rotas:                                                    │
 * │ ┌─────────────────────────────────────────────┐                           │
 * │ │ <?php                                       │                           │
 * │ │                                             │                           │
 * │ │ return [                                    │                           │
 * │ │     '/' => [                                │                           │
 * │ │         'output' => 'view',                 │                           │
 * │ │                                             │                           │
 * │ │         '@main' => [                        │                           │
 * │ │             ...                             │                           │
 * │ │         ],                                  │                           │
 * │ │                                             │                           │
 * │ │         '/produtos' => [                    │                           │
 * │ │             '@main' => [                    │                           │
 * │ │                 ...                         │                           │
 * │ │             ],                              │                           │
 * │ │             '@listar' => [                  │                           │
 * │ │                 'parameters' => '/exibicao' │                           │
 * │ │             ],                              │                           │
 * │ │         ],                                  │                           │
 * │ │     ],                                      │                           │
 * │ │ ];                                          │                           │
 * │ └─────────────────────────────────────────────┘                           │
 * └───────────────────────────────────────────────────────────────────────────┘
 *
 * O framework irá processar a URL da mesma forma do primeiro exemplo, mas com
 * uma execução adicional:
 *
 * 1. IDENTIFICA O NODE
 *
 * 2. IDENTIFICA O ENDPOINT
 *
 * 3. ARMAZENA OS PARÂMETROS
 *    A terceira parte da URL é `todos`. Dentro do endpoint `@listar`,
 *    a configuração `parameters` tem o valor `/exibicao`. O framework
 *    guarda `todos` no parâmetro `exibicao`, que ficará disponível
 *    para o controller.
 *
 * 4. EXECUTA O MÉTODO LISTAR()
 */



/**
 * 🟢 PARÂMETROS DE ROTA
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Os parâmetros de uma rota podem ser de dois tipos:
 *
 * 1. PARÂMETRO OBRIGATÓRIO '/'
 *    Indica um parâmetro que deve obrigatoriamente estar presente na URL. Caso
 *    contrário, a rota será considerada inválida pelo framework.
 *
 *    Como é definido na configuração: `/parametro`
 *
 * 2. PARÂMETRO OPCIONAL '/?'
 *    Indica um parâmetro opcional, que pode ser omitido na URL sem invalidar a
 *    rota.
 *
 *    Como é definido na configuração: `/?parametro`
 *
 * ✔️ EXEMPLO
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ Dada a URL a seguir: `www.dominio.com/produtos/listar/todos/decrescente`  │
 * │ e a seguinte configuração de rotas:                                       │
 * │ ┌────────────────────────────────────────────────┐                        │
 * │ │ <?php                                          │                        │
 * │ │                                                │                        │
 * │ │ return [                                       │                        │
 * │ │     '/' => [                                   │                        │
 * │ │         'projectName' => 'Galastri Framework', │                        │
 * │ │                                                │                        │
 * │ │         'output' => 'view',                    │                        │
 * │ │                                                │                        │
 * │ │         '@main' => [                           │                        │
 * │ │             ...                                │                        │
 * │ │         ],                                     │                        │
 * │ │                                                │                        │
 * │ │         '/produtos' => [                       │                        │
 * │ │             '@main' => [                       │                        │
 * │ │                 ...                            │                        │
 * │ │             ],                                 │                        │
 * │ │                                                │                        │
 * │ │             '@listar' => [                     │                        │
 * │ │                 'parameters' => '/tipo/?ordem' │                        │
 * │ │             ],                                 │                        │
 * │ │         ],                                     │                        │
 * │ │     ],                                         │                        │
 * │ │ ];                                             │                        │
 * │ └────────────────────────────────────────────────┘                        │
 * └───────────────────────────────────────────────────────────────────────────┘
 *
 * -  Na URL de exemplo, `todos` e `decrescente` são parâmetros. O primeiro é
 *    armazenado em `tipo`, enquanto o segundo é armazenado em `ordem`.
 *
 * -  Se a URL não incluir `decrescente`, a rota continuará válida, pois `ordem`
 *    é um parâmetro opcional.
 *
 * -  Se não houver valor para o parâmetro obrigatório `tipo`, a rota será
 *    invalidada.
 *
 * ┌─────────────────────────────────────────────────────┬─────────┐
 * │ URL                                                 │ Válido? │
 * ├─────────────────────────────────────────────────────┼─────────┤
 * │ `www.dominio.com/produtos/listar/todos/decrescente` │ Sim     │
 * ├─────────────────────────────────────────────────────┼─────────┤
 * │ `www.dominio.com/produtos/listar/novos`             │ Sim     │
 * ├─────────────────────────────────────────────────────┼─────────┤
 * │ `www.dominio.com/produtos/listar/novos/crescente`   │ Sim     │
 * ├─────────────────────────────────────────────────────┼─────────┤
 * │ `www.dominio.com/produtos/listar`                   │ Não     │
 * └─────────────────────────────────────────────────────┴─────────┘
 */



/**
 * 🟢 NÓS DINÂMICOS
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Existem casos em que se deseja que nodes armazenem um valor dinâmico, em vez
 * de representarem uma classe controller específica.
 *
 * ✔️ EXEMPLO
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ Dada a URL a seguir: `www.dominio.com/usuario/maria/painel`               │
 * │                                                                           │
 * │ Suponha que o valor `maria` varie de acordo com o nome de um usuário. Ou  │
 * │ seja, onde está `maria`, poderia estar `jose` ou qualquer outro valor que │
 * │ represente um usuário                                                     │
 * │                                                                           │
 * │ Nesta situação, eu teria que criar uma classe pra cada possível usuário,  │
 * │ caso a estrutura de controllers fosse rígida.                             │
 * │                                                                           │
 * │ É aí que entra o `node dinâmico`. Ao invés de definir um controller fixo, │
 * │ definimos que esta parte de URL deverá ser tratada como um parâmetro e    │
 * │ não como um nome de controller.                                           │
 * │                                                                           │
 * │ Neste caso, `maria` deve ser armazenado como um valor dinâmico e não ser  │
 * │ interpretado como uma classe.                                             │
 * │                                                                           │
 * │ _Mas qual classe será chamada nesse caso?_ O framework determinará isso   │
 * │ com base na configuração de `node dinâmico`.                              │
 * │                                                                           │
 * │ Veja como seria configurado nas rotas:                                    │
 * │ ┌────────────────────────────────────────────────┐                        │
 * │ │ <?php                                          │                        │
 * │ │                                                │                        │
 * │ │ return [                                       │                        │
 * │ │     '/' => [                                   │                        │
 * │ │         'projectName' => 'Galastri Framework', │                        │
 * │ │         'output' => 'view',                    │                        │
 * │ │                                                │                        │
 * │ │         '@main' => [                           │                        │
 * │ │             ...                                │                        │
 * │ │         ],                                     │                        │
 * │ │                                                │                        │
 * │ │         '/usuario' => [                        │                        │
 * │ │             '@main' => [                       │                        │
 * │ │                 ...                            │                        │
 * │ │             ],                                 │                        │
 * │ │                                                │                        │
 * │ │             '/?apelido' => [                   │                        │
 * │ │                 '@main' => [                   │                        │
 * │ │                     ...                        │                        │
 * │ │                 ],                             │                        │
 * │ │                 '@painel' => [                 │                        │
 * │ │                     ...                        │                        │
 * │ │                 ],                             │                        │
 * │ │             ],                                 │                        │
 * │ │         ],                                     │                        │
 * │ │     ],                                         │                        │
 * │ │ ];                                             │                        │
 * │ └────────────────────────────────────────────────┘                        │
 * │                                                                           │
 * │ O `node dinâmico` aqui é a configuração `/?apelido`.                      │
 * └───────────────────────────────────────────────────────────────────────────┘
 *
 * O processamento da URL ocorre como já mencionado, até chegar em `/usuario`.
 * Depois, o framework processa a parte `maria` da seguinte forma:
 *
 * 1. ARMAZENA O VALOR DINÂMICO
 *    O framework entende que `maria` deve ser armazenada como o valor de
 *    parâmetro (aqui no caso chamado `apelido`), que ficará disponível para ser
 *    recuperado dentro do controller.
 *
 * 2. IDENTIFICA O NODE CORRETO
 *    Como `maria` ocupa a posição de um `node` sem ser literalmente um node, o
 *    framework não busca uma classe `controller` chamada `Maria`, mas sim uma
 *    classe chamada `Apelido`, que assume o papel de controller.
 *
 * 3. IDENTIFICA E EXECUTA O MÉTODO painel()
 *    O framework executa o método `painel()` da classe `Apelido`, tendo o valor
 *    armazenado `maria` para ser usado conforme necessário.
 *
 *    Esse mecanismo permite que a segunda parte da URL aceite valores que
 *    variam, tornando a rota flexível. O valor dinâmico fica disponível dentro
 *    do método `painel()`, permitindo comportamentos personalizados com base no
 *    parâmetro fornecido.
 */



/**
 * 🟢 URLS SEM ENDPOINT DEFINIDO
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Nem sempre as URLs irão declarar explicitamente um `endpoint`. Em alguns
 * casos, a URL pode terminar em um `node`, sem especificar qual método da
 * classe *controller* deve ser executado.
 *
 * Quando isso ocorre, o framework assume que o método a ser chamado é `main()`,
 * que funciona como um método padrão sempre que o `endpoint` é omitido.
 *
 * Nas saídas `view` e `json`, o nó acessado precisa conter o endpoint `@main`.
 * A saída `file` pode dispensar essa configuração. Os controllers precisam
 * implementar ou herdar o método `main()`, exigido pela classe base.
 *
 * ✔️ EXEMPLO
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ Dada a URL a seguir: `www.dominio.com/produtos` e a seguinte configuração │
 * │ de rotas:                                                                 │
 * │ ┌────────────────────────────────────────────────┐                        │
 * │ │ <?php                                          │                        │
 * │ │                                                │                        │
 * │ │ return [                                       │                        │
 * │ │     '/' => [                                   │                        │
 * │ │         'projectName' => 'Galastri Framework', │                        │
 * │ │         'output' => 'view',                    │                        │
 * │ │                                                │                        │
 * │ │         '@main' => [                           │                        │
 * │ │             ...                                │                        │
 * │ │         ],                                     │                        │
 * │ │                                                │                        │
 * │ │         '/produtos' => [                       │                        │
 * │ │             '@main' => [                       │                        │
 * │ │                 ...                            │                        │
 * │ │             ],                                 │                        │
 * │ │             '@listar' => [                     │                        │
 * │ │                 'parameters' => '/exibicao'    │                        │
 * │ │             ],                                 │                        │
 * │ │         ],                                     │                        │
 * │ │     ],                                         │                        │
 * │ │ ];                                             │                        │
 * │ └────────────────────────────────────────────────┘                        │
 * └───────────────────────────────────────────────────────────────────────────┘
 *
 * O framework processa a URL e identifica que produtos corresponde a um node.
 * No entanto, como não há um endpoint especificado, o framework assume que o
 * endpoint é main e prossegue com o processamento chamando o método main() da
 * classe controller correspondente.
 */



/**
 * 🟢 PARÂMETROS EM URLS SEM ENDPOINT DEFINIDO
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * É possível passar parâmetros para o `endpoint` `main`, da mesma forma que
 * ocorre com `endpoints` explicitamente declarados.
 *
 * ✔️ EXEMPLO
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ Dada a URL a seguir: `www.dominio.com/venda/id/123` e a seguinte          │
 * │ configuração de rotas:                                                    │
 * │ ┌────────────────────────────────────────────────┐                        │
 * │ │ <?php                                          │                        │
 * │ │                                                │                        │
 * │ │ return [                                       │                        │
 * │ │     '/' => [                                   │                        │
 * │ │         'projectName' => 'Galastri Framework', │                        │
 * │ │         'output' => 'view',                    │                        │
 * │ │                                                │                        │
 * │ │         '@main' => [                           │                        │
 * │ │             ...                                │                        │
 * │ │         ],                                     │                        │
 * │ │                                                │                        │
 * │ │         '/venda' => [                          │                        │
 * │ │             '@main' => [                       │                        │
 * │ │                 'parameters' => '/tipo/codigo' │                        │
 * │ │             ],                                 │                        │
 * │ │                                                │                        │
 * │ │             '@listar' => [                     │                        │
 * │ │                 ...                            │                        │
 * │ │             ],                                 │                        │
 * │ │         ],                                     │                        │
 * │ │     ],                                         │                        │
 * │ │ ];                                             │                        │
 * │ └────────────────────────────────────────────────┘                        │
 * └───────────────────────────────────────────────────────────────────────────┘
 *
 * O framework verifica se `id` corresponde a um `endpoint` dentro de `/venda`,
 * mas não encontra uma correspondência. Como não há um `endpoint` explícito, o
 * framework assume `main` como `endpoint` e processa os valores `id` e `123`
 * como parâmetros.
 *
 * Neste caso, `id` é armazenado no parâmetro `tipo`, e `123` no parâmetro
 * `codigo`, tornando esses valores acessíveis dentro do método **`main()`** .
 */



/**
 * 🟢 ORGANIZAÇÃO DOS CONTROLLERS
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * As classes *controllers* são armazenadas em `app/controllers`. Por padrão, a
 * estrutura dos arquivos deve refletir a configuração das rotas.
 * 
 * ✔️ EXEMPLO
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ Dada a URL a seguir: `www.dominio.com/produtos/listar/todos` e a seguinte │
 * │ configuração de rotas:                                                    │
 * │ ┌────────────────────────────────────────────────┐                        │
 * │ │ <?php                                          │                        │
 * │ │                                                │                        │
 * │ │ return [                                       │                        │
 * │ │     '/' => [                                   │                        │
 * │ │         'projectName' => 'Galastri Framework', │                        │
 * │ │         'output' => 'view',                    │                        │
 * │ │                                                │                        │
 * │ │         '@main' => [                           │                        │
 * │ │             ...                                │                        │
 * │ │         ],                                     │                        │
 * │ │                                                │                        │
 * │ │         '/produtos' => [                       │                        │
 * │ │             '@main' => [                       │                        │
 * │ │                 ...                            │                        │
 * │ │             ],                                 │                        │
 * │ │             '@listar' => [                     │                        │
 * │ │                 'parameters' => '/exibicao'    │                        │
 * │ │             ],                                 │                        │
 * │ │         ],                                     │                        │
 * │ │     ],                                         │                        │
 * │ │ ];                                             │                        │
 * │ └────────────────────────────────────────────────┘                        │
 * └───────────────────────────────────────────────────────────────────────────┘
 * 
 * A classe correspondente estará em `app/controllers/Produtos.php` contendo o
 * seguinte código:
 * 
 * 💻 CÓDIGO
 * ┌────────────────────────────────────────┐
 * │ <?php                                  │
 * │ namespace app\controllers;             │
 * │                                        │
 * │ use galastri\modules\Controller;       │
 * │                                        │
 * │ class Produtos extends Controller      │
 * │ {                                      │
 * │     protected function main(): array   │
 * │     {                                  │
 * │         // ...                         │
 * │     }                                  │
 * │                                        │
 * │     protected function listar(): array │
 * │     {                                  │
 * │         // ...                         │
 * │     }                                  │
 * │ }                                      │
 * └────────────────────────────────────────┘
 * 
 * O nome da classe *controller* deve ser idêntico ao nome do arquivo, pois o
 * *autoloader* do **Composer** faz essa correspondência automaticamente.
 * 
 * Se houver aninhamento de `nodes`, a estrutura das pastas deve refletir essa
 * hierarquia.
 * 
 * 
 * ✔️ EXEMPLO
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ Dada a URL a seguir: `www.dominio.com/sobre/nossa-empresa/contato` e a    │
 * │ seguinte configuração de rotas:                                           │
 * │ ┌────────────────────────────────────────────────┐                        │
 * │ │ <?php                                          │                        │
 * │ │                                                │                        │
 * │ │ return [                                       │                        │
 * │ │     '/' => [                                   │                        │
 * │ │         'projectName' => 'Galastri Framework', │                        │
 * │ │         'output' => 'view',                    │                        │
 * │ │                                                │                        │
 * │ │         '@main' => [                           │                        │
 * │ │             ...                                │                        │
 * │ │         ],                                     │                        │
 * │ │                                                │                        │
 * │ │         '/sobre' => [                          │                        │
 * │ │             '@main' => [                       │                        │
 * │ │                 ...                            │                        │
 * │ │             ],                                 │                        │
 * │ │                                                │                        │
 * │ │             '/nossa-empresa' => [              │                        │
 * │ │                 '@main' => [                   │                        │
 * │ │                     ...                        │                        │
 * │ │                 ],                             │                        │
 * │ │                 '@contato' => [                │                        │
 * │ │                     ...                        │                        │
 * │ │                 ],                             │                        │
 * │ │             ],                                 │                        │
 * │ │         ],                                     │                        │
 * │ │                                                │                        │
 * │ │     ],                                         │                        │
 * │ │ ];                                             │                        │
 * │ └────────────────────────────────────────────────┘                        │
 * └───────────────────────────────────────────────────────────────────────────┘
 * 
 * A classe correspondente estará em `app/controllers/Sobre/NossaEmpresa.php`
 * contendo o seguinte código:
 * 
 * 💻 CÓDIGO
 * ┌─────────────────────────────────────────┐
 * │ <?php                                   │
 * │ namespace app\controllers\Sobre;        │
 * │                                         │
 * │ use galastri\modules\Controller;        │
 * │                                         │
 * │ class NossaEmpresa extends Controller   │
 * │ {                                       │
 * │     protected function main(): array    │
 * │     {                                   │
 * │         // ...                          │
 * │     }                                   │
 * │                                         │
 * │     protected function contato(): array │
 * │     {                                   │
 * │         // ...                          │
 * │     }                                   │
 * │ }                                       │
 * └─────────────────────────────────────────┘
 * 
 * O nome da classe *controller* deve ser idêntico ao nome do arquivo, pois o
 * *autoloader* do **Composer** faz essa correspondência automaticamente.
 */



/**
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * ▶️ PRÓXIMO TÓPICO:  
 * 🔗 LINK: /docs/rotas/configuracoes-de-rota.md
 */ 
