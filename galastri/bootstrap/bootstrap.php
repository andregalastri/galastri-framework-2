<?php
/** -----------------------------------------------------------------------------
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                                 BOOTSTRAP                                 ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 * O bootstrap é a etapa que prepara os recursos básicos para a execução do
 * framework. É nesse momento que ficam disponíveis as constantes, as funções
 * auxiliares, o carregamento das classes e o gerenciador de erros.
 *
 * Atualmente essa preparação é organizada em três arquivos dentro da pasta
 * `galastri/bootstrap`:
 *
 * ┌───────────────┬───────────────────────────────────────────────────────────┐
 * │ ARQUIVO       │ RESPONSABILIDADE                                          │
 * ├───────────────┼───────────────────────────────────────────────────────────┤
 * │ bootstrap.php │ Coordena a preparação e inicia o processamento da         │
 * │               │ requisição.                                               │
 * ├───────────────┼───────────────────────────────────────────────────────────┤
 * │ constants.php │ Define caminhos, informações de versão e valores          │
 * │               │ utilizados pelo código.                                   │
 * ├───────────────┼───────────────────────────────────────────────────────────┤
 * │ functions.php │ Disponibiliza funções auxiliares.                         │
 * └───────────────┴───────────────────────────────────────────────────────────┘
 *
 * Além desses arquivos, a aplicação pode possuir seu próprio bootstrap em
 * `app/bootstrap.php`, que é executado depois desta preparação básica.
 * 
 * 
 * 
 * -----------------------------------------------------------------------------
 * 📂
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                 ARQUIVO /galastri/bootstrap/bootstrap.php                 ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 * O arquivo é carregado por `public/index.php`. Suas instruções são executadas
 * em sequência, preparando os recursos necessários para as etapas seguintes.
 * ─────────────────────────────────────────────────────────────────────────────
 */



/**
 * 🟢 REFERENCIAÇÃO AOS NOMES DAS CLASSES USADOS
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * As declarações `use`, aqui, permitem referenciarmos as classes que usaremos
 * neste arquivo. Isso permite que usemos seus nomes ou até mesmo apelidos (com
 * `use X as Y`), sem precisar informar o namespace completo em cada utilização.
 *
 * Por convenção, no framework sempre declaramos `use` para todas as classes que
 * usarmos no arquivo, mesmo as que estejam no mesmo namespace, pra manter um
 * padrão consistente.
 * 
 * ℹ️ OBSERVAÇÃO
 * ┌────────────────────────────────────────────────────────────────────────┐
 * │ Os `use` não carregam os arquivos. O autoloader realiza o carregamento │
 * │ quando o PHP precisa de uma classe que ainda não foi carregada, como   │
 * │ ao criar um objeto ou executar um método estático.                     │
 * └────────────────────────────────────────────────────────────────────────┘
 */

use galastri\core\ErrorHandler;
use galastri\extensions\Exception;
use galastri\core\Galastri;



/**
 * 🟢 ATIVANDO O BUFFER DE SAÍDA DO PHP
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * O buffer de saída do PHP é um armazenamento temporário para o conteúdo
 * produzido pelo PHP.
 *
 * Por exemplo, um texto gerado por `echo` permanece nesse buffer antes de ser
 * enviado ao navegador. Isso permite que o código manipule a saída durante o
 * processamento, ANTES de enviar ao navegador.
 *
 * ℹ️ OBSERVAÇÃO
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ Sua ativação, porém, não significa que uma saída anterior será descartada │
 * │ automaticamente quando ocorrer um erro.                                   │
 * │                                                                           │
 * │ No código atual, o `ErrorHandler` não realiza uma limpeza geral do buffer │
 * │ antes de apresentar um erro. Assim, um conteúdo armazenado anteriormente  │
 * │ pode aparecer junto da mensagem de erro.                                  │
 * └───────────────────────────────────────────────────────────────────────────┘
 */

ob_start();



/**
 * 🟢 CARREGAMENTO DAS CONSTANTES E AS FUNÇÕES AUXILIARES
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * 1. O primeiro arquivo define as constantes utilizadas pelo framework.
 *
 *    Aproveite para ver sobre as constantes agora e depois volte aqui: 
 *    🔗 LINK: /galastri/bootstrap/constants.php  
 *
 * 2. O segundo declara e disponibiliza as funções auxiliares.
 *
 *    Aproveite para ver sobre funções auxiliáres agora e depois volte aqui:
 *    🔗 LINK: /galastri/bootstrap/functions.php  
 */

require('constants.php');
require('functions.php');



