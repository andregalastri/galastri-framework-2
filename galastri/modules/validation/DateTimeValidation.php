<?php
/**
 * Documentação:
 * https://github.com/andregalastri/galastri-framework-2/wiki/Classe-DateTimeValidation
 */

namespace galastri\modules\validation;

use galastri\language\Message;
use \DateTime;

final class DateTimeValidation
{
    use traits\CoreValidation {
        traits\CoreValidation::validate as private parentValidate;
    }
    use traits\EmptyValidation;
    use traits\ListValidation;

    private string $format;

    public function minDateTime(DateTime|string $minDateTime, ?string $format = null): self
    {
        $this->validationChain[] = function () use ($minDateTime, $format) {
            $valueFormat = $this->format;
            $minFormat = $format ?? $valueFormat;

            $value = self::createDateTimeFromString($this->value, $valueFormat);
            $minDateTime = self::createDateTimeFromString($minDateTime, $minFormat);

            if ($value < $minDateTime) {
                $this->messageFlagValues = [$value->format($valueFormat), $minDateTime->format($valueFormat)];
                $this->throwError();
            }
        };

        return $this;
    }

    public function maxDateTime(DateTime|string $maxDateTime, ?string $format = null): self
    {
        $this->validationChain[] = function () use ($maxDateTime, $format) {
            $valueFormat = $this->format;
            $maxFormat = $format ?? $valueFormat;

            $value = self::createDateTimeFromString($this->value, $valueFormat);
            $maxDateTime = self::createDateTimeFromString($maxDateTime, $maxFormat);
            
            if ($value > $maxDateTime) {
                $this->messageFlagValues = [$value->format($valueFormat), $maxDateTime->format($valueFormat)];
                $this->throwError();
            }
        };

        return $this;
    }
   
    public function dateTimeRange(DateTime|string $minDateTime, DateTime|string $maxDateTime, ?string $format = null): self
    {
        $this->validationChain[] = function () use ($minDateTime, $maxDateTime) {
            $valueFormat = $this->format;
            $rangeFormat = $format ?? $inputFormat;

            $value = self::createDateTimeFromString($this->value, $valueFormat);
            $minDateTime = self::createDateTimeFromString($minDateTime, $rangeFormat);
            $maxDateTime = self::createDateTimeFromString($maxDateTime, $rangeFormat);
            
            if ($value < $minDateTime || $value > $maxDateTime) {
                $this->messageFlagValues = [$value->format($valueFormat), $minDateTime->format($valueFormat), $maxDateTime->format($valueFormat)];
                $this->throwError();
            }
        };

        return $this;
    }

    private function createDateTimeFromString(DateTime|string $value, ?string $format = null): DateTime
    {
        if ($value instanceof DateTime) {
            return $value;
        }

        $format = $format ?? $this->format ?? 'Y-m-d H:i:s';

        try {
            $dateTime = DateTime::createFromFormat('!'.$format, $value);
            $errors = DateTime::getLastErrors();
        } catch (\ValueError) {
            $dateTime = false;
            $errors = false;
        }

        if (
            $dateTime !== false
            && (
                $errors === false
                || ($errors['warning_count'] === 0 && $errors['error_count'] === 0)
            )
        ) {
            return $dateTime;
        }

        $this->messageFlagValues = [$value, $format];
        $this->failMessage = Message::get('VALIDATION_INVALID_DATETIME');
        $this->throwError();
    }

    public function validate(mixed $value, string $format = 'Y-m-d H:i:s'): void
    {
        $this->format = $format;
        $this->parentValidate($value);
    }
}
