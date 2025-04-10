<?php
/**
 * Documentação:
 * https://github.com/andregalastri/galastri-framework-2/wiki/Bootstrap
 */

define('PROJECT_DIR', realpath(__DIR__.'/../../'));
define('PERFORMANCE_ANALYSIS_TAG', bin2hex(random_bytes(10)));
define('GALASTRI_VERSION', file_exists(PROJECT_DIR.'/galastri/VERSION') ? trim(file_get_contents(PROJECT_DIR.'/galastri/VERSION')) : 'Version not found');
define('MIME_TYPE_LIST', file_exists(PROJECT_DIR.'/app/config/mime-types.php') ? require(PROJECT_DIR.'/app/config/mime-types.php') : []);

const RESET_BACKTRACE_LEVEL = true;
const KEEP_BACKTRACE_LEVEL = false;

const INSERT_CONTENT_AT_START = 1;
const INSERT_CONTENT_AT_END = 2;
const OVERWRITE_CONTENT = 3;
