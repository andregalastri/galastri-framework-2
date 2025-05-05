<?php

namespace galastri\modules\validation;

use galastri\extensions\Exception;
use galastri\language\Message;

final class StringValidation
{
    use traits\CoreValidation;
    use traits\EmptyValidation;
    use traits\ListValidation;

    const LOWER_ACCENTED_CHARS = 'àáâãäåçèéêëìíîïñòóôõöùúûüýÿŕ';
    const UPPER_ACCENTED_CHARS = 'ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝŸŔ';
    const LOWER_EXTENDED_ACCENTED_CHARS = 'ẃṕśǵḱĺźǘńḿẁỳǜǹẽỹũĩṽŵŷŝĝĥĵẑẅẗḧẍæœ';
    const UPPER_EXTENDED_ACCENTED_CHARS = 'ẂṔŚǴḰĹŹǗŃḾẀỲǛǸẼỸŨĨṼŴŶŜĜĤĴẐẄT̈ḦẌÆŒ';
    const SPECIAL_CHARS = "¹²³£¢¬º\\\\\/\-,.!@#$%\"'&*()_°ª+=\[\]{}^~`?<>:;";

    const CHAR_FLAGS = [
        '--numbers' => '0-9',
        '--numbersUtf8' => '\p{Nl}',
        '--letters' => 'a-zA-Z',
        '--lettersUtf8' => '\p{L}',
        '--upperLetters' => 'A-Z',
        '--upperLettersUtf8' => '\p{Lu}',
        '--lowerLetters' => 'a-z',
        '--lowerLettersUtf8' => '\p{Ll}',
        '--specialChars' => self::SPECIAL_CHARS,
        '--accentedChars' => self::UPPER_ACCENTED_CHARS.self::LOWER_ACCENTED_CHARS,
        '--upperAccentedChars' => self::UPPER_ACCENTED_CHARS,
        '--lowerAccentedChars' => self::LOWER_ACCENTED_CHARS,
        '--extendedAccentedChars' => self::UPPER_EXTENDED_ACCENTED_CHARS.self::UPPER_EXTENDED_ACCENTED_CHARS,
        '--upperExtendedAccentedChars' => self::UPPER_EXTENDED_ACCENTED_CHARS,
        '--lowerExtendedAccentedChars' => self::UPPER_EXTENDED_ACCENTED_CHARS,
        '--spaces' => '\s',
    ];

    public function minLength(int $length): self
    {
        $this->validationChain[] = function () use ($length) {
            $value = $this->value;

            if (mb_strlen($value) < $length) {
                $this->messageData = [$value, $length, mb_strlen($value)];
                $this->throwError();
            }
        };

        return $this;
    }

    public function maxLength(int $length): self
    {
        $this->validationChain[] = function () use ($length) {
            $value = $this->value;
            
            if (mb_strlen($value) > $length) {
                $this->messageData = [$value, $length, mb_strlen($value)];
                $this->throwError();
            }
        };

        return $this;
    }

    public function lengthRange(int $minLength, int $maxLength): self
    {
        $this->validationChain[] = function () use ($minLength, $maxLength) {
            $value = $this->value;
            
            if (mb_strlen($value) < $minLength || mb_strlen($value) > $maxLength) {
                $this->messageData = [$value, $minLength, $maxLength, mb_strlen($value)];
                $this->throwError();
            }
        };

        return $this;
    }

    public function allowChars(string ...$chars): self
    {
        return $this->validateCharset('allowChars', ...$chars);
    }

    public function denyChars(string ...$chars): self
    {
        return $this->validateCharset('denyChars', ...$chars);
    }

    public function allowWords(string ...$words): self
    {
        return $this->validateCharset('allowWords', ...$words);
    }

    public function denyWords(string ...$words): self
    {
        return $this->validateCharset('denyWords', ...$words);
    }

    public function requiredChars(mixed ...$charGroups): self
    {
        if (empty($charGroups)) {
            throw new Exception(
                Message::get("VALIDATION_WRONG_CONFIG"),
                [
                    'requiredChars',
                ]
            );
        }

        if (count($charGroups) === 2 && is_string($charGroups[0]) && (is_int($charGroups[1]) || ctype_digit((string) $charGroups[1]))) {
            $charGroups = [[$charGroups[0], (int) $charGroups[1]]];
        }

        $this->validationChain[] = function () use ($charGroups) {
            $value = $this->value;

            foreach ($charGroups as $group) {
                if (!is_array($group) || count($group) !== 2 || !is_string($group[0]) || !is_int($group[1])) {
                    throw new Exception(
                        Message::get("VALIDATION_INVALID_REQUIRED_CHAR_CONFIG"),
                    );
                }

                [$dataset, $minimum] = $group;

                $dataset = self::CHAR_FLAGS[$dataset] ?? $dataset;

                $regex = '/['.$dataset.']/u';
                preg_match_all($regex, $value, $matches);

                $count = count($matches[0] ?? []);

                if ($count < $minimum) {
                    $this->messageData = [$value, $dataset, $minimum, $count];
                    $this->throwError();
                }
            }
        };

        return $this;
    }

    public function denyUpperCase(): self
    {
        return $this->validateCase('upperCase');
    }

    public function denyLowerCase(): self
    {
        return $this->validateCase('lowerCase');
    }

