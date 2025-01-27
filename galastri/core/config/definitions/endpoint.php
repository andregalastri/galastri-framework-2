<?php

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
        'validRegex' => '/^\/(?:[a-zA-Z0-9_]+\/?)*[a-zA-Z0-9_]+$/',
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
