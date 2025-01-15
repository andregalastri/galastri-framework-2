<?php
/**
 * Documentação da classe:
 * https://github.com/andregalastri/galastri-framework-2/wiki/Classe-Definition
 */

namespace galastri\core\config;

use galastri\extensions\Exception;
use galastri\language\Message;
use galastri\modules\Tools;

final class Definition
{
    private const ALLOWED_CONFIG_TYPES = ['debug', 'route', 'node', 'endpoint'];

    private string $name;
    private mixed $value;
    private array $allowedTypes;
    private array $allowedValues;
    private string $configFile;
    private string $configType;
    private ?\Closure $execute;

    public function __construct(string $name, mixed $defaultValue, array $allowedTypes, array $allowedValues, string $configFile, string $configType, ?\Closure $execute = null)
    {
        $this->setName($name);
        $this->setAllowedTypes($allowedTypes);
        $this->setAllowedValues($allowedValues);
        $this->setConfigFile($configFile);
        $this->setConfigType($configType);
        $this->setValue($defaultValue);
        $this->setExecute($execute);
    }

    private function setName(mixed $value): void
    {
        if (!$this->isOfType('string', $value)) {
            throw new Exception(
                Message::WRONG_TYPE_CONFIG_DEFINITION,
                [
                    'name',
                    'string',
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

    private function setAllowedTypes(mixed $values): void
    {
        if (!$this->isOfType('array', $values)) {
            throw new Exception(
                Message::WRONG_TYPE_CONFIG_DEFINITION,
                [
                    'allowedTypes',
                    'array',
                    Tools::typeOf($values),
                ]
            );
        }

        foreach ($values as $value) {
            if (!$this->isOfType('string', $value)) {
                throw new Exception(
                    Message::WRONG_TYPE_CONFIG_DEFINITION_VALUE,
                    [
                        'allowedTypes',
                        'string',
                        Tools::typeOf($value),
                    ]
                );
            }
        }

        $this->allowedTypes = $values;
    }

    public function getAllowedTypes(): array
    {
        return $this->allowedTypes;
    }

    private function setAllowedValues(mixed $values): void
    {
        if (!$this->isOfType('array', $values)) {
            throw new Exception(
                Message::WRONG_TYPE_CONFIG_DEFINITION,
                [
                    'allowedValues',
                    'array',
                    Tools::typeOf($values),
                ]
            );
        }

        $this->allowedValues = $values;
    }

    public function getAllowedValues(): array
    {
        return $this->allowedValues;
    }

    public function setValue(mixed $value): void
    {
        if (!$this->isOfType($this->getAllowedTypes(), $value)) {
            throw new Exception(
                Message::WRONG_TYPE_CONFIG,
                [
                    $this->getName(),
                    implode(' | ', $this->getAllowedTypes()),
                    Tools::typeOf($value),
                ],
                [
                    'file' => $this->getConfigFile(),
                    'line' => 0,
                ]
            );
        }

        if (!empty($this->getAllowedValues()) and !in_array($value, $this->getAllowedValues(), true)) {
            throw new Exception(
                Message::INVALID_VALUE_CONFIG,
                [
                    $this->getName(),
                    implode(' | ', $this->getAllowedValues()),
                    $value,
                ]
            );
        }

        $this->value = $value;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    private function setConfigFile(mixed $value): void
    {
        if (!$this->isOfType('string', $value)) {
            throw new Exception(
                Message::WRONG_TYPE_CONFIG_DEFINITION,
                [
                    'configFile',
                    'string',
                    Tools::typeOf($value),
                ]
            );
        }

        $this->configFile = $value;
    }

    public function getConfigFile(): string
    {
        return $this->configFile;
    }

    private function setConfigType(mixed $value): void
    {
        if (!$this->isOfType('string', $value)) {
            throw new Exception(
                Message::WRONG_TYPE_CONFIG_DEFINITION,
                [
                    'configType',
                    'string',
                    Tools::typeOf($value),
                ]
            );
        }

        if (empty(self::ALLOWED_CONFIG_TYPES) or !in_array($value, self::ALLOWED_CONFIG_TYPES, true)) {
            throw new Exception(
                Message::INVALID_VALUE_CONFIG,
                [
                    'configType',
                    implode(' | ', self::ALLOWED_CONFIG_TYPES),
                    $value,
                ]
            );
        }

        $this->configType = $value;
    }

    public function getConfigType(): string
    {
        return $this->configFile;
    }

    private function setExecute(mixed $value): void
    {
        if (!$this->isOfType(['Closure', 'null'], $value)) {
            throw new Exception(
                Message::WRONG_TYPE_CONFIG_DEFINITION,
                [
                    'execute',
                    'Closure | null',
                    Tools::typeOf($value),
                ]
            );
        }

        $this->execute = $value;
    }

    public function execute(): void
    {
        if (!empty($this->execute)) {
            ($this->execute)($this);
        }
    }
    
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