/**
 * 🟢 CARREGAMENTO DO AUTOLOAD DO COMPOSER
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * A partir dessa instrução, o PHP passa a contar com o mecanismo que localiza
 * os arquivos das classes conforme os namespaces configurados no projeto.
 *
 * ✔️ EXEMPLO
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ Para referenciar o arquivo localizad em `galastri/core/Galastri.php`,     │
 * │ pode-se simplesmente informar o caminho através de namespaces             │
 * │                                                                           │
 * │ ◆ `use galastri\core\Galastri;`                                           │
 * │   Referencia a classe dentro de outra.                                    │
 * │                                                                           │
 * │ ◆ `galastri\core\Galastri::run();`                                        │
 * │   Executa o método `run()` da classe Galastri.                            │
 * │                                                                           │
 * │ O autoload basicamente procura o local do arquivo com base no caminho     │
 * │ informado no namespace.                                                   │
 * │                                                                           │
 * │ ┌──────────────────────────────────────────────────────────────────────┐  │
 * │ │ Isso afeta a performance?                                            │  │
 * │ │                                                                      │  │
 * │ │ O autoload não carrega todas as classes de uma vez. Ele carrega      │  │
 * │ │ apenas uma classe quando ela é necessária. Portanto, não se trata de │  │
 * │ │ um recurso problemático no sentido de performance.                   │  │
 * │ └──────────────────────────────────────────────────────────────────────┘  │
 * └───────────────────────────────────────────────────────────────────────────┘
 */

require(AUTOLOADER_FILE_PATH);



/**
 * 🟢 DISPONIBILIZANDO O ALIAS \VarDump
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Certas classes podem ser referenciadas com apelidos. Aqui usamos para
 * encurtar o namespace de `\galastri\modules\VarDump`, reduzindo-o apenas para
 * \VarDump.
 *
 * Isso facilita chamar a classe.
 *
 * ℹ️ OBSERVAÇÃO
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ Por que não fazer isso com todas as classes?                              │
 * │ Porque precisamos manter o código auto explicativo. Se encurtarmos todas  │
 * │ as classes, perderíamos a compreensão de quais classes são módulos,       │
 * │ extensões ou parte do núcleo do framework.                                │
 * │                                                                           │
 * │ Aqui, o VarDump é uma exceção, pois trata-se de um módulo de debug que    │
 * │ precisa estar sempre disponível, mesmo não estando declarado, e           │
 * │ facilmente acessível. Do contrário, teríamos sempre que chamar            │
 * │ \galastri\modules\VarDump::<método> para executar seus métodos            │
 * │ ou referenciar use \galastri\modules\VarDump no começo de todas as        │
 * │ classes, incluindo as da aplicação.                                       │
 * └───────────────────────────────────────────────────────────────────────────┘
 *
 * Aproveite para ver sobre a classe VarDump agora e depois volte aqui:  
 * 🔗 LINK: /galastri/modules/VarDump.php  
 */

class_alias(\galastri\modules\VarDump::class, '\VarDump');



/**
 * 🟢 ATIVANDO O GERENCIADOR DE ERROS
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * A classe `ErrorHandler` do framework é a responsável pelo tratamento de erros
 * e exceções. Essa chamada executa o método `run()` e registra os mecanismos
 * responsáveis por:
 *
 * > Tratar os erros do PHP abrangidos pelo manipulador de erros.
 *
 * > Tratar exceções que não foram capturadas pela aplicação.
 *
 * > Verificar determinados erros fatais no encerramento da execução.
 *
 * ⚠️ AVISO
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ Erros ocorridos antes da ativação do `ErrorHandler`, como uma falha ao    │
 * │ carregar o próprio bootstrap, ainda podem aparecer no formato padrão      │
 * │ do PHP.                                                                   │
 * └───────────────────────────────────────────────────────────────────────────┘
 *
 * Aproveite para ver sobre a classe ErrorHandler agora e depois volte aqui:
 * 🔗 LINK: /galastri/core/ErrorHandler.php 
 */
 
ErrorHandler::run();



/**
 * 🟢 EXECUTANDO O BOOTSTRAP DA APLICAÇÃO, SE EXISTIR
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Verifica se o arquivo de inicialização próprio da aplicação localizado em
 * `/app/bootstrap.php` existe.
 *
 * Se existir, ele carrega neste momento.
 */

if (file_exists(APP_BOOTSTRAP_FILE_PATH)) {
    require(APP_BOOTSTRAP_FILE_PATH);
}



/**
 * 🟢 INICIA O PROCESSAMENTO DA REQUISIÇÃO
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * O método `run()` da classe `Galastri` é executado para iniciar o
 * processamento da requisição.
 */

Galastri::run();



/**
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * ▶️ PRÓXIMO TÓPICO:
 * 🔗 LINK: /galastri/core/Galastri.php
 */
