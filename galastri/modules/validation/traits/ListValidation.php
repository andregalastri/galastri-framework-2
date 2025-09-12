<?php
/**
 * Documentação:
 * https://github.com/andregalastri/galastri-framework-2/wiki/Trait-ListValidation
 */

namespace galastri\modules\validation\traits;

use galastri\modules\Tools;

trait ListValidation
{
    public function restrictList(int|string ...$valueList): self
    {
        $this->validationChain[] = function () use ($valueList) {
            $value = $this->value;
            $valueList = Tools::arrayFlatten($valueList);
    
            if (!in_array($value, $valueList, true)) {
                $this->messageFlagValues = [$value, implode(', ', $valueList)];
                $this->throwError();
            }
        };

        return $this;
    }
}
