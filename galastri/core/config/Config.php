<?php
/**
 * -----------------------------------------------------------------------------
 * 📂
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                 ARQUIVO /galastri/core/config/Config.php                  ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 * xxx
 * ─────────────────────────────────────────────────────────────────────────────
 */



/**
 * 🟢 NAMESPACE DA CLASSE
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Config pertence ao núcleo do framework.
 */

namespace galastri\core\config;



/**
 * 🟢 CLASSES UTILIZADAS
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * xxx
 *
 * ┌────────┬──────────────────────────────────────────────────────────────────┐
 * │ Classe │ Uso                                                              │
 * ├────────┼──────────────────────────────────────────────────────────────────┤
 * │ `xxx`  │ xxx                                                              │
 * └────────┴──────────────────────────────────────────────────────────────────┘
 */

use galastri\core\config\Definition;
use galastri\extensions\Exception;
use galastri\language\Message;
use galastri\modules\Tools;



/**
 * 🟢 CLASSE Config
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * xxx
 */

final class Config
{
    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                              CONSTANTES                               ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     */
    
    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * _________________________________
     * @var xxx
     */
    
    private const GALASTRI_DEFINITIONS =
        PROJECT_DIR.'/galastri/core/config/definitions/*.php';
        
    
        
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * _________________________________
     * @var xxx
     */
    
    private const APP_DEFINITIONS = PROJECT_DIR.'/app/config/definitions/*.php';

    
    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * _________________________________
     * @var xxx
     */
    
    private const REQUIRED_PROPERTIES = [
        'defaultValue',
        'validTypes',
        'invalidTypes',
        'validValues',
        'invalidValues',
        'validRegex',
        'invalidRegex',
        'context',
    ];


    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                             PROPRIEDADES                              ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     */
    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * _________________________________
     * @var xxx
     */
    
    private static array $config = [];

    
    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                                MÉTODOS                                ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     */
    
    
    
    /**
     * 🟢 CLASSE ESTÁTICA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Os recursos desta classe são acessados diretamente por chamadas como
     * `Config::set($valor)`, sem precisar criar um objeto.
     *
     * Por isso, deixamos o construtor privado para impedir que a classe seja
     * instanciada com `new Config()`.
     */
    
    private function __construct() {}
    
    
    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * _________________________________
     * @return void
     */
    
    public static function run(): void
    {
        $galastriConfigDefinitions = glob(self::GALASTRI_DEFINITIONS);
        $appConfigDefinitions = glob(self::APP_DEFINITIONS);
        
        $configDefinitions = array_merge(
            $galastriConfigDefinitions, $appConfigDefinitions
        );
        
        foreach($configDefinitions as $definitionFile) {
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
    }

    
    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * _________________________________
     * @param xxx $xxx  
     *    xxx
     * @param xxx $xxx  
     *    xxx
     * _________________________________
     * @return void
     */
    
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

    
    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * _________________________________
     * @param xxx $xxx  
     *    xxx
     * @param xxx $xxx  
     *    xxx
     * _________________________________
     * @return void
     */
    
    public static function set(string $name, mixed $value): void
    {
        self::exists($name, true);
        self::$config[$name]->setValue($value);
    }

    
    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * _________________________________
     * @param xxx $xxx  
     *    xxx
     * _________________________________
     * @return xxx
     *    xxx
     */
    
    public static function get(...$parameters): mixed
    {
        try {
            $name = $parameters[0];

            self::exists($name, true);
            return self::$config[$name]->getValue();

        } catch (Exception $e) {
            if (array_key_exists(1, $parameters)) {
                return $parameters[1];
            }

            throw $e;
        }
    }

    
    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * _________________________________
     * @return xxx
     *    xxx
     */
    
    public static function list(): array
    {
        return self::$config;
    }

    
    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * _________________________________
     * @return xxx
     *    xxx
     */
    
    public static function listValues(): array
    {
        $valueList = [];

        foreach (self::$config as $propertyName => $properties) {
            $valueList[$propertyName] = $properties->getValue();
        }

        return $valueList;
    }

    
    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * _________________________________
     * @param xxx $xxx  
     *    xxx
     * @param xxx $xxx  
     *    xxx
     * _________________________________
     * @return xxx
     *    xxx
     */
    
    public static function exists(string $name, bool $throwException = false): bool
    {
        if (array_key_exists($name, self::list())) {
            return true;
        }

        if ($throwException) {
            throw new Exception(
                Message::get("CONFIG_DOESNT_EXIST"),
                [
                    $name,
                ]
            );
        }

        return false;
    }

    
    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * _________________________________
     * @param xxx $xxx  
     *    xxx
     * @param xxx $xxx  
     *    xxx
     * _________________________________
     * @return xxx
     *    xxx
     */
    
    public static function notExists(string $name, bool $throwException = false): bool
    {
        if (!array_key_exists($name, self::list())) {
            return true;
        }

        if ($throwException) {
            throw new Exception(
                Message::get("CONFIG_EXISTS"),
                [
                    $name,
                ]
            );
        }

        return false;
    }

    
    
    /**
     * 🟢 xxx
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * xxx
     *
     *
     * _________________________________
     * @param xxx $xxx  
     *    xxx
     * _________________________________
     * @return xxx
     *    xxx
     */
    
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
