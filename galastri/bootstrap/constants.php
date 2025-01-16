<?php

define('PROJECT_DIR', realpath(__DIR__.'/../../'));
define('PERFORMANCE_ANALYSIS_TAG', bin2hex(random_bytes(10)));

const RESET_BACKTRACE_LEVEL = true;
const KEEP_BACKTRACE_LEVEL = false;

const INSERT_CONTENT_AT_START = 1;
const INSERT_CONTENT_AT_END = 2;
const OVERWRITE_CONTENT = 3;
