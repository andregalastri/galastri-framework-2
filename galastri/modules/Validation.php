<?php
/**
 * Documentação:
 * https://github.com/andregalastri/galastri-framework-2/wiki/Classe-Validation
 */

namespace galastri\modules;

use galastri\modules\validation\StringValidation;
use galastri\modules\validation\NumericValidation;
use galastri\modules\validation\DateTimeValidation;

final class Validation
{
    public static bool $displayError = false;

    private function __construct() {}

    public static function string(): StringValidation
    {
        return new StringValidation();
    }

    public static function number(): NumericValidation
    {
        return new NumericValidation();
    }

    public static function dateTime(): DateTimeValidation
    {
        return new DateTimeValidation();
    }
}