    public function isCpf(): self
    {
        $this->validationChain[] = function () {
            $value = $this->value;

            $isFormatted = preg_match('/^\d{3}\.\d{3}\.\d{3}-\d{2}$/', $value);
            $isNumericOnly = preg_match('/^\d{11}$/', $value);

            if (!$isFormatted && !$isNumericOnly) {
                $this->messageData = [$value];
                $this->throwError();
            }

            $digits = preg_replace('/\D/', '', $value);

            if (preg_match('/^(\d)\1{10}$/', $digits)) {
                $this->messageData = [$value];
                $this->throwError();
            }

            for ($position = 9; $position < 11; $position++) {
                $sum = 0;

                for ($i = 0; $i < $position; $i++) {
                    $weight = ($position + 1) - $i;
                    $sum += $digits[$i] * $weight;
                }

                $digit = (10 * $sum) % 11;
                $digit = $digit % 10;

                if ((int) $digits[$position] !== $digit) {
                    $this->messageData = [$value];
                    $this->throwError();
                }
            }
        };

        return $this;
    }

    public function isCnpj(): self
    {
        $this->validationChain[] = function () {
            $value = $this->value;

            // Verifica se está no formato pontuado ##.###.###/####-## ou apenas 14 dígitos
            $isFormatted = preg_match('/^\d{2}\.\d{3}\.\d{3}\/\d{4}-\d{2}$/', $value);
            $isNumericOnly = preg_match('/^\d{14}$/', $value);

            if (!$isFormatted && !$isNumericOnly) {
                $this->messageData = [$value];
                $this->throwError();
            }

            $digits = preg_replace('/\D/', '', $value);

            if (preg_match('/^(\d)\1{13}$/', $digits)) {
                $this->messageData = [$value];
                $this->throwError();
            }

            $weights = [
                [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2],
                [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2],
            ];

            for ($round = 0; $round < 2; $round++) {
                $sum = 0;
                $limit = 12 + $round;

                for ($i = 0; $i < $limit; $i++) {
                    $sum += $digits[$i] * $weights[$round][$i];
                }

                $digit = ($sum % 11 < 2) ? 0 : 11 - ($sum % 11);

                if ((int) $digits[$limit] !== $digit) {
                    $this->messageData = [$value];
                    $this->throwError();
                }
            }
        };

        return $this;
    }

    public function isEmail(): self
    {
        $this->validationChain[] = function () {
            $value = $this->value;

            if (!is_string($value) || !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $this->messageData = [$value];
                $this->throwError();
            }
        };

        return $this;
    }

    public function isPhone(): self
    {
        $this->validationChain[] = function () {
            $value = $this->value;

            if (!is_string($value)) {
                $this->messageData = [$value];
                $this->throwError();
            }

            $patternList = [
                '/^(\+55\s?)?(\(?\d{2}\)?\s?)?9\d{4}[-\s]?\d{4}$/',
                '/^(\+55\s?)?(\(?\d{2}\)?\s?)?[2-5]\d{3}[-\s]?\d{4}$/',
                '/^0[3-9]00[-\s]?\d{3}[-\s]?\d{4}$/',
            ];

            $valid = false;

            foreach ($patternList as $pattern) {
                if (preg_match($pattern, $value)) {
                    $valid = true;
                    break;
                }
            }

            if (!$valid) {
                $this->messageData = [$value];
                $this->throwError();
            }
        };

        return $this;
    }


    private function validateCharset(string $mode, string ...$datasets): self
    {
        $modeList = [
            'allowChars' => '/[^'.implode($datasets).']/u',
            'denyChars' => '/['.implode($datasets).']/u',
            'allowWords' => '/^(?:'.implode('|', $datasets).')$/u',
            'denyWords'  => '/(?:'.implode('|', $datasets).')/u',
        ];

        if (!isset($modeList[$mode])) {
            throw new Exception(
                Message::get("VALIDATION_INVALID_MODE"),
                [
                    $mode,
                ]
            );
        }

        if (empty($datasets)) {
            throw new Exception(
                Message::get("VALIDATION_WRONG_CONFIG"),
                [
                    $mode,
                ]
            );
        }

        $this->validationChain[] = function () use ($mode, $datasets, $modeList) {
            $value = $this->value;

            foreach ($datasets as &$data) {
                $data = self::CHAR_FLAGS[$data] ?? preg_quote($data);
            }
            unset($data);
            
            preg_match_all($modeList[$mode], $value, $matches);

            if (!empty($matches[0])) {
                $this->messageData = [$value, implode(', ', array_unique($matches[0]))];
                $this->throwError();
            }
        };

        return $this;
    }

    private function validateCase(string $mode): self
    {
        $modeList = [
            'upperCase' => '/[^\p{Ll}]/u',
            'lowerCase' => '/[^\p{Lu}]/u',
        ];

        if (!isset($modeList[$mode])) {
            throw new Exception(
                Message::get("VALIDATION_INVALID_MODE"),
                [
                    $mode,
                ]
            );
        }

        $this->validationChain[] = function () use ($mode, $modeList) {
            $value = $this->value;

            preg_match_all('/[\p{L}]/u', $value, $allLetters);

            if (empty($allLetters[0])) {
                return;
            }

            preg_match_all($modeList[$mode], implode($allLetters[0]), $unmatches);

            if (!empty($unmatches[0])) {
                $this->messageData = [$value, implode(', ', array_unique($unmatches[0]))];
                $this->throwError();
            }
        };

        return $this;
    }
}
