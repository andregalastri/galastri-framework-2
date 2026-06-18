<?php

namespace app\controllers;

use galastri\modules\Controller;

final class Index extends Controller
{
    protected function main(): array
    {
        $version = file_get_contents(PROJECT_DIR.'/galastri/VERSION');
        
        return [
            'version' => $version,
        ];
    }
}
