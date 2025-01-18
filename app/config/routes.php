<?php

return [
    '/' => [
        'templateFile' => '/app/templates/main.php',
        'output' => 'view',

        '/images' => [
            'output' => 'file',
            'baseFolder' => '/app/images',
        ],
    ],
];
