<?php
/**
 * Documentação:
 * https://github.com/andregalastri/galastri-framework-2/wiki/Classe-NumericValidation
 */

namespace galastri\modules\validation;

final class NumericValidation
{
    use traits\CoreValidation;
    use traits\EmptyValidation;
    use traits\ListValidation;

    public function minValue(int|float $minValue): self
    {
        $this->validationChain[] = function () use ($minValue) {
            $value = $this->value;

            if ($value < $minValue) {
                $this->messageFlagValues = [$value, $minValue];
                $this->throwError();
            }
        };

        return $this;
    }

    public function maxValue(int|float $maxValue): self
    {
        $this->validationChain[] = function () use ($maxValue) {
            $value = $this->value;
            
            if ($value > $maxValue) {
                $this->messageFlagValues = [$value, $maxValue];
                $this->throwError();
            }
        };

        return $this;
    }

    public function valueRange(int $minValue, int $maxValue): self
    {
        $this->validationChain[] = function () use ($minLength, $maxLength) {
            $value = $this->value;
            
            if ($value < $minValue || $value > $maxValue) {
                $this->messageFlagValues = [$value, $minValue, $maxValue];
                $this->throwError();
            }
        };

        return $this;
    }

    public function denyFloat(): self
    {
        $this->validationChain[] = function () {
            $value = $this->value;

            if (is_float($value)) {
                $this->messageFlagValues = [$value];
                $this->throwError();
            }
        };

        return $this;
    }

    public function denyZero(): self
    {
        $this->validationChain[] = function () {
            $value = (int)$this->value;

            if ($value === 0) {
                $this->messageFlagValues = [$value];
                $this->throwError();
            }
        };
        
        return $this;
    }

    public function denyNegative(): self
    {
        $this->validationChain[] = function () {
            $value = (int)$this->value;

            if ($value < 0) {
                $this->messageFlagValues = [$value];
                $this->throwError();
            }
        };
        
        return $this;
    }
}
