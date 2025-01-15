<?php

namespace galastri\bootstrap;

use galastri\core\ErrorHandler;
use galastri\extensions\Exception;
use galastri\core\Galastri;

ob_start();

require('constants.php');
require('functions.php');
require(PROJECT_DIR.'/vendor/autoload.php');


class_alias(\galastri\modules\VarDump::class, '\VarDump');

ErrorHandler::run();

if (file_exists(PROJECT_DIR.'/app/bootstrap.php')) {
    require(PROJECT_DIR.'/app/bootstrap.php');
}

Galastri::run();
