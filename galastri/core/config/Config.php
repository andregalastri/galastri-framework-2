<?php
/**
 * Documentação da classe:
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
    
    private const REQUIRED_PROPERTIES = ['defaultValue', 'allowedTypes', 'allowedValues', 'configFile', 'configType', 'execute'];
    
    private const APP_CONFIG_FILES = [
        PROJECT_DIR.'/app/config/debug.php',
    ];
    

    private static array $config = [];

    private function __construct() {}

    public static function run(): void
    {
        foreach(array_merge(glob(self::GALASTRI_DEFINITIONS), glob(self::APP_DEFINITIONS)) as $definitionFile) {
            $definitions = require($definitionFile);
        
            if (Tools::typeOf($definitions) !== 'array') {
                throw new Exception(
                    Message::INVALID_CONFIG_FILE,
                    [
                        PROJECT_DIR.'/'.$definitionFile,
                        Tools::typeOf($definitions)
                    ]
                );
            }
        
            foreach ($definitions as $name => $config) {
                if (Tools::typeOf($config) !== 'array') {
                    throw new Exception(
                        Message::CONFIG_DEFINITION_NEEDS_TO_BE_ARRAY,
                        [
                            $name
                        ]
                    );
                }

                foreach (self::REQUIRED_PROPERTIES as $requiredProperty) {
                    if (!array_key_exists($requiredProperty, $config)) {
                        throw new Exception(
                            Message::CONFIG_DEFINITION_MISSING_REQUIRED_PROPERTY,
                            [
                                $requiredProperty,
                                $name,
                                PROJECT_DIR.'/'.$definitionFile
                            ]
                        );
                    }
                }
        
                self::create(
                    $name,
                    $config['defaultValue'],
                    $config['allowedTypes'],
                    $config['allowedValues'],
                    $config['configFile'],
                    $config['configType'],
                    $config['execute']
                );
            }
        }

        foreach(self::APP_CONFIG_FILES as $path) {
            foreach(self::importConfig($path) as $name => $config) {
                self::set($name, $config);
            }
        }
    }

    private static function create(string $name, mixed $defaultValue, array $allowedTypes, array $allowedValues, string $configFile, string $configType, ?\Closure $execute = null): void
    {
        if (array_key_exists($name, self::list())) {
            throw new Exception(
                Message::CONFIG_DEFINITION_PROPERTY_ALREADY_EXISTS,
                [
                    $name,
                ]
            );
        }
        self::$config[$name] = new Definition($name, $defaultValue, $allowedTypes, $allowedValues, $configFile, $configType, $execute);
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

    public static function execute(string $name): void
    {
        self::$config[$name]->execute();
    }

    private static function checkPropertyExists(string $name): void
    {
        if (!array_key_exists($name, self::list())) {
            throw new Exception(
                Message::CONFIG_DEFINITION_PROPERTY_UNDEFINED,
                [
                    $name,
                ]
            );
        }
    }

    private static function importConfig(string $path): array
    {
        if (!file_exists($path)) {
            throw new Exception(
                Message::NOT_FOUND_CONFIG_FILE,
                [
                    $path
                ]
            );
        }

        return require($path);
    }
}
