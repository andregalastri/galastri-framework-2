<?php

return [
    '/' => [
        'displayErrors' => true,
        'showTrace' => false,
        'title' => 'Galastri Framework',

        '@main' => [
            // Configuração do endpoint principal.
        ],

        '@protected-route' => [
            'title' => 'Galastri a',
            'output' => 'view',
            'authTag' => 'login',
            // Configuração do endpoint principal.
        ],

        '@login' => [
            'output' => 'json',
        ],

        '@logout' => [
            'output' => 'json',
        ],
    ],
];
