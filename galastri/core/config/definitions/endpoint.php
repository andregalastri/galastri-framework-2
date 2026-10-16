<?php
/**
 * Documentação:
 * https://github.com/andregalastri/galastri-framework-2/wiki/Lista-de-Configura%C3%A7%C3%B5es-Endpoint
 */

return [
    'method' => [
        'defaultValue' => '',
        'validTypes' => ['string'],
        'invalidTypes' => [],
        'validValues' => [],
        'invalidValues' => [],
        'validRegex' => '',
        'invalidRegex' => '',
        'context' => 'endpoint',
    ],

    'parameters' => [
        'defaultValue' => '',
        'validTypes' => ['string'],
        'invalidTypes' => [],
        'validValues' => [],
        'invalidValues' => [],
        'validRegex' => '/\A\/\??[a-zA-Z0-9_]+(?:\/\??[a-zA-Z0-9_]+)*\z/',
        'invalidRegex' => '',
        'context' => 'endpoint',
    ],

    'httpMethod' => [
        'defaultValue' => [],
        'validTypes' => ['array'],
        'invalidTypes' => [],
        'validValues' => [],
        'invalidValues' => [],
        'validRegex' => '',
        'invalidRegex' => '',
        'context' => 'endpoint',
    ],

    'viewFile' => [
        'defaultValue' => '',
        'validTypes' => ['string'],
        'invalidTypes' => [],
        'validValues' => [],
        'invalidValues' => [],
        'validRegex' => '/^(\/(?:[a-zA-Z0-9_-]+|(\.\.))(?:\/(?:[a-zA-Z0-9_-]+|(\.\.)))*\/[a-zA-Z0-9_-]+\.php|[a-zA-Z0-9_-]+\.php)$/u',
        'invalidRegex' => '',
        'context' => 'endpoint',
    ],
];
