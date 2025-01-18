<?php

return [
    'urlRoot' => [
        'defaultValue' => '/',
        'validTypes' => ['string'],
        'invalidTypes' => [],
        'validValues' => [],
        'invalidValues' => [],
        'validRegex' => '/^\/(?:[\p{L}\p{N}_-]+\/?)*[\p{L}\p{N}_-]+$|^\/$/u',
        'invalidRegex' => '',
        'context' => 'route',
    ],
    // 'timezone' => [
    //     'defaultValue' => date_default_timezone_get(),
    //     'allowedTypes' => ['string'],
    //     'allowedValues' => [],
    //     'configFile' => '/app/config/route.php',
    //     'configContext' => 'route',
    // ],

    // 'offline' => [
    //     'defaultValue' => false,
    //     'allowedTypes' => ['bool'],
    //     'allowedValues' => [],
    //     'configFile' => '/app/config/route.php',
    //     'configContext' => 'route',
    // ],

    // 'projectTitle' => [
    //     'defaultValue' => null,
    //     'allowedTypes' => ['string', 'null'],
    //     'allowedValues' => [],
    //     'configFile' => '/app/config/route.php',
    //     'configContext' => 'route',
    // ],
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
