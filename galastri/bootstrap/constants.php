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
 * 🟢 CAMINHO DO PROJETO
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Define o caminho absoluto da pasta raiz onde o framework está no servidor,
 * padronizando a localização e facilitando a recuperação deste dado em qualquer
 * outra parte do projeto.
 *
 * 
 * ✔️ EXEMPLO
 * ┌───────────────────────────────────────────────────────────────────────────┐
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
 * 🟢 AINDA NÃO IMPLEMENTADO
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Ainda não implementado.
 */

define('PERFORMANCE_ANALYSIS_TAG', bin2hex(random_bytes(10)));



/**
 * 🟢 VERSÕES
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
 * os metadados da resposta.
 */

$galastriVersion = 'Version file not found';
$appVersion = 'Version file not found';

if (file_exists(PROJECT_DIR.'/galastri/VERSION')) {
    $galastriVersion = trim(file_get_contents(PROJECT_DIR.'/galastri/VERSION'));
}

define('GALASTRI_VERSION', $galastriVersion);

if (file_exists(PROJECT_DIR.'/app/VERSION')) {
    $appVersion = trim(file_get_contents(PROJECT_DIR.'/app/VERSION'));
}

define('APP_VERSION', $appVersion);



/**
 * 🟢 TIPOS MIME
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Armazena os tipos MIME da aplicação. Se ele não existir, o valor utilizado
 * será um array vazio.
 */

$mimeTypeList = [];

if (file_exists(PROJECT_DIR.'/app/config/mime-types.php')) {
    $mimeTypeList = require(PROJECT_DIR.'/app/config/mime-types.php');
}



/**
 * 🟢 MODOS DE ESCRITA EM ARQUIVOS
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Permitem indicar qual a operação será usada para escrever em arquivos, sem
 * depender da interpretação dos números `1`, `2` e `3` no código, que tornaria
 * a leitura pouco autoexplicativa, como em chamadas de função.
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
 * 🟢 É UM OBJETO?
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Permitem indicar se um elemento é um objeto ou não, sem depender da
 * interpretação dos valores `true` e `false` no código, que tornaria a leitura
 * pouco autoexplicativa, como em chamadas de função.
 *
 * ┌──────────────────┬───────┬──────────────────────────────────────────────────┐
 * │ CONSTANTE        │ VALOR │ FINALIDADE                                       │
 * ├──────────────────┼───────┼──────────────────────────────────────────────────┤
 * │ IS_AN_OBJECT     │ true  │ Indicar literalmente que um elemento é um        │
 * │                  │       │ objeto.                                          │
 * ├──────────────────┼───────┼──────────────────────────────────────────────────┤
 * │ IS_NOT_AN_OBJECT │ false │ Indicar literalmente que um elemento NÃO É um    │
 * │                  │       │ objeto.                                          │
 * └──────────────────┴───────┴──────────────────────────────────────────────────┘
 */

const IS_AN_OBJECT = true;
const IS_NOT_AN_OBJECT = false;


// -----------------------------------------------------------------------------
// ◀️ VOLTAR AO TÓPICO ANTERIOR:
// LINK: /galastri/bootstrap/bootstrap.php
