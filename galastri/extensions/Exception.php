<?php
/**
 * Documentação:
 * https://github.com/andregalastri/galastri-framework-2/wiki/Classe-Exception
 */

namespace galastri\extensions;

use galastri\language\Message;
use galastri\modules\Tools;

class Exception extends \Exception
{
    protected array $data = [];
    private static string $testedMessage = '';
    private static bool $testedHasFlags = false;

    public function __construct()
    {
        $this->__overload(func_get_args());
        parent::__construct($this->getMessage());
    }

    public static function hasFlags(string $message): bool
    {
        self::$testedMessage = $message;
        self::$testedHasFlags = preg_match(Tools::FLAG_REPLACER_REGEX_SEQUENTIAL, $message) || preg_match(Tools::FLAG_REPLACER_REGEX_POSITIONAL, $message);

        return self::$testedHasFlags;
    }

    private function __overload(array $parameters): void
    {
        if (!empty($parameters)) {
            if (Tools::typeOf($parameters[0]) === 'array') {
                if (count($parameters[0]) < 2) {
                    throw new Exception(Message::get("EXCEPTION_INVALID_ARRAY"));
                }

                if (self::$testedMessage == $parameters[0][0] && self::$testedHasFlags || self::hasFlags($parameters[0][0])) {
                    $this->processArrayWithFlags($parameters[0], $parameters[1] ?? [], $parameters[2] ?? null);
                    $this->processArrayWithFlags($parameters[0], $parameters[1] ?? [], $parameters[2] ?? null);
                } else {
                    $this->processSimpleArray($parameters[0], $parameters[1] ?? null);
                }
            } else {
                if (self::$testedMessage == $parameters[0][0] && self::$testedHasFlags || self::hasFlags($parameters[0][0])) {
                    $this->processStringWithFlags($parameters[0], $parameters[1] ?? [], $parameters[2] ?? null, $parameters[3] ?? null);
                } else {
                    $this->processSimpleString($parameters[0], $parameters[1] ?? null, $parameters[2] ?? null);
                }
            }
        }
    }

    private function processArrayWithFlags(array $data, array $printFArgs, ?array $additionalData): void
    {
        $this->setMessage(Tools::flagReplace($data[0], $printFArgs));
        $this->setCode($data[1]);
        $this->setAdditionalData($additionalData);
    }

    private function processSimpleArray(array $data, ?array $additionalData): void
    {
        $this->setMessage($data[0]);
        $this->setCode($data[1]);
        $this->setAdditionalData($additionalData);
    }
    
    private function processStringWithFlags(string $message, array $printFArgs, array|int|null|string $code, ?array $additionalData): void
    {
        $this->setMessage(Tools::flagReplace($message, $printFArgs));
        
        if ($code !== null && Tools::typeOf($code) !== 'array') {
            $this->setCode($code);
        }
        $this->setAdditionalData(Tools::typeOf($code) === 'array' ? $code : $additionalData);
    }

    private function processSimpleString(string $message, array|int|null|string $code, ?array $additionalData): void
    {
        $this->setMessage($message);

        if ($code !== null && Tools::typeOf($code) !== 'array') {
            $this->setCode($code);
        }
        $this->setAdditionalData(Tools::typeOf($code) === 'array' ? $code : $additionalData);
    }

    private function setMessage(string $message): void
    {
        $this->message = $message;
    }

    private function setCode(int|string $code): void
    {
        if (Tools::typeOf($code) === 'int' || Tools::typeOf($code) === 'string') {
            $this->code = $code;
        } else {
            throw new Exception(Message::get("EXCEPTION_INVALID_CODE"), [Tools::typeOf($code)]);
        }
    }

    private function setAdditionalData(?array $additionalData){
        if (($additionalData['line'] ?? false) !== false) {
            $this->setLine($additionalData['line']);
            unset($additionalData['line']);
        }
        
        if (($additionalData['file'] ?? false) !== false) {
            $this->setFile($additionalData['file']);
            unset($additionalData['file']);
        }

        $this->setData($additionalData ?? []);
    }

    private function setLine(int $line): void
    {
        $this->line = $line;
    }

    private function setFile(string $file): void
    {
        $this->file = $file;
    }

    private function setData(array $data): void
    {
        $this->data = $data;
    }

    public function getData(int|null|string $key = null) // : mixed
    {
        return $key === null ? $this->data : $this->data[$key];
    }
}
