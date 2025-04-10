<?php
/**
 * Documentação:
 * https://github.com/andregalastri/galastri-framework-2/wiki/Classe-Config
 */

namespace galastri\core\config;

use galastri\core\config\Definition;
use galastri\extensions\Exception;
use galastri\language\Message;
use galastri\modules\Tools;

final class Config
{
    private const GALASTRI_DEFINITIONS = PROJECT_DIR.'/galastri/core/config/definitions/*.php';
    private const APP_DEFINITIONS = PROJECT_DIR.'/app/config/definitions/*.php';
    
    private const REQUIRED_PROPERTIES = ['defaultValue', 'validTypes', 'invalidTypes', 'validValues', 'invalidValues', 'validRegex', 'invalidRegex', 'context'];
    
    private const APP_CONFIG_FILE_DEBUG = PROJECT_DIR.'/app/config/debug.php';
    

    private static array $config = [];


    private function __construct() {}

    public static function run(): void
    {
        foreach(array_merge(glob(self::GALASTRI_DEFINITIONS), glob(self::APP_DEFINITIONS)) as $definitionFile) {
            $definitions = require($definitionFile);
        
            if (Tools::typeOf($definitions) !== 'array') {
                throw new Exception(
                    Message::get("CONFIG_FILE_RETURNED_INVALID_DATA"),
                    [
                        PROJECT_DIR.'/'.$definitionFile,
                    ]
                );
            }
        
            foreach ($definitions as $name => $definitions) {
                if (Tools::typeOf($definitions) !== 'array') {
                    throw new Exception(
                        Message::get("DEFINITION_INVALID_PROPERTY_VALUE"),
                        [
                            $name
                        ]
                    );
                }

                foreach (self::REQUIRED_PROPERTIES as $requiredProperty) {
                    if (!array_key_exists($requiredProperty, $definitions)) {
                        throw new Exception(
                            Message::get("DEFINITION_MISSING_REQUIRED_PROPERTY"),
                            [
                                $name,
                                $requiredProperty,
                            ]
                        );
                    }
                }
        
                self::create(
                    $name,
                    $definitions,
                );
            }
        }

        foreach(self::importConfig(self::APP_CONFIG_FILE_DEBUG) as $name => $value) {
            self::set($name, $value);
        }
    }

    private static function create(string $name, array $definitions): void
    {
        if (array_key_exists($name, self::list())) {
            throw new Exception(
                Message::get("DEFINITION_CONFIG_NAME_ALREADY_IN_USE"),
                [
                    $name,
                ]
            );
        }

        self::$config[$name] = new Definition($name, $definitions);
    }

    public static function set(string $name, mixed $value): void
    {
        self::checkPropertyExists($name);
        self::$config[$name]->setValue($value);
    }

    public static function get(...$parameters): mixed
    {
        try {
            $name = $parameters[0];
    
            self::checkPropertyExists($name);
            return self::$config[$name]->getValue();
    
        } catch (Exception $e) {
            if (isset($parameters[1])) {
                return $parameters[1];
            }
    
            throw new Exception($e->getMessage());
        }
    }

    public static function list(): array
    {
        return self::$config;
    }

    public static function listValues(): array
    {
        $valueList = [];

        foreach (self::$config as $propertyName => $properties) {
            $valueList[$propertyName] = $properties->getValue();
        }

        return $valueList;
    }

    private static function checkPropertyExists(string $name): void
    {
        if (!array_key_exists($name, self::list())) {
            throw new Exception(
                Message::get("CONFIG_DOESNT_EXIST"),
                [
                    $name,
                ]
            );
        }
    }

    public static function importConfig(string $path): array
    {
        if (!file_exists($path)) {
            throw new Exception(
                Message::get("CONFIG_FILE_NOT_FOUND"),
                [
                    $path
                ]
            );
        }

        return require($path);
    }
}
