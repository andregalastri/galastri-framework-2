<?php

return [
    'timezone' => [
        'defaultValue' => date_default_timezone_get(),
        'allowedTypes' => ['string'],
        'allowedValues' => [],
        'configFile' => PROJECT_DIR.'/app/config/route.php',
        'configType' => 'route',
        'execute' => null
    ],

    'offline' => [
        'defaultValue' => false,
        'allowedTypes' => ['bool'],
        'allowedValues' => [],
        'configFile' => PROJECT_DIR.'/app/config/route.php',
        'configType' => 'route',
        'execute' => null
    ],

    'projectTitle' => [
        'defaultValue' => null,
        'allowedTypes' => ['string', 'null'],
        'allowedValues' => [],
        'configFile' => PROJECT_DIR.'/app/config/route.php',
        'configType' => 'route',
        'execute' => null
    ],
];

// return [
//     'timezone' => date_default_timezone_get(),
//     'offline' => false,
//     'projectTitle' => null,
//     'pageTitle' => null,
//     'authTag' => null,
//     'authFailRedirect' => null,
//     'forceRedirect' => null,
//     'namespace' => null,
//     'notFoundRedirect' => null,
//     'output' => null,
//     'browserCache' => null,
//     'templateFile' => null,
//     'baseFolder' => null,
//     'offlineMessage' => null,
//     'authFailMessage' => null,
//     'permissionFailMessage' => null,
//     'ignoreMimeType' => null,
//     'templateEngineClass' => null,
//     'downloadable' => false,
//     'allowedExtensions' => null,
//     'viewPath' => null,
//     'request' => null,
//     'urlParameters' => null,
// ];
