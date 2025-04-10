<?php
/**
 * Documentação:
 * https://github.com/andregalastri/galastri-framework-2/wiki/Classe-Definition
 */

namespace galastri\core\config;

use galastri\extensions\Exception;
use galastri\language\Message;
use galastri\modules\Tools;

final class Definition
{
    private const ALLOWED_CONFIG_CONTEXT = ['debug', 'route', 'node', 'endpoint'];

    private string $name;
    private mixed $value;
    private array $validTypes;
    private array $invalidTypes;
    private array $validValues;
    private array $invalidValues;
    private string $validRegex;
    private string $invalidRegex;
    private string $context;

    public function __construct(string $name, array $definitions)
    {
        $this->setName($name);
        $this->setValidTypes($definitions['validTypes']);
        $this->setInvalidTypes($definitions['invalidTypes']);
        $this->setValidValues($definitions['validValues']);
        $this->setInvalidValues($definitions['invalidValues']);
        $this->setValidRegex($definitions['validRegex']);
        $this->setInvalidRegex($definitions['invalidRegex']);
        $this->setContext($definitions['context']);
        $this->setValue($definitions['defaultValue']);
    }

    private function setName(mixed $value): void
    {
        if (!$this->isOfType('string', $value)) {
            throw new Exception(
                Message::get("DEFINITION_INVALID_NAME"),
                [
                    'string',
                    $value,
                    Tools::typeOf($value),
                ]
            );
        }

        $this->name = $value;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /*********************************************** */

    private function setValidTypes(mixed $values): void
    {
        if (!$this->isOfType('array', $values)) {
            throw new Exception(
                Message::get("DEFINITION_INVALID_TYPE"),
                [
                    $this->getName(),
                    'validTypes',
                    'array',
                    Tools::typeOf($values),
                ]
            );
        }

        foreach ($values as $value) {
            if (!$this->isOfType('string', $value)) {
                throw new Exception(
                    Message::get("DEFINITION_INVALID_TYPE_IN_ARRAY"),
                    [
                        $this->getName(),
                        'validTypes',
                        'string',
                        Tools::typeOf($value),
                    ]
                );
            }
        }

        $this->validTypes = $values;
    }

    public function getValidTypes(): array
    {
        return $this->validTypes;
    }

    /*********************************************** */

    private function setInvalidTypes(mixed $values): void
    {
        if (!$this->isOfType('array', $values)) {
            throw new Exception(
                Message::get("DEFINITION_INVALID_TYPE"),
                [
                    $this->getName(),
                    'invalidTypes',
                    'array',
                    Tools::typeOf($values),
                ]
            );
        }

        foreach ($values as $value) {
            if (!$this->isOfType('string', $value)) {
                throw new Exception(
                    Message::get("DEFINITION_INVALID_TYPE_IN_ARRAY"),
                    [
                        $this->getName(),
                        'invalidTypes',
                        'string',
                        Tools::typeOf($value),
                    ]
                );
            }
        }

        $this->invalidTypes = $values;
    }

    public function getInvalidTypes(): array
    {
        return $this->invalidTypes;
    }

    /*********************************************** */

    private function setValidValues(mixed $values): void
    {
        if (!$this->isOfType('array', $values)) {
            throw new Exception(
                Message::get("DEFINITION_INVALID_TYPE"),
                [
                    $this->getName(),
                    'validValues',
                    'array',
                    Tools::typeOf($values),
                ]
            );
        }

        $this->validValues = $values;
    }

    public function getValidValues(): array
    {
        return $this->validValues;
    }

    /*********************************************** */

    private function setInvalidValues(mixed $values): void
    {
        if (!$this->isOfType('array', $values)) {
            throw new Exception(
                Message::get("DEFINITION_INVALID_TYPE"),
                [
                    $this->getName(),
                    'invalidValues',
                    'array',
                    Tools::typeOf($values),
                ]
            );
        }

        $this->invalidValues = $values;
    }

    public function getInvalidValues(): array
    {
        return $this->invalidValues;
    }

    /*********************************************** */

    private function setValidRegex(mixed $value): void
    {
        if (!$this->isOfType('string', $value)) {
            throw new Exception(
                Message::get("DEFINITION_INVALID_TYPE"),
                [
                    $this->getName(),
                    'validRegex',
                    'string',
                    Tools::typeOf($value),
                ]
            );
        }

        $this->validRegex = $value;
    }

    public function getValidRegex(): string
    {
        return $this->validRegex;
    }

    /*********************************************** */

    private function setInvalidRegex(mixed $value): void
    {
        if (!$this->isOfType('string', $value)) {
            throw new Exception(
                Message::get("DEFINITION_INVALID_TYPE"),
                [
                    $this->getName(),
                    'invalidRegex',
                    'string',
                    Tools::typeOf($value),
                ]
            );
        }

        $this->invalidRegex = $value;
    }

    public function getInvalidRegex(): string
    {
        return $this->invalidRegex;
    }

    /*********************************************** */

    private function setContext(mixed $value): void
    {
        if (!$this->isOfType('string', $value)) {
            throw new Exception(
                Message::get("DEFINITION_INVALID_TYPE"),
                [
                    $this->getName(),
                    'context',
                    'string',
                    Tools::typeOf($value),
                ]
            );
        }

        if (empty(self::ALLOWED_CONFIG_CONTEXT) or !in_array($value, self::ALLOWED_CONFIG_CONTEXT, true)) {
            throw new Exception(
                Message::get("DEFINITION_INVALID_VALUE_IN_ARRAY"),
                [
                    $this->getName(),
                    'context',
                    Tools::readableImplode(', ', ' ou ', self::ALLOWED_CONFIG_CONTEXT),
                    $value,
                ]
            );
        }

        $this->context = $value;
    }

    public function getContext(): string
    {
        return $this->context;
    }

    /*********************************************** */

    public function setValue(mixed $value): void
    {
        if (!empty($this->getValidTypes()) and !$this->isOfType($this->getValidTypes(), $value)) {
            throw new Exception(
                Message::get("CONFIG_INVALID_TYPE"),
                [
                    $this->getName(),
                    Tools::readableImplode(', ', ' ou ', $this->getValidTypes()),
                    Tools::typeOf($value),
                ],
                [
                    'data' => [
                        'testedValue' => $value,
                    ]
                ]
            );
        }

        if (!empty($this->getInvalidTypes()) and $this->isOfType($this->getInvalidTypes(), $value)) {
            throw new Exception(
                Message::get("CONFIG_INVALID_TYPE_NOT"),
                [
                    $this->getName(),
                    Tools::readableImplode(', ', ' ou ', $this->getInvalidTypes()),
                ],
                [
                    'data' => [
                        'testedValue' => $value,
                    ]
                ]
            );
        }

        if (!empty($this->getValidValues()) and !in_array($value, $this->getValidValues(), true)) {
            throw new Exception(
                Message::get("CONFIG_INVALID_VALUE"),
                [
                    $this->getName(),
                    Tools::readableImplode(', ', ' ou ', $this->getValidValues()),
                    $value,
                ],
                [
                    'data' => [
                        'testedValue' => $value,
                    ]
                ]
            );
        }

        if (!empty($this->getInvalidValues()) and in_array($value, $this->getInvalidValues(), true)) {
            throw new Exception(
                Message::get("CONFIG_INVALID_VALUE_NOT"),
                [
                    $this->getName(),
                    Tools::readableImplode(', ', ' ou ', $this->getValidValues()),
                ],
                [
                    'data' => [
                        'testedValue' => $value,
                    ]
                ]
            );
        }


        if ($value !== '') {
            if (!empty($this->getInvalidRegex()) and preg_match($this->getInvalidRegex(), $value)) {
                throw new Exception(
                    Message::get("CONFIG_VALUE_MATCHED_REGEX"),
                    [
                        $this->getName(),
                        $value,
                    ],
                    [
                        'data' => [
                            'testedValue' => $value,
                        ]
                    ]
                );
            }

            if (!empty($this->getValidRegex()) and !preg_match($this->getValidRegex(), $value)) {
                throw new Exception(
                    Message::get("CONFIG_VALUE_MATCHED_REGEX_NOT"),
                    [
                        $this->getName(),
                        $value,
                        $this->getValidRegex(),
                    ],
                    [
                        'data' => [
                            'testedValue' => $value,
                        ]
                    ]
                );
            }
        }

        $this->value = $value;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }


    /*********************************************** */

    private function isOfType(array|string $types, mixed $value): bool
    {
        if (Tools::typeOf($types) !== 'array') {
            $types = [$types];
        }

        foreach ($types as $type) {
            if (Tools::typeOf($value) === $type) {
                return true;
                break;
            }
        }

        return false;
    }
}
