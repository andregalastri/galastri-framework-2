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
            $value = trim($this->value);

            if ($value === null || $value === '' || (is_array($value) && count($value) === 0)) {
                $this->throwError();
            }
        };

        return $this;
    }
}
