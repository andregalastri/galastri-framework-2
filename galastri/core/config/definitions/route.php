<?php
/**
 * Documentação:
 * https://github.com/andregalastri/galastri-framework-2/wiki/Lista-de-Configura%C3%A7%C3%B5es-Route
 */

return [
    'projectName' => [
        'defaultValue' => '',
        'validTypes' => ['string'],
        'invalidTypes' => [],
        'validValues' => [],
        'invalidValues' => [],
        'validRegex' => '',
        'invalidRegex' => '',
        'context' => 'route',
    ],

    'title' => [
        'defaultValue' => '',
        'validTypes' => ['string'],
        'invalidTypes' => [],
        'validValues' => [],
        'invalidValues' => [],
        'validRegex' => '',
        'invalidRegex' => '',
        'context' => 'route',
    ],

    'output' => [
        'defaultValue' => 'view',
        'validTypes' => ['string'],
        'invalidTypes' => [],
        'validValues' => ['view', 'file', 'json'],
        'invalidValues' => [],
        'validRegex' => '',
        'invalidRegex' => '',
        'context' => 'route',
    ],
    
    'timezone' => [
        'defaultValue' => date_default_timezone_get(),
        'validTypes' => ['string'],
        'invalidTypes' => [],
        'validValues' => [],
        'invalidValues' => [],
        'validRegex' => '',
        'invalidRegex' => '',
        'context' => 'route',
    ],

    'offline' => [
        'defaultValue' => false,
        'validTypes' => ['bool'],
        'invalidTypes' => [],
        'validValues' => [],
        'invalidValues' => [],
        'validRegex' => '',
        'invalidRegex' => '',
        'context' => 'route',
    ],

    'offlineRedirectTo' => [
        'defaultValue' => '',
        'validTypes' => ['string'],
        'invalidTypes' => [],
        'validValues' => [],
        'invalidValues' => [],
        'validRegex' => '',
        'invalidRegex' => '',
        'context' => 'route',
    ],

    'offlineMessage' => [
        'defaultValue' => 'System is temporarily offline.',
        'validTypes' => ['string'],
        'invalidTypes' => [],
        'validValues' => [],
        'invalidValues' => [],
        'validRegex' => '',
        'invalidRegex' => '',
        'context' => 'route',
    ],

    'authTag' => [
        'defaultValue' => '',
        'validTypes' => ['string'],
        'invalidTypes' => [],
        'validValues' => [],
        'invalidValues' => [],
        'validRegex' => '',
        'invalidRegex' => '',
        'context' => 'route',
    ],

    'authFailRedirectTo' => [
        'defaultValue' => '',
        'validTypes' => ['string'],
        'invalidTypes' => [],
        'validValues' => [],
        'invalidValues' => [],
        'validRegex' => '',
        'invalidRegex' => '',
        'context' => 'route',
    ],

    'authFailMessage' => [
        'defaultValue' => 'Forbidden.',
        'validTypes' => ['string'],
        'invalidTypes' => [],
        'validValues' => [],
        'invalidValues' => [],
        'validRegex' => '',
        'invalidRegex' => '',
        'context' => 'route',
    ],

    'notFoundRedirectTo' => [
        'defaultValue' => '',
        'validTypes' => ['string'],
        'invalidTypes' => [],
        'validValues' => [],
        'invalidValues' => [],
        'validRegex' => '',
        'invalidRegex' => '',
        'context' => 'route',
    ],

    'notFoundMessage' => [
        'defaultValue' => '404 - Route not found',
        'validTypes' => ['string'],
        'invalidTypes' => [],
        'validValues' => [],
        'invalidValues' => [],
        'validRegex' => '',
        'invalidRegex' => '',
        'context' => 'route',
    ],

    'forceRedirectTo' => [
        'defaultValue' => false,
        'validTypes' => ['bool', 'string'],
        'invalidTypes' => [],
        'validValues' => [],
        'invalidValues' => [],
        'validRegex' => '',
        'invalidRegex' => '',
        'context' => 'route',
    ],

    'namespace' => [
        'defaultValue' => '',
        'validTypes' => ['string'],
        'invalidTypes' => [],
        'validValues' => [],
        'invalidValues' => [],
        'validRegex' => '/^(\\\\?[a-zA-Z_][a-zA-Z0-9_]*(\\\\[a-zA-Z_][a-zA-Z0-9_]*)*)$/u',
        'invalidRegex' => '',
        'context' => 'route',
    ],

    'browserCache' => [
        'defaultValue' => null,
        'validTypes' => ['null', 'string'],
        'invalidTypes' => [],
        'validValues' => [],
        'invalidValues' => [],
        'validRegex' => '',
        'invalidRegex' => '',
        'context' => 'route',
    ],

    'templateFile' => [
        'defaultValue' => '/app/templates/template.php',
        'validTypes' => ['string'],
        'invalidTypes' => [],
        'validValues' => [],
        'invalidValues' => [],
        'validRegex' => '/^\/(?:[\p{L}\p{N}_-]+\/)*[\p{L}\p{N}_-]+\.php$/u',
        'invalidRegex' => '',
        'context' => 'route',
    ],

    'viewFolder' => [
        'defaultValue' => '/app/views',
        'validTypes' => ['string'],
        'invalidTypes' => [],
        'validValues' => [],
        'invalidValues' => [],
        'validRegex' => '/^\/(?:[\p{L}\p{N}_-]+\/?)*[\p{L}\p{N}_-]+$|^\/$/u',
        'invalidRegex' => '',
        'context' => 'route',
    ],

    'fileFolder' => [
        'defaultValue' => '',
        'validTypes' => ['string'],
        'invalidTypes' => [],
        'validValues' => [],
        'invalidValues' => [],
        'validRegex' => '/^\/(?:[\p{L}\p{N}_-]+\/?)*[\p{L}\p{N}_-]+$|^\/$/u',
        'invalidRegex' => '',
        'context' => 'route',
    ],

    'isDownloadableFile' => [
        'defaultValue' => false,
        'validTypes' => ['bool'],
        'invalidTypes' => [],
        'validValues' => [],
        'invalidValues' => [],
        'validRegex' => '',
        'invalidRegex' => '',
        'context' => 'route',
    ],

    'allowedFileExtensions' => [
        'defaultValue' => [],
        'validTypes' => ['array'],
        'invalidTypes' => [],
        'validValues' => [],
        'invalidValues' => [],
        'validRegex' => '',
        'invalidRegex' => '',
        'context' => 'route',
    ],

    'permissionFailMessage' => [
        'defaultValue' => 'Forbidden.',
        'validTypes' => ['string'],
        'invalidTypes' => [],
        'validValues' => [],
        'invalidValues' => [],
        'validRegex' => '',
        'invalidRegex' => '',
        'context' => 'route',
    ],

    'validateMimeType' => [
        'defaultValue' => true,
        'validTypes' => ['bool'],
        'invalidTypes' => [],
        'validValues' => [],
        'invalidValues' => [],
        'validRegex' => '',
        'invalidRegex' => '',
        'context' => 'route',
    ],
];
