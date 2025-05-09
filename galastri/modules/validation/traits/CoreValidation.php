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
    private array $messageData = [];

    public function __construct()
    {
        $this->failMessage = Message::get('VALIDATION_FAIL');
    }

    public function withMessage(string $message, int|null|string $code = null): self
    {
        $this->validationChain[] = function () use ($message, $code) {
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

        throw new Exception(
            $this->failMessage, $this->messageData
        );
    }
}
