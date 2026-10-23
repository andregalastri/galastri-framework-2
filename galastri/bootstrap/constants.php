<?php
/**
 * -----------------------------------------------------------------------------
 * 📂
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                  ARQUIVO galastri/bootstrap/constants.php                 ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 * Este arquivo define constantes que podem ser utilizados em diferentes partes
 * do framework e da aplicação.
 * ─────────────────────────────────────────────────────────────────────────────
 */

/**
 * 🚩 CAMINHO DO PROJETO
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Define o caminho absoluto da pasta raiz onde o framework está no servidor,
 * padronizando a localização e facilitando a recuperação deste dado em qualquer
 * outra parte do projeto.
 * 
 * ✔️
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ EXEMPLO                                                                   │
 * │ Supondo que o projeto esteja em '/home/desenvolvedor/projeto'             │
 * │                                                                           │
 * │ A expressão...                                                            │
 * │ ┌──────────────────────────────────────┐                                  │
 * │ │ PROJECT_DIR.'/app/config/routes.php' │                                  │
 * │ └──────────────────────────────────────┘                                  │
 * │ ...será convertida em                                                     │
 * │ ┌─────────────────────────────────────────────────────┐                   │
 * │ │ '/home/desenvolvedor/projeto/app/config/routes.php' │                   │
 * │ └─────────────────────────────────────────────────────┘                   │
 * └───────────────────────────────────────────────────────────────────────────┘
 */
define('PROJECT_DIR', realpath(__DIR__.'/../../'));

/**
 * 🚩 AINDA NÃO IMPLEMENTADO
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Texto
 */
define('PERFORMANCE_ANALYSIS_TAG', bin2hex(random_bytes(10)));

/**
 * 🚩 VERSÕES
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Armazena o conteúdo dos arquivos de versão.
 *
 * Se um dos arquivos não existir, a constante correspondente armazenará a
 * string "Version file not found"
 *
 * ┌──────────────────┬──────────────────────────────────────────┐
 * │ GALASTRI_VERSION │ Conteúdo do arquivo `/galastri/VERSION`. │
 * ├──────────────────┼──────────────────────────────────────────┤
 * │ APP_VERSION      │ Conteúdo do arquivo `/app/VERSION`.      │
 * └──────────────────┴──────────────────────────────────────────┘
 * 
 * Esses valores ficam disponíveis para consulta pelo código e podem integrar
 * os metadados da resposta
 */
define(
    'GALASTRI_VERSION',
    file_exists(PROJECT_DIR.'/galastri/VERSION')
    ? trim(file_get_contents(PROJECT_DIR.'/galastri/VERSION'))
    : 'Version file not found'
);

define(
    'APP_VERSION',
    file_exists(PROJECT_DIR.'/app/VERSION')
    ? trim(file_get_contents(PROJECT_DIR.'/app/VERSION'))
    : 'Version file not found'
);

/**
 * 🚩 TIPOS MIME
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Armazena os tipos MIME da aplicação. Se ele não existir, o valor utilizado
 * será um array vazio.
 */
define(
    'MIME_TYPE_LIST',
    file_exists(PROJECT_DIR.'/app/config/mime-types.php')
    ? require(PROJECT_DIR.'/app/config/mime-types.php')
    : []
);

/**
 * 🚩 CONTROLES DE REFERÊNCIA DE CHAMADA
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Utilizadas no `VarDump`. Os nomes permitem indicar a operaçao desejada sem
 * depender da interpretação dos valores `true` e `false` no código.
 * 
 * ┌───────────────────────┬───────┬───────────────────────────────────────────┐
 * │ CONSTANTE             │ VALOR │ FINALIDADE                                │
 * ├───────────────────────┼───────┼───────────────────────────────────────────┤
 * │ RESET_BACKTRACE_LEVEL │ true  │ <falta descrever>                         │
 * ├───────────────────────┼───────┼───────────────────────────────────────────┤
 * │ KEEP_BACKTRACE_LEVEL  │ false │ <falta descrever>                         │
 * └───────────────────────┴───────┴───────────────────────────────────────────┘
 *
 * Aproveite para ver sobre a classe VarDump agora, se ainda não viu, e depois
 * volte aqui:  
 * LINK: /galastri/modules/VarDump.php  
 */
const RESET_BACKTRACE_LEVEL = true;
const KEEP_BACKTRACE_LEVEL = false;

/**
 * 🚩 MODOS DE ESCRITA EM ARQUIVOS
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Utilizadas por `Tools::writeFile()`. Os nomes permitem indicar a operação
 * desejada sem depender da interpretação dos números `1`, `2` e `3` no código.
 * 
 * ┌─────────────────────────┬───────┬────────────────────────────────────────┐
 * │ CONSTANTE               │ VALOR │ FINALIDADE                             │
 * ├─────────────────────────┼───────┼────────────────────────────────────────┤
 * │ INSERT_CONTENT_AT_START │ 1     │ Inserir conteúdo no início do arquivo. │
 * ├─────────────────────────┼───────┼────────────────────────────────────────┤
 * │ INSERT_CONTENT_AT_END   │ 2     │ Inserir conteúdo no final do arquivo.  │
 * ├─────────────────────────┼───────┼────────────────────────────────────────┤
 * │ OVERWRITE_CONTENT       │ 3     │ Substituir o conteúdo do arquivo.      │
 * └─────────────────────────┴───────┴────────────────────────────────────────┘
 *
 * Aproveite para ver sobre a classe VarDump agora, se ainda não viu, e depois
 * volte aqui:  
 * LINK: /galastri/modules/VarDump.php  
 */
const INSERT_CONTENT_AT_START = 1;
const INSERT_CONTENT_AT_END = 2;
const OVERWRITE_CONTENT = 3;



/**
 * -----------------------------------------------------------------------------
 * ◀️ TÓPICO ANTERIOR: bootstrap.php
 * LINK: /galastri/bootstrap/bootstrap.php
 *
 *
 *
 * ---
 *
 * 👾 Esta documentação está sendo gerada com auxílio de IA...
 * 🙋‍♂️ ...mas revisada sempre por um humano!
 */
