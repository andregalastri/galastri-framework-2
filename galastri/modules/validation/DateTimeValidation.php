<?php

namespace galastri\modules\validation;

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
                $this->messageData = [$value->format($valueFormat), $minDateTime->format($valueFormat)];
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
                $this->messageData = [$value->format($valueFormat), $maxDateTime->format($valueFormat)];
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
                $this->messageData = [$value->format($valueFormat), $minDateTime->format($valueFormat), $maxDateTime->format($valueFormat)];
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

        if (is_string($value)) {
            try {
                $dateTime = new DateTime($value);     
                return $dateTime;
            } catch (\Exception) {
                $dateTime = DateTime::createFromFormat($format, $value);
                $errors = DateTime::getLastErrors();

                if (!$errors) {
                    return $dateTime;
                }
            }
        }

        $this->messageData = [$value, $format];
        $this->failMessage = Message::get('VALIDATION_INVALID_DATETIME');
        $this->throwError();
    }

    public function validate(mixed $value, string $format = 'Y-m-d H:i:s'): void
    {
        $this->format = $format;
        $this->parentValidate($value);
    }
}
