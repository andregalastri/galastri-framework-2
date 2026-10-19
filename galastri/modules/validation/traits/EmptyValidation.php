<?php
/**
 * Documentação:
 * https://github.com/andregalastri/galastri-framework-2/wiki/Trait-EmptyValidation
 */

namespace galastri\modules\validation\traits;

trait EmptyValidation
{
    public function denyNull(): self
    {
        $this->validationChain[] = function () {
            if ($this->value === null) {
                $this->throwError();
            }
        };

        return $this;
    }

    public function denyEmpty(): self
    {
        $this->validationChain[] = function () {
            $value = $this->value;

            if (is_string($value)) {
                $value = trim($value);
            }

            if ($value === null || $value === false || $value === '' || $value === []) {
                $this->throwError();
            }
        };

        return $this;
    }
}
