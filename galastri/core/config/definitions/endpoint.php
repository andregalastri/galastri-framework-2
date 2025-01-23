<?php

return [
    'downloadable' => [
        'defaultValue' => false,
        'validTypes' => ['bool'],
        'invalidTypes' => [],
        'validValues' => [],
        'invalidValues' => [],
        'validRegex' => '',
        'invalidRegex' => '',
        'context' => 'endpoint',
    ],

    'allowedExtensions' => [
        'defaultValue' => null,
        'validTypes' => ['null', 'array'],
        'invalidTypes' => [],
        'validValues' => [],
        'invalidValues' => [],
        'validRegex' => '',
        'invalidRegex' => '',
        'context' => 'endpoint',
    ],

    'viewPath' => [
        'defaultValue' => 'app/views',
        'validTypes' => ['string'],
        'invalidTypes' => [],
        'validValues' => [],
        'invalidValues' => [],
        'validRegex' => '',
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
];
