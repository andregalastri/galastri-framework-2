<?php
// -----------------------------------------------------------------------------
// ╔═══════════════════════════════════════════════════════════════════════════╗
// #                    SEJA BEM VINDO AO GALASTRI FRAMEWORK!                  ║
// ╚═══════════════════════════════════════════════════════════════════════════╝
//
// Decidi colocar toda a documentação do framework dentro do próprio projeto.
//
// Ela está principalmente em formato de comentários como este aqui. Algumas
// descrições, porém, podem ser grandes demais ou exigirem um nível de
// explicação maior. Nestes casos, irei colocar um link interno apontando para
// um arquivo .md com uma explicação detalhada.
//
// Por falar nisso, antes de você continuar, confirme se você leu isso aqui e
// toda a sequência de tópicos antes de prosseguir por aqui.
//
//
// 💡
// ┌───────────────────────────────────────────────────────────────────────────┐
// │ *  Introdução ao Galastri Framwork:                                       │
// │    LINK: /docs/galastri-framework.md                                      │
// │                                                                           │
// │ *  Instalação:                                                            │
// │    LINK: /docs/instalacao/preparando-o-ambiente-de-desenvolvimento.md     │
// └───────────────────────────────────────────────────────────────────────────┘
// >



// -----------------------------------------------------------------------------
// ╔═══════════════════════════════════════════════════════════════════════════╗
// #                              public/index.php                             ║
// ╚═══════════════════════════════════════════════════════════════════════════╝
// 
// Este arquivo configura o comportamento inicial dos erros do PHP e carrega o
// bootstrap, responsável pela preparação do framework.
// 
// 
// #                       CONFIGURAÇÃO INICIAL DOS ERROS                      <
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// As duas primeiras instruções possuem as seguintes funções:
// 
// ┌────────────────────────────────────┬──────────────────────────────────────┐
// │ _INSTRUÇÃO_                        │ _FUNÇÃO_                             │
// ├────────────────────────────────────┼──────────────────────────────────────┤
// │ `ini_set('display_errors', true);` │ Ativa a exibição de erros pelo PHP   │
// │                                    │ durante a execução.                  │
// ├────────────────────────────────────┼──────────────────────────────────────┤
// │ `error_reporting(E_ALL)`           │ Inclui todos os níveis de erro no    │
// │                                    │ conjunto de erros reportados pelo    │
// │                                    │ PHP.                                 │
// └────────────────────────────────────┴──────────────────────────────────────┘

ini_set('display_errors', true);
error_reporting(E_ALL);

// ℹ️
// ┌───────────────────────────────────────────────────────────────────────────┐
// │ Essas configurações são aplicadas durante a requisição e não alteram      │
// │ permanentemente o arquivo `php.ini`.                                      │
// └───────────────────────────────────────────────────────────────────────────┘
// >
//
// #                          CARREGAMENTO DO BOOTSTRAP                        <
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// A última instrução incorpora o arquivo bootstrap.php:
// 
// O uso de `__DIR__` constrói o caminho a partir da pasta onde está o
// `index.php`. Nesse caso, `..` sobe da pasta `public` para a raiz do projeto,
// permitindo localizar a pasta `galastri`.
//
// O `require` executa o conteúdo do bootstrap. Se o arquivo não puder ser
// carregado, a inicialização é interrompida.

require(__DIR__.'/../galastri/bootstrap/bootstrap.php');
