<?php

define('PROJECT_DIR', realpath(__DIR__.'/../../'));
define('PERFORMANCE_ANALYSIS_TAG', bin2hex(random_bytes(10)));
define('GALASTRI_VERSION', file_exists(PROJECT_DIR.'/galastri/VERSION') ? trim(file_get_contents(PROJECT_DIR.'/galastri/VERSION')) : 'Version not found');

const RESET_BACKTRACE_LEVEL = true;
const KEEP_BACKTRACE_LEVEL = false;

const INSERT_CONTENT_AT_START = 1;
const INSERT_CONTENT_AT_END = 2;
const OVERWRITE_CONTENT = 3;
