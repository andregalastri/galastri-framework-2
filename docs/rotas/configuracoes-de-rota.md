```php
/**
 * -----------------------------------------------------------------------------
 * 📂
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                           CONFIGURAÇÕES DE ROTA                           ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 *
 * O framework é configurado por meio do arquivo de configuração de rotas,
 * permitindo que cada rota tenha definições próprias de forma flexível.
 *
 * As configurações são organizadas por contextos, sendo eles:
 *
 * - `route`, `node` e `endpoint`, que controlam o roteamento e o processamento
 *   de requisições
 *
 * - `debug`, voltado para configurações de depuração.
 */


/**
 * 🟢 CONFIGURAÇÕES DO CONTEXTO route
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Esse contexto reúne configurações aplicadas ao percurso da URL, desde o
 * `node` onde ele foi configurado até o `endpoint`.
 *
 * As configurações desse tipo permanecem ativas ao longo de todo o caminho
 * percorrido, sem serem redefinidas para seus valores padrão a cada `node`.
 * Isso permite definir valores globais para o projeto apenas uma vez, evitando
 * repetições desnecessárias.
 *
 * As configurações do contexto `route` podem ser aplicadas tanto a `nodes`
 * quanto a `endpoints`.
 *
 *
 *
 * 🔶 projectName
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * - Tipo: `string`
 * - Valor padrão: ''
 *
 * Define o nome do projeto. Esse valor é incorporado à saída de dados `meta`,
 * podendo ser utilizado, por exemplo, para definir dinamicamente o título da
 * janela no frontend.
 *
 * 🖥️ EXEMPLO DE USO
 * ┌────────────────────────────────────────────────┐
 * │ <?php                                          │
 * │                                                │
 * │ return [                                       │
 * │     '/' => [                                   │
 * │         'projectName' => 'Galastri Framework', │
 * │                                                │
 * │         '@main' => [                           │
 * │             ...                                │
 * │         ],                                     │
 * │                                                │
 * │         ...                                    │
 * │     ],                                         │
 * │ ];                                             │
 * └────────────────────────────────────────────────┘
 *
 *
 *
 * 🔶 title
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * - Tipo: `string`
 * - Valor padrão: ''
 *
 * Define um título específico para a rota. Esse valor é incluído na saída de
 * dados, permitindo sua aplicação, por exemplo, no título da janela do
 * navegador.
 *
 * 🖥️ EXEMPLO DE USO
 * ┌─────────────────────────────────────────────┐
 * │ <?php                                       │
 * │                                             │
 * │ return [                                    │
 * │     '/' => [                                │
 * │         '@main' => [                        │
 * │             ...                             │
 * │         ],                                  │
 * │                                             │
 * │         '@produtos' => [                    │
 * │             'title' => 'Lista de produtos', │
 * │         ],                                  │
 * │                                             │
 * │         ...                                 │
 * │     ],                                      │
 * │ ];                                          │
 * └─────────────────────────────────────────────┘
 *
 *
 *
 * 🔶 output
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * - Tipo: `string`
 * - Valor padrão: 'view'
 * - Valores permitidos: 'view', 'json', 'file'
 *
 * Define o formato da saída de dados da rota, determinando como a resposta será
 * entregue ao usuário que fez a requisição.
 *
 * ┌────────┬──────────────────────────────────────────────────────────────────┐
 * │ Valor  │ Descrição                                                        │
 * ├────────┼──────────────────────────────────────────────────────────────────┤
 * │ `view` │ Passa o `array` de saída do `endpoint` para um template PHP e/ou │
 * │        │ um arquivo `view` correspondente, retornando um HTML ao usuário. │
 * ├────────┼──────────────────────────────────────────────────────────────────┤
 * │ `json` │ Converte o `array` de saída do `endpoint` para formato JSON e    │
 * │        │ retorna ao usuário.                                              │
 * ├────────┼──────────────────────────────────────────────────────────────────┤
 * │ `file` │ Entrega o conteúdo preparado pelo controller com                 │
 * │        │ `setFileContents()`.                                             │
 * │        │                                                                  │
 * │        │ Quando não há controller, procura o arquivo no disco usando      │
 * │        │ `fileFolder` e o restante da URL.                                │
 * └────────┴──────────────────────────────────────────────────────────────────┘
 * 
 * 🖥️ EXEMPLO DE USO
 * ┌─────────────────────────────────┐
 * │ <?php                           │
 * │                                 │
 * │ return [                        │
 * │     '/' => [                    │
 * │         'output' => 'view',     │
 * │                                 │
 * │         '@main' => [            │
 * │             ...                 │
 * │         ],                      │
 * │                                 │
 * │         '/imagens' => [         │
 * │             'output' => 'file', │
 * │         ],                      │
 * │                                 │
 * │         '/api' => [             │
 * │             'output' => 'json', │
 * │                                 │
 * │             '@main' => [        │
 * │                 ...,            │
 * │             ],                  │
 * │         ],                      │
 * │                                 │
 * │         ...                     │
 * │     ],                          │
 * │ ];                              │
 * └─────────────────────────────────┘
 *
 *
 *
 * 🔶 timezone
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * - Tipo: `string`
 * - Valor padrão: `date_default_timezone_get()`
 *
 * Define o fuso horário (timezone) em formato IANA a ser utilizado para
 * configurações de data e hora no projeto.
 *
 * 🖥️ EXEMPLO DE USO
 * ┌────────────────────────────────────────────┐
 * │ <?php                                      │
 * │                                            │
 * │ return [                                   │
 * │     '/' => [                               │
 * │         'timezone' => 'America/Sao_Paulo', │
 * │                                            │
 * │         '@main' => [                       │
 * │             ...                            │
 * │         ],                                 │
 * │                                            │
 * │         ...                                │
 * │     ],                                     │
 * │ ];                                         │
 * └────────────────────────────────────────────┘
 *
 *
 *
 * 🔶 offline
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * - Tipo: `bool`
 * - Valor padrão: `false`
 *
 * Define se a rota deve ser desativada, tornando-a inacessível. Quando definida
 * como `true`, qualquer tentativa de acesso à rota resultará em uma resposta ou
 * redirecionamento indicando que ela está offline.
 *
 * 🖥️ EXEMPLO DE USO
 * ┌─────────────────────────────────┐
 * │ <?php                           │
 * │                                 │
 * │ return [                        │
 * │     '/' => [                    │
 * │         '@main' => [            │
 * │             ...                 │
 * │         ],                      │
 * │                                 │
 * │         '/produtos' => [        │
 * │             '@main' => [        │
 * │                 ...             │
 * │             ],                  │
 * │         ],                      │
 * │                                 │
 * │         '/sobre-a-empresa' => [ │
 * │             'offline' => true,  │
 * │                                 │
 * │             '@main' => [        │
 * │                 ...             │
 * │             ],                  │
 * │         ],                      │
 * │                                 │
 * │         ...                     │
 * │     ],                          │
 * │ ];                              │
 * └─────────────────────────────────┘
 *
 *
 *
 * 🔶 offlineRedirectTo
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * - Tipo: `string`
 * - Valor padrão: ''
 *
 * Define o endereço de redirecionamento para quando uma rota estiver offline.
 * Caso a rota seja desativada com a configuração `offline` definida como
 * `true`, o usuário será automaticamente redirecionado para o URL especificado
 * em `offlineRedirectTo`.
 *
 * 🖥️ EXEMPLO DE USO
 * ┌───────────────────────────────────────────────────────────┐
 * │ <?php                                                     │
 * │                                                           │
 * │ return [                                                  │
 * │     '/' => [                                              │
 * │         '@main' => [                                      │
 * │             ...                                           │
 * │         ],                                                │
 * │                                                           │
 * │         '/produtos' => [                                  │
 * │             '@main' => [                                  │
 * │                 ...                                       │
 * │             ],                                            │
 * │         ],                                                │
 * │                                                           │
 * │         '/site-em-manutencao' => [                        │
 * │             '@main' => [                                  │
 * │                 ...                                       │
 * │             ],                                            │
 * │         ],                                                │
 * │                                                           │
 * │         '/sobre-a-empresa' => [                           │
 * │             'offline' => true,                            │
 * │                                                           │
 * │             'offlineRedirectTo' => '/site-em-manutencao', │
 * │                                                           │
 * │             ...                                           │
 * │         ],                                                │
 * │                                                           │
 * │         ...                                               │
 * │     ],                                                    │
 * │ ];                                                        │
 * └───────────────────────────────────────────────────────────┘
 *
 * Neste exemplo, ao acessar qualquer página, a navegação segue normalmente,
 * mas ao acessar `www.dominio.com/sobre-a-empresa`, o usuário será
 * redirecionado para `www.dominio.com/site-em-manutencao`.
 *
 *
 *
 * 🔶 offlineMessage
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * - Tipo: `string`
 * - Valor padrão: 'System is temporarily offline.'
 *
 * Define a mensagem da exceção lançada quando a rota está offline e não há
 * redirecionamento configurado em `offlineRedirectTo`. Sua apresentação ao
 * usuário depende da configuração `displayErrors`.
 *
 * 🖥️ EXEMPLO DE USO
 * ┌─────────────────────────────────────────────────────────────────────┐
 * │  <?php                                                              │
 * │                                                                     │
 * │ return [                                                            │
 * │     '/' => [                                                        │
 * │         '@main' => [                                                │
 * │             ...                                                     │
 * │         ],                                                          │
 * │                                                                     │
 * │         '/sobre-a-empresa' => [                                     │
 * │             'offline' => true,                                      │
 * │             'offlineMessage' => 'Sistema offline temporariamente.', │
 * │                                                                     │
 * │             '@main' => [                                            │
 * │                 ...                                                 │
 * │             ],                                                      │
 * │         ],                                                          │
 * │                                                                     │
 * │         ...                                                         │
 * │     ],                                                              │
 * │ ];                                                                  │
 * └─────────────────────────────────────────────────────────────────────┘
 *
 * No exemplo acima, o funcionamento das outras páginas permanece normal. Ao
 * acessar `www.dominio.com/sobre-a-empresa`, o processamento é interrompido. A
 * mensagem personalizada aparece se `displayErrors` estiver habilitado.
 *
 *
 *
 * 🔶 authTag
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * - Tipo: `string`
 * - Valor padrão: ''
 *
 * Define a tag da autenticação que será verificada ao acessar a rota. Quando
 * preenchida, exige uma autenticação válida para essa tag, conferida por
 * `Authentication::validate()`.
 *
 * 🖥️ EXEMPLO DE USO
 * ┌────────────────────────────────────────────────┐
 * │ <?php                                          │
 * │                                                │
 * │ return [                                       │
 * │     '/' => [                                   │
 * │         '@main' => [                           │
 * │             ...                                │
 * │         ],                                     │
 * │                                                │
 * │         '/area-do-usuario' => [                │
 * │             'authTag' => 'login-area-usuario', │
 * │                                                │
 * │             '@main' => [                       │
 * │                 ...                            │
 * │             ],                                 │
 * │         ],                                     │
 * │                                                │
 * │         ...                                    │
 * │     ],                                         │
 * │ ];                                             │
 * └────────────────────────────────────────────────┘
 *
 * No exemplo acima, o acesso a `www.dominio.com/area-do-usuario` exige uma
 * autenticação válida para a tag `login-area-usuario`.
 *
 *
 *
 * 🔶 authFailRedirectTo
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * - Tipo: `string`
 * - Valor padrão: ''
 *
 * Define o endereço de redirecionamento quando a autenticação falha. O
 * redirecionamento acontece quando esse endereço está preenchido e a saída é
 * diferente de `json`.
 *
 * 🖥️ EXEMPLO DE USO
 * ┌─────────────────────────────────────────────────┐
 * │ <?php                                           │
 * │                                                 │
 * │ return [                                        │
 * │     '/' => [                                    │
 * │         '@main' => [                            │
 * │             ...                                 │
 * │         ],                                      │
 * │                                                 │
 * │         '/login' => [                           │
 * │             '@main' => [                        │
 * │                 ...                             │
 * │             ],                                  │
 * │         ],                                      │
 * │                                                 │
 * │         '/area-do-usuario' => [                 │
 * │             'authTag' => 'login-area-usuario',  │
 * │             'authFailRedirectTo' => '/login',   │
 * │                                                 │
 * │             '@main' => [                        │
 * │                 ...                             │
 * │             ],                                  │
 * │         ],                                      │
 * │                                                 │
 * │         ...                                     │
 * │     ],                                          │
 * │ ];                                              │
 * └─────────────────────────────────────────────────┘
 *
 * No exemplo acima, acessar `www.dominio.com/area-do-usuario` sem uma
 * autenticação válida para a tag `login-area-usuario` provoca o
 * redirecionamento para `www.dominio.com/login`.
 *
 *
 *
 * 🔶 authFailMessage
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * - Tipo: `string`
 * - Valor padrão: 'Forbidden.'
 *
 * Define a mensagem da exceção lançada quando a autenticação falha e não ocorre
 * redirecionamento. Sua apresentação ao usuário depende da configuração
 * `displayErrors`.
 *
 * 🖥️ EXEMPLO DE USO
 * ┌─────────────────────────────────────────────────────────────────┐
 * │ <?php                                                           │
 * │                                                                 │
 * │ return [                                                        │
 * │     '/' => [                                                    │
 * │         '@main' => [                                            │
 * │             ...                                                 │
 * │         ],                                                      │
 * │                                                                 │
 * │         '/area-do-usuario' => [                                 │
 * │             'authTag' => 'login-area-usuario',                  │
 * │             'authFailMessage' => 'Faça o login para acessar.',  │
 * │                                                                 │
 * │             '@main' => [                                        │
 * │                 ...                                             │
 * │             ],                                                  │
 * │         ],                                                      │
 * │                                                                 │
 * │         ...                                                     │
 * │     ],                                                          │
 * │ ];                                                              │
 * └─────────────────────────────────────────────────────────────────┘
 *
 * No exemplo acima, acessar `www.dominio.com/area-do-usuario` sem uma
 * autenticação válida interrompe o processamento. A mensagem "Faça o login para
 * acessar." aparece se `displayErrors` estiver habilitado.
 *
 *
 *
 * 🔶 notFoundRedirectTo
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * - Tipo: `string`
 * - Valor padrão: ''
 *
 * Define o endereço de redirecionamento usado por `notFound()` quando uma rota
 * ou um arquivo não é encontrado. Na saída `json`, esse redirecionamento não é
 * realizado.
 *
 * 🖥️ EXEMPLO DE USO
 * ┌─────────────────────────────────────────────────────┐
 * │ <?php                                               │
 * │                                                     │
 * │ return [                                            │
 * │     '/' => [                                        │
 * │         'notFoundRedirectTo' => '/nao-encontrado',  │
 * │                                                     │
 * │         '@main' => [                                │
 * │             ...                                     │
 * │         ],                                          │
 * │                                                     │
 * │         '/nao-encontrado' => [                      │
 * │             '@main' => [                            │
 * │                 ...                                 │
 * │             ],                                      │
 * │         ],                                          │
 * │                                                     │
 * │         ...                                         │
 * │     ],                                              │
 * │ ];                                                  │
 * └─────────────────────────────────────────────────────┘
 *
 * No exemplo acima, caso o usuário acesse `www.dominio.com/produtos` a rota
 * será considerada inválida pois não foi configurada. O usuário será
 * automaticamente redirecionado para `www.dominio.com/nao-encontrado`.
 *
 *
 *
 * 🔶 notFoundMessage
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * - Tipo: `string`
 * - Valor padrão: '404 - Route not found'
 *
 * Define a mensagem da exceção lançada por `notFound()` quando não ocorre
 * redirecionamento. Sua apresentação ao usuário depende da configuração
 * `displayErrors`.
 *
 * 🖥️ EXEMPLO DE USO
 * ┌──────────────────────────────────────────────────────────────────────┐
 * │ <?php                                                                │
 * │                                                                      │
 * │ return [                                                             │
 * │     '/' => [                                                         │
 * │         'notFoundMessage' => 'O endereço informado foi encontrado.', │
 * │                                                                      │
 * │         '@main' => [                                                 │
 * │             ...                                                      │
 * │         ],                                                           │
 * │                                                                      │
 * │         '/nao-encontrado' => [                                       │
 * │             '@main' => [                                             │
 * │                 ...                                                  │
 * │             ],                                                       │
 * │         ],                                                           │
 * │                                                                      │
 * │         ...                                                          │
 * │     ],                                                               │
 * │ ];                                                                   │
 * └──────────────────────────────────────────────────────────────────────┘
 *
 * No exemplo acima, acessar `www.dominio.com/produtos` resulta em uma resposta
 * HTTP 404. A mensagem personalizada aparece se `displayErrors` estiver
 * habilitado.
 *
 *
 *
 * 🔶 forceRedirectTo
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * - Tipo: `bool` ou `string`
 * - Valor padrão: `false`
 *
 * Força o redirecionamento de uma rota para outra rota ou página, seja interna
 * ou externa.
 *
 * 🖥️ EXEMPLO DE USO
 * ┌──────────────────────────────────────────────────────────────────────┐
 * │ <?php                                                                │
 * │                                                                      │
 * │ return [                                                             │
 * │     '/' => [                                                         │
 * │         '@main' => [                                                 │
 * │             ...                                                      │
 * │         ],                                                           │
 * │                                                                      │
 * │         '/produtos' => [                                             │
 * │             'forceRedirectTo' => '/produtos-novo',                   │
 * │                                                                      │
 * │             '@main' => [                                             │
 * │                 ...                                                  │
 * │             ],                                                       │
 * │         ],                                                           │
 * │                                                                      │
 * │         '/produtos-novo' => [                                        │
 * │             '@main' => [                                             │
 * │                 ...                                                  │
 * │             ],                                                       │
 * │         ],                                                           │
 * │                                                                      │
 * │         '/nosso-whatsapp' => [                                       │
 * │             'forceRedirectTo' => 'https://api.example.com?code=001', │
 * │                                                                      │
 * │             '@main' => [                                             │
 * │                 ...                                                  │
 * │             ],                                                       │
 * │         ],                                                           │
 * │                                                                      │
 * │         ...                                                          │
 * │     ],                                                               │
 * │ ];                                                                   │
 * └──────────────────────────────────────────────────────────────────────┘
 *
 * No exemplo acima:
 *
 * - Caso o usuário acesse `www.dominio.com/produtos`, ele será automaticamente
 *   redirecionado para `www.dominio.com/produtos-novo`.
 *
 * - Caso o usuário acesse `www.dominio.com/nosso-whatsapp`, ele será
 *   redirecionado para o endereço externo configurado.
 *
 *
 *
 * 🔶 namespace
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * - Tipo: `string`
 * - Valor padrão: ''
 *
 * Altera o namespace para um caminho diferente do padrão, redirecionando para
 * outra localização da classe controller.
 *
 * 🖥️ EXEMPLO DE USO
 * ┌──────────────────────────────────────────┐
 * │ <?php                                    │
 * │                                          │
 * │ return [                                 │
 * │     '/' => [                             │
 * │         '@main' => [                     │
 * │             ...                          │
 * │         ],                               │
 * │                                          │
 * │         '/produtos' => [                 │
 * │             '@main' => [                 │
 * │                 ...                      │
 * │             ],                           │
 * │         ],                               │
 * │                                          │
 * │         '/empresa' => [                  │
 * │             'namespace' => 'app\outros', │
 * │                                          │
 * │             '@main' => [                 │
 * │                 ...                      │
 * │             ],                           │
 * │         ],                               │
 * │                                          │
 * │         ...                              │
 * │     ],                                   │
 * │ ];                                       │
 * └──────────────────────────────────────────┘
 *
 * No exemplo acima, o namespace padrão para a rota www.dominio.com/empresa
 * seria app\controllers, mas foi alterado para app\outros, possivelmente porque
 * o arquivo da classe controller Empresa está localizado na pasta app/outros.
 *
 *
 * 🔶 browserCache
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * - Tipo: `array`
 * - Valor padrão: []
 *
 * Configura o cache das respostas `view` e dos arquivos exibidos pela saída
 * `file`. É aplicado a requisições GET ou HEAD com status 200. Rotas com
 * `authTag` preenchida recebem `Cache-Control: private, no-store`.
 *
 * CONFIGURAÇÃO DOS ÍNDICES
 * ┌────────┬──────────┬─────────────────────────────────────────────────────┐
 * │ Índice │ Tipo     │ Descrição                                           │
 * ├────────┼──────────┼─────────────────────────────────────────────────────┤
 * │ `[0]`  │ `int`    │ A duração do cache em segundos.                     │
 * ├────────┼──────────┼─────────────────────────────────────────────────────┤
 * │ `[1]`  │ `string` │ Opcional: As definições do cabeçalho Cache-Control. │
 * └────────┴──────────┴─────────────────────────────────────────────────────┘
 *
 * 🖥️ EXEMPLO DE USO
 * ┌──────────────────────────────────────────────────────────┐
 * │ <?php                                                    │
 * │                                                          │
 * │ return [                                                 │
 * │     '/' => [                                             │
 * │         'browserCache' => [86400, 'immutable, private'], │
 * │                                                          │
 * │         '@main' => [                                     │
 * │             ...                                          │
 * │         ],                                               │
 * │                                                          │
 * │         '/produtos' => [                                 │
 * │             '@main' => [                                 │
 * │                 ...                                      │
 * │             ],                                           │
 * │         ],                                               │
 * │                                                          │
 * │         '/empresa' => [                                  │
 * │             'browserCache' => [],                        │
 * │                                                          │
 * │             '@main' => [                                 │
 * │                 ...                                      │
 * │             ],                                           │
 * │         ],                                               │
 * │                                                          │
 * │         ...                                              │
 * │     ],                                                   │
 * │ ];                                                       │
 * └──────────────────────────────────────────────────────────┘
 *
 * No exemplo acima, todos os endpoints das rotas serão armazenados em cache no
 * navegador do usuário por 86400 segundos (1 dia), com as propriedades
 * 'immutable, private', exceto para a rota www.dominio.com/empresa, que teve o
 * browserCache desabilitado (vazio).
 *
 * 🖥️ EXEMPLO DE USO
 * ┌────────────────────────────────────┐
 * │ <?php                              │
 * │                                    │
 * │ return [                           │
 * │     '/' => [                       │
 * │         'browserCache' => [86400], │
 * │                                    │
 * │         '@main' => [               │
 * │             ...                    │
 * │         ],                         │
 * │                                    │
 * │         ...                        │
 * │     ],                             │
 * │ ];                                 │
 * └────────────────────────────────────┘
 *
 * No exemplo acima, todos os endpoints das rotas serão armazenados em cache no
 * navegador do usuário por 86400 segundos (1 dia). Como o segundo índice foi
 * omitido, então ele automaticamente irá definir como 'public'.
 *
 *
 * 
 * 🔶 templateFile
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * - Tipo: `string`
 * - Valor padrão: `'/app/templates/template.php'`
 *
 *
 * Define o caminho do arquivo de template que será utilizado para retornar a
 * `view` HTML, caso o formato de saída da rota seja `view`.
 *
 * 🖥️ EXEMPLO DE USO
 * ┌─────────────────────────────────────────────────────────────────────┐
 * │ <?php                                                               │
 * │                                                                     │
 * │ return [                                                            │
 * │     '/' => [                                                        │
 * │         'templateFile' => '/app/template/template.php',             │
 * │                                                                     │
 * │         '@main' => [                                                │
 * │             ...                                                     │
 * │         ],                                                          │
 * │                                                                     │
 * │         '/produtos' => [                                            │
 * │             '@main' => [                                            │
 * │                 ...                                                 │
 * │             ],                                                      │
 * │         ],                                                          │
 * │                                                                     │
 * │         '/empresa' => [                                             │
 * │             'templateFile' => '/app/template/template-empresa.php', │
 * │                                                                     │
 * │             '@main' => [                                            │
 * │                 ...                                                 │
 * │             ],                                                      │
 * │         ],                                                          │
 * │                                                                     │
 * │         ...                                                         │
 * │     ],                                                              │
 * │ ];                                                                  │
 * └─────────────────────────────────────────────────────────────────────┘
 *
 * No exemplo acima, todas as rotas utilizam o template localizado em
 * /app/template/template.php, exceto a rota www.dominio.com/empresa, que
 * utiliza o template localizado em /app/template/template-empresa.php.
 *
 *
 * 🔶 viewFolder
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * - Tipo: `string`
 * - Valor padrão: '/app/views'
 *
 * Define o diretório onde os arquivos de view (HTML) estão localizados quando o
 * output for definido como view.
 *
 * 🖥️ EXEMPLO DE USO
 * ┌───────────────────────────────────────────────────┐
 * │ <?php                                             │
 * │                                                   │
 * │ return [                                          │
 * │     '/' => [                                      │
 * │         'output' => 'view',                       │
 * │                                                   │
 * │         '@main' => [                              │
 * │             ...                                   │
 * │         ],                                        │
 * │                                                   │
 * │         '/empresa' => [                           │
 * │             'viewFolder' => '/app/views-empresa', │
 * │                                                   │
 * │             '@main' => [                          │
 * │                 ...                               │
 * │             ],                                    │
 * │                                                   │
 * │             '@contato' => [                       │
 * │                 ...                               │
 * │             ],                                    │
 * │                                                   │
 * │         ],                                        │
 * │                                                   │
 * │         ...                                       │
 * │     ],                                            │
 * │ ];                                                │
 * └───────────────────────────────────────────────────┘
 *
 * No exemplo acima, os `nodes` e `endpoints` irão acessar as `views` no
 * diretório padrão, com exceção da rota `www.dominio.com/empresa`, onde as
 * `views` serão localizadas na pasta `/app/views-empresa`.
 *
 *
 *
 * 🔶 fileFolder
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * - Tipo: `string`
 * - Valor padrão: ''
 *
 * Define o diretório usado pela saída `file` para procurar arquivos no disco.
 * Essa busca acontece quando o controller não fornece conteúdo por
 * `setFileContents()`, inclusive quando a requisição segue sem controller.
 *
 * Esse parâmetro só é relevante quando o valor de `output` for `file`.
 *
 * 🖥️ EXEMPLO DE USO
 * ┌───────────────────────────────────────────────────┐
 * │ <?php                                             │
 * │                                                   │
 * │ return [                                          │
 * │     '/' => [                                      │
 * │         'output' => 'view',                       │
 * │                                                   │
 * │         '@main' => [                              │
 * │             ...                                   │
 * │         ],                                        │
 * │                                                   │
 * │         '/images' => [                            │
 * │             'output' => 'file',                   │
 * │             'fileFolder' => '/app/images/public', │
 * │         ],                                        │
 * │                                                   │
 * │         ...                                       │
 * │     ],                                            │
 * │ ];                                                │
 * └───────────────────────────────────────────────────┘
 *
 * No exemplo acima, a saída procura arquivos em `/app/images/public` quando
 * não há controller ou quando ele não fornece conteúdo por
 * `setFileContents()`.
 *
 * Se houver um arquivo chamado `logo.jpg` em `/app/images/public/logo.jpg`, a
 * URL `www.dominio.com/images/logo.jpg` irá retornar esse arquivo.
 *
 *
 * 🔶 isDownloadableFile
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * - Tipo: `bool`
 * - Valor padrão: `false`
 *
 * Se `true`, define que o arquivo acessado será tratado como um arquivo para
 * download, ou seja, o navegador irá entender que o arquivo deve ser baixado ao
 * invés de exibido. Se `false`, o arquivo será exibido diretamente no navegador
 * (quando possível).
 *
 * Esse parâmetro só é relevante quando o valor de `output` for `file`.
 *
 * 🖥️ EXEMPLO DE USO
 * ┌───────────────────────────────────────────────────┐
 * │ <?php                                             │
 * │                                                   │
 * │ return [                                          │
 * │     '/' => [                                      │
 * │         'output' => 'view',                       │
 * │                                                   │
 * │         '@main' => [                              │
 * │             ...                                   │
 * │         ],                                        │
 * │                                                   │
 * │         '/images' => [                            │
 * │             'output' => 'file',                   │
 * │             'fileFolder' => '/app/images/public', │
 * │             'isDownloadableFile' => true,         │
 * │         ],                                        │
 * │                                                   │
 * │         ...                                       │
 * │     ],                                            │
 * │ ];                                                │
 * └───────────────────────────────────────────────────┘
 *
 * No exemplo acima, ao acessar um arquivo na rota `/images`, o arquivo não será
 * exibido diretamente no navegador, mas será forçado o download, devido à
 * configuração de `isDownloadableFile` estar definida como `true`.
 *
 *
 *
 * 🔶 allowedFileExtensions
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * - Tipo: `array`
 * - Valor padrão: []
 *
 * Define as extensões permitidas para os arquivos procurados no disco.
 * Uma lista vazia não restringe as extensões. Quando a lista está preenchida,
 * uma extensão que não esteja nela provoca uma exceção.
 *
 * Esse parâmetro só é relevante quando o valor de `output` for `file`.
 *
 * 🖥️ EXEMPLO DE USO
 * ┌───────────────────────────────────────────────────────────────┐
 * │ <?php                                                         │
 * │                                                               │
 * │ return [                                                      │
 * │     '/' => [                                                  │
 * │         'output' => 'view',                                   │
 * │                                                               │
 * │         '@main' => [                                          │
 * │             ...                                               │
 * │         ],                                                    │
 * │                                                               │
 * │         '/images' => [                                        │
 * │             'output' => 'file',                               │
 * │             'fileFolder' => '/app/images/public',             │
 * │             'allowedFileExtensions' => ['jpg', 'gif', 'png'], │
 * │         ],                                                    │
 * │                                                               │
 * │         ...                                                   │
 * │     ],                                                        │
 * │ ];                                                            │
 * └───────────────────────────────────────────────────────────────┘
 *
 * No exemplo acima, o framework verifica a extensão do arquivo solicitado. Se
 * ela não for `jpg`, `gif` ou `png`, lança uma exceção e interrompe a entrega
 * do arquivo.
 *
 *
 *
 * 🔶 permissionFailMessage
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * - Tipo: `string`
 * - Valor padrão: 'Forbidden.'
 *
 * Define a mensagem padrão usada pelo módulo `Permission` quando uma
 * verificação de permissão falha. Sua apresentação ao usuário depende da
 * configuração `displayErrors`.
 *
 * ⚠️ IMPORTANTE
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ Essa configuração só funcionará se o módulo `Permission` estiver sendo    │
 * │ utilizado dentro do método (como em `@deletar` no exemplo a seguir), pois │
 * │ é esse módulo que gerencia e verifica as permissões do usuário.           │
 * └───────────────────────────────────────────────────────────────────────────┘
 *
 * 🖥️ EXEMPLO DE USO
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ <?php                                                                     │
 * │ return [                                                                  │
 * │     '/' => [                                                              │
 * │         '@main' => [                                                      │
 * │             ...                                                           │
 * │         ],                                                                │
 * │                                                                           │
 * │         '/area-do-usuario' => [                                           │
 * │             'authTag' => 'login-area-usuario',                            │
 * │                                                                           │
 * │             '@main' => [                                                  │
 * │                 ...                                                       │
 * │             ],                                                            │
 * │                                                                           │
 * │             '/api' => [                                                   │
 * │                 '@deletar' => [                                           │
 * │                     'permissionFailMessage' => 'Você não tem permissão.', │
 * │                 ],                                                        │
 * │                                                                           │
 * │                 '@main' => [                                              │
 * │                     ...                                                   │
 * │                 ],                                                        │
 * │             ],                                                            │
 * │         ],                                                                │
 * │                                                                           │
 * │         ...                                                               │
 * │     ],                                                                    │
 * │ ];                                                                        │
 * └───────────────────────────────────────────────────────────────────────────┘
 *
 * No exemplo acima, uma verificação feita com `Permission` pode impedir a
 * operação em `/area-do-usuario/api/deletar`. A mensagem "Você não tem
 * permissão." aparece se `displayErrors` estiver habilitado.
 *
 *
 *
 * 🔶 validateMimeType
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * - Tipo: `bool`
 * - Valor padrão: true
 *
 * Define se o framework deve validar o tipo MIME de um arquivo antes de
 * exibi-lo ou disponibilizá-lo para download. Essa verificação garante que o
 * tipo real do arquivo corresponda ao esperado para sua extensão, ajudando a
 * evitar a execução ou o download de arquivos maliciosos.
 *
 * Os tipos MIME permitidos podem ser configurados no arquivo
 * `app\config\mime-types.php`.
 *
 * 🖥️ EXEMPLO DE USO
 * ┌──────────────────────────────────────────┐
 * │ <?php                                    │
 * │                                          │
 * │ return [                                 │
 * │     '/' => [                             │
 * │         'output' => 'view',              │
 * │                                          │
 * │         '@main' => [                     │
 * │             ...                          │
 * │         ],                               │
 * │                                          │
 * │         '/images' => [                   │
 * │             'output' => 'file',          │
 * │             'validateMimeType' => false, │
 * │         ],                               │
 * │                                          │
 * │         ...                              │
 * │     ],                                   │
 * │ ];                                       │
 * └──────────────────────────────────────────┘
 *
 * No exemplo acima, caso `validateMimeType` esteja ativado (`true`), o
 * framework verificará se o tipo MIME do arquivo corresponde ao esperado para
 * sua extensão.
 *
 * Por exemplo, se a configuração de tipos MIME especificar que um arquivo
 * `.jpg` deve ter o tipo `image/jpg` ou `image/jpeg`, um arquivo `.exe`
 * renomeado para `.jpg` será automaticamente bloqueado.
 *
 *
 *
 * 🔶 forceMimeType
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * - Tipo: `array`
 * - Valor padrão: []
 *
 * Faz com que o framework ignore a auto detecção do tipo MIME de uma extensão,
 * aplicando um tipo MIME forçadamente.
 *
 * Para as extensões presentes nessa configuração, o tipo informado substitui a
 * detecção automática e a verificação de `validateMimeType`. As demais
 * extensões continuam seguindo a configuração de validação.
 *
 * 🖥️ EXEMPLO DE USO
 * ┌───────────────────────────────────────────┐
 * │ <?php                                     │
 * │                                           │
 * │ return [                                  │
 * │     '/' => [                              │
 * │         'output' => 'view',               │
 * │                                           │
 * │         '@main' => [                      │
 * │             ...                           │
 * │         ],                                │
 * │                                           │
 * │         '/images' => [                    │
 * │             'output' => 'file',           │
 * │             'forceMimeType' => [          │
 * │                 'js' => 'text/javascript' │
 * │             ],                            │
 * │         ],                                │
 * │                                           │
 * │         ...                               │
 * │     ],                                    │
 * │ ];                                        │
 * └───────────────────────────────────────────┘
 *
 * No exemplo acima, a configuração `forceMimeType` irá aplicar o tipo MIME
 * `text/javascript` para todos os arquivos com extensão `.js`, mesmo que a auto
 * detecção do PHP identifique o arquivo com outro tipo MIME.
 */





/**
 * 🟢 CONFIGURAÇÕES DO CONTEXTO node
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Este contexto abrange configurações que se aplicam exclusivamente a um `node`
 * específico.
 *
 * As configurações dentro desse contexto são válidas apenas para o `node` em
 * que foram definidas, ou seja, não permanecem ativas ao longo do caminho
 * percorrido. Cada `node` pode ter suas próprias definições, sem que isso afete
 * os demais nós da URL.
 *
 *
 *
 * 🔶 controller
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * - Tipo: `string`
 * - Valor padrão: ''
 *
 * Define um `controller` alternativo para a rota, substituindo o `controller`
 * padrão que seria atribuído automaticamente com base no nome da rota.
 *
 * 🖥️ EXEMPLO DE USO
 * ┌──────────────────────────────────────────────────┐
 * │ <?php                                            │
 * │                                                  │
 * │ return [                                         │
 * │     '/' => [                                     │
 * │         '@main' => [                             │
 * │             ...                                  │
 * │         ],                                       │
 * │                                                  │
 * │         '/empresa' => [                          │
 * │             'controller' => 'SobreNossaEmpresa', │
 * │                                                  │
 * │             '@main' => [                         │
 * │                 ...                              │
 * │             ],                                   │
 * │                                                  │
 * │             '/contato' => [                      │
 * │                 '@main' => [                     │
 * │                     ...                          │
 * │                 ],                               │
 * │             ],                                   │
 * │         ],                                       │
 * │                                                  │
 * │         ...                                      │
 * │     ],                                           │
 * │ ];                                               │
 * └──────────────────────────────────────────────────┘
 *
 * No exemplo acima, o `controller` padrão para a rota `www.dominio.com/empresa`
 * seria `Empresa`, mas foi alterado para `SobreNossaEmpresa`, permitindo
 * que o nome do arquivo da classe seja `SobreNossaEmpresa.php`.
 *
 * Porém, para a rota `www.dominio.com/empresa/contato`, o `controller` padrão
 * permanece como sendo o mesmo nome da parte `/contato`, ou seja, `Contato`.
 *
 * ⚠️ IMPORTANTE
 * ┌────────────────────────────────────────────────────────────────────────┐
 * │ Lembre-se de que a configuração `controller`, não altera o padrão do   │
 * │ namespace das classes posteriores. Ele altera apenas o nome da classe  │
 * │ `controller` (e consequentemente o nome do arquivo contendo a classe), │
 * │ mas as demais rotas permanecem conforme o padrão.                      │
 * └────────────────────────────────────────────────────────────────────────┘
 */



/**
 * 🟢 CONFIGURAÇÕES DO CONTEXTO endpoint
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Este contexto abrange configurações específicas aplicadas a um `endpoint`
 * determinado. Cada `endpoint` pode ter suas próprias definições, de forma
 * independente, sem afetar outros `endpoints` do projeto.
 *
 *
 *
 * 🔶 method
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * - Tipo: `string`
 * - Valor padrão: `''`
 *
 * Define um método alternativo para o `endpoint`, substituindo o método padrão
 * que seria atribuído automaticamente com base no nome da rota.
 *
 * Ao configurar esta opção, você pode especificar um método diferente para ser
 * executado no `endpoint`, caso o nome do método padrão (gerado automaticamente
 * a partir da rota) não seja adequado ou desejado.
 *
 * 🖥️ EXEMPLO DE USO
 * ┌────────────────────────────────────────────────┐
 * │ <?php                                          │
 * │                                                │
 * │ return [                                       │
 * │     '/' => [                                   │
 * │         'output' => 'view',                    │
 * │                                                │
 * │         '@main' => [                           │
 * │             ...                                │
 * │         ],                                     │
 * │                                                │
 * │         '/produtos' => [                       │
 * │             '@main' => [                       │
 * │                 ...                            │
 * │             ],                                 │
 * │                                                │
 * │             '@listar' => [                     │
 * │                 'method' => '@exibirProdutos', │
 * │             ],                                 │
 * │         ],                                     │
 * │                                                │
 * │         ...                                    │
 * │     ],                                         │
 * │ ];                                             │
 * └────────────────────────────────────────────────┘
 *
 * No exemplo acima, o método padrão para a rota
 * `www.dominio.com/produtos/listar` seria `listar()` , mas foi alterado para
 * `exibirProdutos()` , conforme configurado no parâmetro `method`.
 *
 * Assim, ao acessar a rota, o framework executará o método `exibirProdutos()`
 * no lugar do `listar()` .
 *
 *
 *
 * 🔶 parameters
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * - Tipo: `string`
 * - Valor padrão: `''`
 *
 * Especifica os parâmetros de uma rota, que podem ser de dois tipos:
 *
 * 1. Parâmetro obrigatório `/`
 *    Indica um parâmetro que deve obrigatoriamente estar presente na URL. Caso
 *    contrário, a rota será considerada inválida pelo framework.
 *
 *    - Como é definido na configuração: `/parametro`
 *
 * 2. Parâmetro opcional `/?`
 *    Indica um parâmetro opcional, que pode ser omitido na URL sem invalidar a
 *    rota.
 *
 *    - Como é definido na configuração: `/?parametro`
 *
 * ✔️ EXEMPLO DE USO
 * ┌──────────────────────────────────────────────────────────────────────────┐
 * │ Dada a URL a seguir: `www.dominio.com/produtos/listar/todos/decrescente` │
 * │ e a seguinte configuração de rotas:                                      │
 * │                                                                          │
 * │ CÓDIGO                                                                   │
 * │ ┌────────────────────────────────────────────────┐                       │
 * │ │ <?php                                          │                       │
 * │ │                                                │                       │
 * │ │ return [                                       │                       │
 * │ │     '/' => [                                   │                       │
 * │ │         'projectName' => 'Galastri Framework', │                       │
 * │ │         'output' => 'view',                    │                       │
 * │ │                                                │                       │
 * │ │         '@main' => [                           │                       │
 * │ │             ...                                │                       │
 * │ │         ],                                     │                       │
 * │ │                                                │                       │
 * │ │         '/produtos' => [                       │                       │
 * │ │             '@main' => [                       │                       │
 * │ │                 ...                            │                       │
 * │ │             ],                                 │                       │
 * │ │                                                │                       │
 * │ │             '@listar' => [                     │                       │
 * │ │                 'parameters' => '/tipo/?ordem' │                       │
 * │ │             ],                                 │                       │
 * │ │         ],                                     │                       │
 * │ │     ],                                         │                       │
 * │ │ ];                                             │                       │
 * │ └────────────────────────────────────────────────┘                       │
 * └──────────────────────────────────────────────────────────────────────────┘
 *
 * - Na URL de exemplo, `todos` e `decrescente` são parâmetros. O primeiro é
 *   armazenado em `tipo`, enquanto o segundo é armazenado em `ordem`.
 *
 * - Se a URL não incluir `decrescente`, a rota continuará válida, pois `ordem`
 *   é um parâmetro opcional.
 * 
 * - Se não houver valor para o parâmetro obrigatório `tipo`, a rota será
 *   invalidada.
 *
 * Ou seja:
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
 *
 *
 *
 * 🔶 httpMethod
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * - Tipo: `array`
 * - Valor padrão: []
 *
 * Define o comportamento do `endpoint` com base no tipo de requisição HTTP
 * (como `GET`, `POST`, etc.), especificando o método da classe controller que
 * será executado para cada tipo de requisição.
 *
 * A configuração é definida no formato `tipo => @método`, onde:
 *
 * - `tipo`: O tipo da requisição (`GET`, `POST`, `PUT`, etc.)
 * - `@método`: O nome do método da classe controller que será executado para
 *   aquela requisição.
 *
 * 🖥️ EXEMPLO DE USO
 * ┌─────────────────────────────────────────────┐
 * │ <?php                                       │
 * │                                             │
 * │ return [                                    │
 * │     '/' => [                                │
 * │         'output' => 'view',                 │
 * │                                             │
 * │         '@main' => [                        │
 * │             ...                             │
 * │         ],                                  │
 * │                                             │
 * │         '/produtos' => [                    │
 * │             '@main' => [                    │
 * │                 ...                         │
 * │             ],                              │
 * │                                             │
 * │             '@cadastro' => [                │
 * │                 'httpMethod' => [           │
 * │                     'GET' => '@listar',     │
 * │                     'POST' => '@adicionar', │
 * │                     'DELETE' => '@remover', │
 * │                 ],                          │
 * │             ],                              │
 * │         ],                                  │
 * │                                             │
 * │         ...                                 │
 * │     ],                                      │
 * │ ];                                          │
 * └─────────────────────────────────────────────┘
 *
 * No exemplo acima, o método `cadastro()` continua obrigatório e é executado
 * primeiro. Depois, o tipo da requisição determina a chamada adicional:
 *
 * - GET: executa `cadastro()` e, depois, `listar()`.
 * - POST: executa `cadastro()` e, depois, `adicionar()`.
 * - DELETE: executa `cadastro()` e, depois, `remover()`.
 *
 * O retorno de `cadastro()` não é passado como argumento para o método
 * seguinte. A classe base `Controller` reúne os arrays retornados depois
 * das chamadas.
 *
 * Se não houver uma associação para o tipo de requisição recebido,
 * apenas `cadastro()` será executado.
 */



/**
 * 🟢 CONFIGURAÇÕES DO CONTEXTO debug
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Trata-se de uma extensão do contexto `route`, pois seu funcionamento é
 * idêntico. Porém, conceitualmente, é um contexto separado, já que trata de
 * configurações de depuração, não de rota e processamento de requisições.
 *
 *
 *
 * 🔶 displayErrors
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * - Tipo: `bool`
 * - Valor padrão: false
 *
 * Define se mensagens de erros serão mostradas integralmente quando `true`
 * ou se apenas uma mensagem genérica será mostrada.
 *
 * 🖥️ EXEMPLO DE USO
 * ┌──────────────────────────────────┐
 * │ <?php                            │
 * │                                  │
 * │ return [                         │
 * │     '/' => [                     │
 * │         'displayErrors' => true, │
 * │         'output' => 'view',      │
 * │                                  │
 * │         '@main' => [             │
 * │             ...                  │
 * │         ],                       │
 * │                                  │
 * │         ...                      │
 * │     ],                           │
 * │ ];                               │
 * └──────────────────────────────────┘
 *
 * No exemplo acima, caso haja algum erro na programação PHP ou qualquer outro
 * tipo de erro no projeto, as mensagens de erros serão mostradas de forma
 * detalhada.
 *
 * Isso é excelente para desenvolvimento, mas para produção pode ser um grande
 * problema de segurança, já que dados sensíveis podem ser exibidos.
 *
 * Uma característica importante é que o parâmetro pode ser configurado por
 * rota, ou seja, é possível habilitar ou desabilitar a apresentação de erros
 * apenas para determinadas rotas.
 *
 * 🖥️ EXEMPLO DE USO
 * ┌──────────────────────────────────────┐
 * │ <?php                                │
 * │                                      │
 * │ return [                             │
 * │     '/' => [                         │
 * │         'output' => 'view',          │
 * │                                      │
 * │         '@main' => [                 │
 * │             ...                      │
 * │         ],                           │
 * │                                      │
 * │         '@listar' => [               │
 * │             'displayErrors' => true, │
 * │                                      │
 * │             ...                      │
 * │         ],                           │
 * │                                      │
 * │         ...                          │
 * │     ],                               │
 * │ ];                                   │
 * └──────────────────────────────────────┘
 *
 * Supondo que a URL `www.dominio.com/listar` esteja com algum erro, o
 * exemplo acima permite a exibição das mensagens de erro apenas para esta rota,
 * mantendo as demais com o comportamento padrão.
 *
 *
 *
 * 🔶 stopOnNonFatalErrors
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * - Tipo: `bool`
 * - Valor padrão: true
 *
 * Define se falhas não fatais, como avisos, notificações e mensagens de
 * depreciação, devem interromper a execução. Quando está em `false`,
 * registramos o log, se habilitado, e continuamos sem apresentar a falha nem
 * alterar o status HTTP.
 *
 * 🖥️ EXEMPLO DE USO
 * ┌─────────────────────────────────────────┐
 * │ <?php                                   │
 * │                                         │
 * │ return [                                │
 * │     '/' => [                            │
 * │         'stopOnNonFatalErrors' => true, │
 * │         'output' => 'view',             │
 * │                                         │
 * │         '@main' => [                    │
 * │             ...                         │
 * │         ],                              │
 * │                                         │
 * │         ...                             │
 * │     ],                                  │
 * │ ];                                      │
 * └─────────────────────────────────────────┘
 *
 * No exemplo acima, avisos, notificações e mensagens de depreciação também
 * interrompem a execução, pois `stopOnNonFatalErrors` está em `true`.
 *
 * Uma característica importante é que o parâmetro pode ser configurado por
 * rota, ou seja, é possível habilitar ou desabilitar o comportamento citado
 * apenas para determinadas rotas.
 *
 * 🖥️ EXEMPLO DE USO
 * ┌──────────────────────────────────────────────┐
 * │ <?php                                        │
 * │                                              │
 * │ return [                                     │
 * │     '/' => [                                 │
 * │         'output' => 'view',                  │
 * │                                              │
 * │         '@main' => [                         │
 * │             ...                              │
 * │         ],                                   │
 * │                                              │
 * │         '@listar' => [                       │
 * │             'stopOnNonFatalErrors' => false, │
 * │                                              │
 * │             ...                              │
 * │         ],                                   │
 * │                                              │
 * │         ...                                  │
 * │     ],                                       │
 * │ ];                                           │
 * └──────────────────────────────────────────────┘
 *
 * Supondo que a URL `www.dominio.com/listar` esteja com algum erro de nível
 * *warning*, o exemplo acima não irá parar a execução do código, mas apenas
 * para esta rota. Para as demais, o comportamento será o padrão do framework,
 * ou seja, irá parar a execução.
 *
 *
 *
 * 🔶 showTrace
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * - Tipo: `bool`
 * - Valor padrão: false
 *
 * Autoriza a apresentação do histórico de chamadas da falha. Esse histórico só
 * aparece quando `showTrace` e `displayErrors` estão em `true`.
 *
 * 🖥️ EXEMPLO DE USO
 * ┌──────────────────────────────────┐
 * │ <?php                            │
 * │                                  │
 * │ return [                         │
 * │     '/' => [                     │
 * │         'displayErrors' => true, │
 * │         'showTrace' => true,     │
 * │         'output' => 'view',      │
 * │                                  │
 * │         '@main' => [             │
 * │             ...                  │
 * │         ],                       │
 * │                                  │
 * │         ...                      │
 * │     ],                           │
 * │ ];                               │
 * └──────────────────────────────────┘
 *
 * No exemplo acima, caso haja algum erro na programação PHP ou qualquer outro
 * tipo de erro no projeto, o histórico de chamadas será mostrado junto com a
 * mensagem de erro.
 *
 * Uma característica importante é que o parâmetro pode ser configurado por
 * rota, ou seja, é possível habilitar ou desabilitar o comportamento citado
 * apenas para determinadas rotas.
 *
 * 🖥️ EXEMPLO DE USO
 * ┌──────────────────────────────────────┐
 * │ <?php                                │
 * │                                      │
 * │ return [                             │
 * │     '/' => [                         │
 * │         'output' => 'view',          │
 * │                                      │
 * │         '@main' => [                 │
 * │             ...                      │
 * │         ],                           │
 * │                                      │
 * │         '@listar' => [               │
 * │             'displayErrors' => true, │
 * │             'showTrace' => true,     │
 * │                                      │
 * │             ...                      │
 * │         ],                           │
 * │                                      │
 * │         ...                          │
 * │     ],                               │
 * │ ];                                   │
 * └──────────────────────────────────────┘
 *
 * Supondo que a URL **`www.dominio.com/listar`** esteja com algum erro, o
 * exemplo acima irá apresentar também o histórico de chamadas junto com a
 * mensagem de erro.
 *
 *
 *
 * 🔶 createLogsOnError
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * - Tipo: `bool`
 * - Valor padrão: true
 *
 * Habilita ou desabilita a geração de logs de erros na pasta /logs.
 *
 * 🖥️ EXEMPLO DE USO
 * ┌──────────────────────────────────────┐
 * │ <?php                                │
 * │                                      │
 * │ return [                             │
 * │     '/' => [                         │
 * │         'createLogsOnError' => true, │
 * │         'output' => 'view',          │
 * │                                      │
 * │         '@main' => [                 │
 * │             ....                     │
 * │         ],                           │
 * │                                      │
 * │         ...                          │
 * │     ],                               │
 * │ ];                                   │
 * └──────────────────────────────────────┘
 *
 * No exemplo acima, caso haja algum erro na programação PHP ou qualquer outro
 * tipo de erro no projeto, um log contendo as informações do erro será salvo na
 * pasta  **`/logs`**.
 *
 * Uma característica importante é que o parâmetro pode ser configurado por
 * rota, ou seja, é possível habilitar ou desabilitar o comportamento citado
 * apenas para determinadas rotas.
 *
 * 🖥️ EXEMPLO DE USO
 * ┌──────────────────────────────────────────┐
 * │ <?php                                    │
 * │                                          │
 * │ return [                                 │
 * │     '/' => [                             │
 * │         'output' => 'view',              │
 * │         'createLogsOnError' => false,    │
 * │         '@main' => [                     │
 * │             ...                          │
 * │         ],                               │
 * │                                          │
 * │         '@listar' => [                   │
 * │             'createLogsOnError' => true, │
 * │                                          │
 * │             ...                          │
 * │         ],                               │
 * │                                          │
 * │         ...                              │
 * │     ],                                   │
 * │ ];                                       │
 * └──────────────────────────────────────────┘
 *
 * Supondo que a URL **`www.dominio.com/listar`** esteja com algum erro, o
 * exemplo acima irá salvar um arquivo de log apenas para erros desta rota.
 */



/**
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 *
 * ▶️ PRÓXIMO TÓPICO:
 * 🔗 LINK: /docs/fluxo-de-dados.md
 */
