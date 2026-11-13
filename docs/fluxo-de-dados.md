```php
/**
 * -----------------------------------------------------------------------------
 * 📂
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                              FLUXO DE DADOS                               ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 *
 * Lá em `Galastri`, vimos a requisição chegar a `executeOutput()`. A rota já
 * tem um formato de saída definido, e é nessa chamada que a classe escolhida
 * assume o trabalho de preparar e enviar a resposta.
 *
 * Se a rota passou por um controller, a saída pode consultar o resultado que
 * ele preparou. O que acontece a partir daí depende da configuração `output`.
 * `View` monta uma página HTML, `Json` envia os dados em JSON e `File` entrega
 * um arquivo.
 *
 * Vamos retomar o caminho dos dados até esse ponto e ver como cada uma dessas
 * saídas prepara o que será entregue a quem fez a requisição.
 */



/**
 * 🟢 ORGANIZAÇÃO
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Vamos rever todo o fluxo de dados, desde o momento em que a requisição chega
 * até o envio da resposta final.
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
 *    -  O `Router` interpreta as partes da URL conforme a configuração da rota
 *       e guarda os valores dos nós dinâmicos e dos parâmetros. Os dados
 *       enviados via POST podem ser consultados pelo módulo `HttpData`.
 *
 *       O processo completo de configuração e organização das rotas é detalhado
 *       no arquivo da classe Router. Se ainda não viu sobre ele, acesse o link
 *       a seguir e depois volte aqui.
 *
 *       🔗 LINK: /galastri/core/Router.php
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
 *    Depois, identificamos o controller e conferimos o método que será
 *    chamado antes de começar a execução da aplicação.
 *
 * 4️⃣ EXECUÇÃO DO CONTROLLER
 *    ──────────────────────────────────────────────────────────────────────────
 *    O controller executa o método associado à rota (ou o método configurado
 *    especificamente para o verbo HTTP recebido, em `onHttpMethod`).
 *
 *    A programação da aplicação ocorre nesse método, que pode consultar bancos
 *    de dados, executar regras de negócio e preparar a resposta. Os dados
 *    processados devem ser retornados em um `array`, que será entregue à classe
 *    responsável pela saída.
 *
 * 5️⃣ RETORNO DOS DADOS
 *    ──────────────────────────────────────────────────────────────────────────
 *    O fluxo é finalizado com a execução da classe responsável pelo formato de
 *    saída.
 *
 *    Atualmente os formatos são `View`, `Json` e `File`.
 */



/**
 * 🟢 SAÍDA VIEW
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * A saída `View` monta uma página HTML. Durante a montagem, o template e a
 * view podem consultar os dados que vieram do controller pelo objeto `$g`.
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
 * Esse é o comportamento padrão. Se quiser mudar a pasta onde a saída procura a
 * view, use a configuração de rota `viewFolder`. Se quiser indicar qual arquivo
 * usar para um endpoint, use `viewFile`.
 *
 *
 *
 * 🔶 ARQUIVOS DE TEMPLATE
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Um arquivo de template pode ser usado como estrutura básica do layout, onde a
 * view da rota é incluída.
 *
 * Quando a rota usa um template, `View` executa esse arquivo primeiro.
 * Dentro dele, podemos chamar `$g->include('view')` pra encaixar a view
 * no ponto do layout em que queremos mostrar o conteúdo da página.
 *
 * Se a configuração de rota `templateFile` estiver vazia, `View` executa o
 * arquivo da view diretamente.
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
 * A saída `Json` pega o array que o controller preparou, converte os dados em
 * JSON e envia o resultado pra quem fez a requisição. Numa resposta comum, o
 * cabeçalho `Content-Type` indica `application/json`.
 *
 * Se a aplicação chamar `ResponseStream::send()`, a resposta passa a ser
 * enviada em NDJSON. Cada envio ocupa uma linha com seu próprio JSON. Quando a
 * execução chega a `Json::run()`, os dados reunidos pelo controller são
 * enviados como a última linha.
 *
 * ⚠️ IMPORTANTE
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ `Json` é um formato de saída que exige um controller.                     │
 * └───────────────────────────────────────────────────────────────────────────┘
 */



/**
 * 🟢 SAÍDA FILE
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * A saída `File` entrega um arquivo para exibição ou download. Ela pode buscar
 * esse arquivo no disco ou usar o conteúdo que o controller preparou durante a
 * requisição.
 *
 * ✔️ IMPORTANTE
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ `File` é um formato de saída que __NÃO__ exige um controller.             │
 * └───────────────────────────────────────────────────────────────────────────┘
 *
 * A saída possui dois comportamentos possíveis:
 *
 * -  Usa o arquivo preparado pelo controller com `setFileContents()`.
 *
 * -  Busca o arquivo no disco quando não há conteúdo preparado pelo controller,
 *    mesmo que a requisição tenha passado por um.
 *
 *
 *
 * 🔶 BUSCA DIRETA
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * A saída `File` usa as partes que sobraram da URL depois que o `Router`
 * processou a rota. Essas partes formam o caminho do arquivo dentro da pasta
 * indicada em `fileFolder`.
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
 * 1. A rota `www.dominio.com/imagens` é definida como sendo uma rota que irá
 *    retornar um arquivo.
 *
 * 2. Os arquivos estão armazenados em `/app/assets/imagens`.
 *
 * 3. Se não existir a classe `app\controllers\Imagens`, o processamento
 *    continuará sem controller, pois a saída `file` permite isso.
 *
 * 4. O restante da URL `/icones/empresa.jpg` será o caminho buscado em
 *    `/app/assets/imagens`.
 *
 * 5. Se o arquivo for encontrado e passar pelas verificações, ele será enviado
 *    para exibição. Se não for encontrado, o usuário será redirecionado para
 *    `www.dominio.com/erro`.
 *
 *
 *
 * 🔶 PASSAGEM PELO CONTROLLER
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Quando a rota passa por um controller, ele roda antes da saída `File`. É
 * nesse momento que a aplicação pode conferir se o usuário tem acesso ao
 * arquivo ou usar os parâmetros da rota pra decidir o que entregar.
 *
 * O controller também pode gerar um arquivo na hora, como uma imagem, e passar
 * seu conteúdo, nome e tipo MIME com `setFileContents()`. Se ele deixar a busca
 * por conta da saída, `File` procura no disco.
 *
 *
 *
 * 🔶 ORGANIZAÇÃO DO FLUXO DE DADOS
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 *
 * Na busca em disco, `File` confere a extensão e o tipo MIME de acordo com as
 * configurações da rota. Quando o controller já preparou o arquivo, a saída usa
 * os dados que ele forneceu.
 *
 * Com o arquivo pronto, `File` consulta `isDownloadableFile`. Se a opção
 * estiver ativa, envia o arquivo como anexo para download. Caso contrário,
 * envia o conteúdo com seu tipo MIME para que o navegador possa exibi-lo.
 */



/**
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * ▶️ PRÓXIMO TÓPICO:
 * 🔗 LINK: /galastri/core/output/View.php
 */
