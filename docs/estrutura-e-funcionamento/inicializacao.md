```md
GALASTRI FRAMEWORK > ESTRUTURA E FUNCIONAMENTO
--------------------------------------------------------------------------------
╔══════════════════════════════════════════════════════════════════════════════╗
#                                 INICIALIZAÇÃO                                ║
╚══════════════════════════════════════════════════════════════════════════════╝

Sempre que há uma requisição nova, antes de qualquer processamento de dados no
controller, o framework precisa fazer uma série de preparações. Esse processo
começa em `public/index.php` e se repete a cada requisição encaminhada ao
framework.

A sequência abaixo apresenta as principais etapas, desde a entrada da requisição
até a preparação da resposta:

┌────────────────────────────────────┬─────────────────────────────────────────┐
│ _ETAPA_                            │ _RESPONSABILIDADE_                      │
├────────────────────────────────────┼─────────────────────────────────────────┤
│ `public/index.php`                 │ Inicia a execução e carrega o           │
│                                    │ bootstrap.                              │
├────────────────────────────────────┼─────────────────────────────────────────┤
│ `galastri/bootstrap/bootstrap.php` │ Prepara os recursos básicos e ativa o   │
│                                    │ gerenciador de erros.                   │
├────────────────────────────────────┼─────────────────────────────────────────┤
│ `Galastri::run()`                  │ Coordena o processamento da requisição. │
├────────────────────────────────────┼─────────────────────────────────────────┤
│ `Config::run()`                    │ Carrega as definições de configuração.  │
├────────────────────────────────────┼─────────────────────────────────────────┤
│ `Router::run()`                    │ Identifica a rota e aplica suas         │
│                                    │ configurações.                          │
├────────────────────────────────────┼─────────────────────────────────────────┤
│ `Controller`                       │ Executa as operações da aplicação e     │
│                                    │ reúne os dados da resposta.             │
├────────────────────────────────────┼─────────────────────────────────────────┤
│ `Classe de saída`                  │ Prepara e envia o resultado no formato  │
│                                    │ configurado.                            │
└────────────────────────────────────┴─────────────────────────────────────────┘

Essa é a sequência de uma requisição atendida normalmente por um controller.
Redirecionamentos, erros e algumas respostas de arquivos podem seguir caminhos
diferentes, conforme explicado adiante.



--------------------------------------------------------------------------------
╔══════════════════════════════════════════════════════════════════════════════╗
#                             ENTRADA DA REQUISIÇÃO                            ║
╚══════════════════════════════════════════════════════════════════════════════╝

O arquivo `public/index.php` é o ponto de entrada do framework. As regras do
servidor encaminham as requisições para esse arquivo, permitindo que o framework
identifique a rota e execute o código correspondente.

✔️
┌──────────────────────────────────────────────────────────────────────────────┐
│ _EXEMPLO_                                                                    │
│                                                                              │
│ Considere uma requisição para a seguinte URL                                 │
│ - https://galastri.test/clientes/listar                                      │
│                                                                              │
│ Internamente, o servidor encaminha essa requisição para `public/index.php`.  │
│ O endereço continua sendo `/clientes/listar` no navegador, e o framework     │
│ utiliza esse caminho para identificar a rota.                                │
│                                                                              │
│ Esse encaminhamento interno é diferente de um redirecionamento HTTP, no qual │
│ o navegador recebe uma instrução para acessar outro endereço.                │
└──────────────────────────────────────────────────────────────────────────────┘
\



#                          ENCAMINHAMENTO PELO APACHE                          <
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Na instalação com Apache, as regras dentro de `public/.htaccess` faz com que o
servidor tenha um comportamento diferente do padrão.

- A regra principal encaminha para `index.php` todas as requisições que não
correspondem a um diretório existente

- URLs com barra no final são redirecionadas para a versão sem essa barra,
exceto quando correspondem a diretórios existentes.

- Requisições para `/favicon.ico` são atendidas diretamente pelo Apache, sem
iniciar o framework. Se o arquivo não existir na pasta pública, o Apache retorna
uma resposta 404.

Por isso, nem toda requisição recebida pelo servidor executa o código do
Galastri Framework.



--------------------------------------------------------------------------------
╔══════════════════════════════════════════════════════════════════════════════╗
#                           Arquivo public/index.php                           ║
╚══════════════════════════════════════════════════════════════════════════════╝

O arquivo de entrada contém o seguinte:

💻
┌──────────────────────────────────────────────────────────┐
│ <?php                                                    │
│ ini_set('display_errors', true);                         │
│ error_reporting(E_ALL);                                  │
│                                                          │
│ require(__DIR__.'/../galastri/bootstrap/bootstrap.php'); │
└──────────────────────────────────────────────────────────┘
``


#                        CONFIGURAÇÃO INICIAL DOS ERROS                        <
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
As duas primeiras instruções possuem as seguintes funções:

┌────────────────────────────────────┬─────────────────────────────────────────┐
│ _INSTRUÇÃO_                        │ _FUNÇÃO_                                │
├────────────────────────────────────┼─────────────────────────────────────────┤
│ `ini_set('display_errors', true);` │ Ativa a exibição de erros pelo PHP      │
│                                    │ durante a execução.                     │
├────────────────────────────────────┼─────────────────────────────────────────┤
│ `error_reporting(E_ALL)`           │ Inclui todos os níveis de erro no       │
│                                    │ conjunto de erros reportados pelo PHP.  │
└────────────────────────────────────┴─────────────────────────────────────────┘

ℹ️
┌──────────────────────────────────────────────────────────────────────────────┐
│ Essas configurações são aplicadas durante a requisição e não alteram         │
│ permanentemente o arquivo `php.ini`.                                         │
└──────────────────────────────────────────────────────────────────────────────┘
>


#                           CARREGAMENTO DO BOOTSTRAP                          <
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
A última instrução incorpora o arquivo:

💻
┌──────────────────────────────────────────────────────────┐
│ require(__DIR__.'/../galastri/bootstrap/bootstrap.php'); │
└──────────────────────────────────────────────────────────┘
``

O uso de `__DIR__` constrói o caminho a partir da pasta onde está o `index.php`.
Nesse caso, `..` sobe da pasta `public` para a raiz do projeto, permitindo
localizar a pasta `galastri`.

O `require` executa o conteúdo do bootstrap. Se o arquivo não puder ser
carregado, a inicialização é interrompida.



--------------------------------------------------------------------------------
╔══════════════════════════════════════════════════════════════════════════════╗
#                                   BOOTSTRAP                                  ║
╚══════════════════════════════════════════════════════════════════════════════╝

O bootstrap é a etapa que prepara os recursos básicos para a execução do
framework. É nesse momento que ficam disponíveis as constantes, as funções
auxiliares, o carregamento das classes e o gerenciador de erros.

Atualmente essa preparação é organizada em três arquivos dentro da pasta
`galastri/bootstrap`:

┌─────────────────┬────────────────────────────────────────────────────────────┐
│ _ARQUIVO_       │ _RESPONSABILIDADE_                                         │
├─────────────────┼────────────────────────────────────────────────────────────┤
│ `bootstrap.php` │ Coordena a preparação e inicia o processamento da          │
│                 │ requisição.                                                │
├─────────────────┼────────────────────────────────────────────────────────────┤
│ `constants.php` │ Define caminhos, informações de versão e valores           │
│                 │ utilizados pelo código.                                    │
├─────────────────┼────────────────────────────────────────────────────────────┤
│ `functions.php` │ Disponibiliza funções auxiliares.                          │
└─────────────────┴────────────────────────────────────────────────────────────┘

Além desses arquivos, a aplicação pode possuir seu próprio `app/bootstrap.php`,
executado depois da preparação básica e antes do processamento das configurações
e das rotas.


#                   ARQUIVO galastri/bootstrap/bootstrap.php                   <
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
O arquivo é carregado por `public/index.php`. Suas instruções são executadas em
sequência, preparando os recursos necessários para as etapas seguintes.

A ordem da execução é a seguinte

1. Ativa o buffer de saída do PHP.

   💻
  ┌─────────────┐
  │ ob_start(); │
  └─────────────┘   
   ``

   Ela ativa um buffer de saída, ou seja, um armazenamento temporário para o
   conteúdo produzido pelo PHP.

   Por exemplo, um conteúdo gerado por `echo` pode permanecer nesse buffer antes
   de ser enviado ao navegador. Isso permite que o código manipule a saída
   durante o processamento.

   O buffer, entretanto, não modifica o conteúdo por conta própria. Sua ativação
   também não significa que uma saída anterior será descartada automaticamente
   quando ocorrer um erro.

   ℹ️
   ┌───────────────────────────────────────────────────────────────────────────┐
   │ No código atual, o `ErrorHandler` não realiza uma limpeza geral do buffer │
   │ antes de apresentar um erro. Assim, um conteúdo impresso anteriormente    │
   │ pode aparecer junto da mensagem de erro.                                  │
   └───────────────────────────────────────────────────────────────────────────┘
   >
   
2. Carrega as constantes e as funções auxiliares.

   💻
   ┌───────────────────────────┐
   │ require('constants.php'); │
   │ require('functions.php'); │
   └───────────────────────────┘
   ``

   O primeiro arquivo define as constantes utilizadas pelo framework.
   O segundo declara e disponibiliza as funções auxiliares.

3. Carrega o autoload do Composer.

   💻
   ┌──────────────────────────────────────────────┐
   │ require(PROJECT_DIR.'/vendor/autoload.php'); │
   └──────────────────────────────────────────────┘
   ``

   A partir dessa instrução, o PHP passa a contar com o mecanismo que localiza
   os arquivos das classes conforme os namespaces configurados no projeto.

   ✔️
   ┌───────────────────────────────────────────────────────────────────────────┐
   │ _EXEMPLO_                                                                 │
   │                                                                           │
   │ A classe `galastri\core\Galastri` fica localizada em                      │
   │ `galastri/core/Galastri.php`                                              │
   │                                                                           │
   │ O autoload basicamente procura o local do arquivo com base no caminho     │
   │ informado através do namepace.                                            │
   │                                                                           │
   │ ┌─────────────────────────────────────────────────────────────────────┐   │
   │ │ Uma observação importante a respeito de performance:                │   │
   │ │                                                                     │   │
   │ │ O autoload não carrega todas as classes de uma vez. Ele carrega     │   │
   │ │ apenas uma classe quando ela é necessária. Portanto, não se trata d │   │
   │ │ um recurso problemático no sentido de performance.                  │   │
   │ └─────────────────────────────────────────────────────────────────────┘   │
   │ >                                                                         │
   └───────────────────────────────────────────────────────────────────────────┘
   \
   
4. Disponibiliza o alias \VarDump para facilitar o uso da classe de depuração:

   💻
   ┌────────────────────────────────────────────────────────────┐
   │ class_alias(\galastri\modules\VarDump::class, '\VarDump'); │
   └────────────────────────────────────────────────────────────┘
   ``

   Com isso, a classe pode ser referenciada por `\VarDump`, sem repetir seu
   namespace completo.

   Esse alias também é utilizado pela função auxiliar `vardump()`, descrita
   adiante.
   
5. Ativa o gerenciador de erros:

   💻
   ┌──────────────────────┐
   │ ErrorHandler::run(); │
   └──────────────────────┘
   ``
   
   A classe `ErrorHandler` do framework é a responsável pelo tratamento de erros
   e exceções. Essa chamada executa o método `run()` de e registra os mecanismos
   responsáveis por:

   * Tratar os erros do PHP abrangidos pelo manipulador de erros.
   
   * Tratar exceções que não foram capturadas pela aplicação.
   
   * Verificar determinados erros fatais no encerramento da execução.

   ⚠️
   ┌───────────────────────────────────────────────────────────────────────────┐
   │ Erros ocorridos antes da ativação do `ErrorHandler`, como uma falha ao    │
   │ carregar o próprio bootstrap, ainda aparecerem no formato padrão do PHP.  │
   └───────────────────────────────────────────────────────────────────────────┘
   >
   
6. Executa `app/bootstrap.php`, o arquivo de inicialização próprio da aplicação,
   caso exista:

   💻
   ┌──────────────────────────────────────────────────────┐
   │ if (file_exists(PROJECT_DIR.'/app/bootstrap.php')) { │
   │     require(PROJECT_DIR.'/app/bootstrap.php');       │
   │ }                                                    │
   └──────────────────────────────────────────────────────┘
   ``
   
7. Chama `Galastri::run()`.

   💻
   ┌──────────────────┐
   │ Galastri::run(); │
   └──────────────────┘
   ``
   
   Essa chamada inicia o processamento da requisição.


#                   ARQUIVO galastri/bootstrap/constants.php                   <
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Este arquivo define valores que podem ser utilizados em diferentes partes
do framework e da aplicação.


*  Caminho do projeto

   ┌───────────────┬───────────────────────────────────────────────────────────┐
   │ _CONSTANTE_   │ _CONTEÚDO_                                                │
   ├───────────────┼───────────────────────────────────────────────────────────┤
   │ `PROJECT_DIR` │ Caminho absoluto da pasta raiz do projeto no servidor.    │
   └───────────────┴───────────────────────────────────────────────────────────┘

   No ambiente de instalação apresentado nesta documentação, o valor será:

   💻
   ┌──────────────────────────────────────────┐
   │ /home/desenvolvedor/galastri-framework-2 │
   └──────────────────────────────────────────┘
   >
   
   Esse caminho é calculado a partir da localização de `constants.php`, sem
   depender do endereço utilizado no navegador.

   A constante permite construir caminhos para outros arquivos do projeto.
   Por exemplo:

   💻
   ┌──────────────────────────────────────┐
   │ PROJECT_DIR.'/app/config/routes.php' │
   └──────────────────────────────────────┘
   >
   
*  Versões

   ┌────────────────────┬──────────────────────────────────────────────────────┐
   │ _CONSTANTE_        │ _CONTEÚDO_                                           │
   ├────────────────────┼──────────────────────────────────────────────────────┤
   │ `GALASTRI_VERSION` │ Conteúdo do arquivo`galastri/VERSION`.               │
   ├────────────────────┼──────────────────────────────────────────────────────┤
   │ `APP_VERSION`      │ Conteúdo do arquivo`app/VERSION`.                    │
   └────────────────────┴──────────────────────────────────────────────────────┘

   Se um dos arquivos não existir, a constante correspondente recebe o texto:

   🧾
   ┌───────────────────────────────────────────────────────────────────────────┐
   │ Version file not found                                                    │
   └───────────────────────────────────────────────────────────────────────────┘
   @
   
   Esses valores ficam disponíveis para consulta pelo código e podem integrar
   os metadados da resposta.

*  Tipos MIME

   ┌──────────────────┬────────────────────────────────────────────────────────┐
   │ _CONSTANTE_      │ _CONTEÚDO_                                             │
   ├──────────────────┼────────────────────────────────────────────────────────┤
   │ `MIME_TYPE_LIST` │ Dados retornados por`app/config/mime-types.php`.       │
   └──────────────────┴────────────────────────────────────────────────────────┘

   O arquivo é carregado durante a definição da constante. Se ele não
   existir, o valor utilizado será um array vazio.

   Essa lista participa do tratamento dos tipos de arquivo pelo framework.

*  Identificador para análise de desempenho

   ┌────────────────────────────┬──────────────────────────────────────────────┐
   │ _CONSTANTE_                │ _CONTEÚDO_                                   │
   ├────────────────────────────┼──────────────────────────────────────────────┤
   │ `PERFORMANCE_ANALYSIS_TAG` │ Identificador gerado com 10 bytes            │
   │                            │ aleatórios, convertidos em uma string        │
   │                            │ hexadecimal de 20 caracteres.                │
   └────────────────────────────┴──────────────────────────────────────────────┘

   Atualmente, a constante está definida, mas o módulo de análise de
   desempenho ainda não foi desenvolvido.

*  Controle da referência de chamada

   ┌─────────────────────────┬─────────────────────────────────────────────────┐
   │ _CONSTANTE_             │ _CONTEÚDO_                                      │
   ├─────────────────────────┼─────────────────────────────────────────────────┤
   │ `RESET_BACKTRACE_LEVEL` │ true                                            │
   ├─────────────────────────┼─────────────────────────────────────────────────┤
   │ `KEEP_BACKTRACE_LEVEL`  │ false                                           │
   └─────────────────────────┴─────────────────────────────────────────────────┘

   Esses nomes representam opções usadas no controle da referência de chamada
   pelo `VarDump`.

   A referência de chamada permite identificar de onde uma operação de
   depuração foi solicitada. Seu funcionamento será detalhado na documentação
   desse módulo.

*  Modos de escrita em arquivos

   ┌───────────────────────────┬─────────┬─────────────────────────────────────┐
   │ _CONSTANTE_               │ _VALOR_ │ _FINALIDADE_                        │
   ├───────────────────────────┼─────────┼─────────────────────────────────────┤
   │ `INSERT_CONTENT_AT_START` │ 1       │ Inserir conteúdo no início do       │
   │                           │         │ arquivo.                            │
   ├───────────────────────────┼─────────┼─────────────────────────────────────┤
   │ `INSERT_CONTENT_AT_END`   │ 2       │ Inserir conteúdo no final do        │
   │                           │         │ arquivo.                            │
   ├───────────────────────────┼─────────┼─────────────────────────────────────┤
   │ `OVERWRITE_CONTENT`       │ 3       │ Substituir o conteúdo do arquivo.   │
   └───────────────────────────┴─────────┴─────────────────────────────────────┘

   Essas constantes são utilizadas por `Tools::writeFile()`. Os nomes
   permitem indicar a operação desejada sem depender da interpretação dos
   números `1`, `2` e `3` no código.



--------------------------------------------------------------------------------



▶️ PRÓXIMO TÓPICO: Processamento da requisição
LINK: /docs/estrutura-e-funcionamento/processamento-da-requisicao.md



---

👾 Esta documentação está sendo gerada com auxílio de IA...
🙋‍♂️ ...mas revisada sempre por um humano!
