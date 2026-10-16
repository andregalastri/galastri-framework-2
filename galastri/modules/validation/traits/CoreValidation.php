<?php
/**
 * Documentação:
 * https://github.com/andregalastri/galastri-framework-2/wiki/Trait-CoreValidation
 */

namespace galastri\modules\validation\traits;

use galastri\extensions\Exception;
use galastri\language\Message;
use galastri\modules\Validation;

trait CoreValidation
{
    private array $validationChain = [];
    private mixed $value = null;
    private array $failMessage;
    private array $messageFlagValues = [];
    private array $additionalData = [];

    public function __construct()
    {
        $this->failMessage = Message::get('VALIDATION_FAIL');
    }

    public function onError(string $message, int|null|string $code = null, $additionalData = []): self
    {
        $this->validationChain[] = function () use ($message, $code, $additionalData) {
            $this->additionalData = $additionalData;
            $this->failMessage = [$message, $code ?? $this->failMessage[1]];
        };

        return $this;
    }

    public function validate(mixed $value): void
    {
        $this->value = $value;

        if (empty($this->validationChain)) {
            return;
        }

        foreach (array_reverse($this->validationChain) as $validation) {
            $validation();
        }
    }

    private function throwError(): void
    {
        Validation::$displayError = true;

        if (Exception::hasFlags($this->failMessage[0])) {
            throw new Exception(
                $this->failMessage, $this->messageFlagValues, $this->additionalData
            );
        }
        
        throw new Exception(
            $this->failMessage, $this->additionalData
        );
    }
}
