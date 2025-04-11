<?php

return [
    '/' => [
        'displayErrors' => true,
        'permissions' => [
            'userPermissions' => \app\modules\UserPermission::class,
            'managerPermissions' => \app\modules\ManagerPermission::class,
        ],

        '@main' => [
            // Configuração do endpoint principal.
        ],

        '/listar-produtos' => [

            '@main' => [
                'permissions' => [
                    'allow' => ['userPermissions' => ['listar-produtos']],
                ],
            ],

            '@deletar' => [
                'permissions' => [
                    'deny' => ['managerPermissions' => ['deletar-produtos']],
                ],
            ]
        ],
    ],
];
