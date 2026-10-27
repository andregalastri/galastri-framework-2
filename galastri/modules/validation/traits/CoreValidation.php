<?php
/**
 * Documentação:
 * https://github.com/andregalastri/galastri-framework-2/wiki/Trait-CoreValidation
 */

namespace galastri\modules\validation\traits;

use galastri\extensions\Exception;
use galastri\language\Message;

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
        if (Exception::hasPlaceholders($this->failMessage[0])) {
            $exception = new Exception(
                $this->failMessage,
                $this->messageFlagValues,
                $this->additionalData
            );
        } else {
            $exception = new Exception(
                $this->failMessage,
                $this->additionalData
            );
        }

        $exception->displayValidationError = true;
        $exception->errorSource = 'validation';

        throw $exception;
    }
}
