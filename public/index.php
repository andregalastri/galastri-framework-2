<?php
/** -----------------------------------------------------------------------------
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                    SEJA BEM VINDO AO GALASTRI FRAMEWORK!                  ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 *
 * Decidi colocar toda a documentação do framework dentro do próprio projeto.
 *
 * Ela está principalmente em formato de comentários como este aqui. Algumas
 * descrições, porém, podem ser grandes demais ou exigirem um nível de
 * explicação maior. Nestes casos, irei colocar um link interno apontando para
 * um arquivo .md com uma explicação detalhada.
 *
 * Por falar nisso, antes de você continuar, confirme se você leu o conteúdo
 * abaixo, seguindo os tópicos até chegar neste arquivo aqui prosseguir por
 * aqui:
 *
 * Introdução ao Galastri Framwork:
 * 🔗 LINK: /docs/galastri-framework.md  
 *
 * Preparando o ambiente de desenvolvimento:
 * 🔗 LINK: /docs/instalacao/preparando-o-ambiente-de-desenvolvimento.md
 *
 * 
 * 
 * -----------------------------------------------------------------------------
 * 📂
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                         ARQUIVO public/index.php                          ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 * Este arquivo configura o comportamento inicial dos erros do PHP e carrega o
 * bootstrap, responsável pela preparação do framework.
 * ─────────────────────────────────────────────────────────────────────────────
 */



/**
 * 🟢 ATIVANDO A EXIBIÇÃO DE ERROS PELO PHP DURANTE A EXECUÇÃO
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * O motivo disso é que será o próprio framework que fará o tratamento e
 * exibição de mensagens de erro e isso exige que elas estejam visíveis para o
 * framework.
 *
 * Isso não significa que as mensagens vão ser exibidas ao usuário, pois o
 * framework possui uma opção chamada `displayErrors` que servirá justamente
 * para controlar este comportamento.
 * 
 * ℹ️ OBSERVAÇÃO
 * ┌───────────────────────────────────────────────────────────────────────┐
 * │ Estas configurações abaixo não alteram o arquivo `php.ini`, elas são  │
 * │ aplicadas apenas durante a requisição.                                │
 * └───────────────────────────────────────────────────────────────────────┘
 */

ini_set('display_errors', true);



/**
 * 🟢 INCLUINDO TODOS OS NÍVEIS DE ERRO REPORTADOS PELO PHP
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Mesmo motivo acima.
 */

error_reporting(E_ALL);



/**
 * 🟢 CARREGAMENTO DO BOOTSTRAP
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * O arquivo bootstrap.php é importado e executado aqui. Ele é o responsável
 * pela inicializa. A construção do caminho até o bootstrap se dá da seguinte
 * forma:
 *
 * 1. O uso de __DIR__ constrói o caminho a partir da pasta onde está o
 *    `index.php`.
 *
 * 2. O uso de `/../` sobe da pasta `public` para a raiz do projeto, permitindo
 *    localizar a pasta `galastri`.
 *
 * 3. Por fim, aponta-se para o local onde está o arquivo `bootstrap.php`.
 *
 * O `require` executa o conteúdo do `bootstrap.php`. Se o arquivo não puder ser
 * carregado, a inicialização é interrompida.
 */ 

require(__DIR__.'/../galastri/bootstrap/bootstrap.php');



// -----------------------------------------------------------------------------
// ▶️ PRÓXIMO TÓPICO:
// 🔗 LINK: /galastri/bootstrap/bootstrap.php
