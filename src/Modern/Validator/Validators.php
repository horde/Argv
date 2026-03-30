<?php

declare(strict_types=1);

/**
 * Copyright 2024-2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @author   Ralf Lang <ralf.lang@ralf-lang.de>
 * @category Horde
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Argv
 */

namespace Horde\Argv\Modern\Validator;

/**
 * Common validators for convenience.
 *
 * Following Principle #3: Provide Infrastructure, Don't Mandate Usage
 * These are OPTIONAL helpers, not required for parser operation.
 *
 * All validators return true on success, or a string error message on failure.
 * This allows composable validation with clear error messages.
 *
 * Usage:
 *   ->validator(Validators::range(1, 100))
 *   ->validator(Validators::file(mustExist: true))
 *   ->validator(Validators::all(
 *       Validators::minLength(3),
 *       Validators::maxLength(20)
 *   ))
 *
 * @category Horde
 * @package  Argv
 */
class Validators
{
    /**
     * Validate value is within range (inclusive).
     *
     * @param int|float $min Minimum value (inclusive)
     * @param int|float $max Maximum value (inclusive)
     * @return callable Validator function
     */
    public static function range(int|float $min, int|float $max): callable
    {
        return function (mixed $value) use ($min, $max): bool|string {
            if ($value < $min || $value > $max) {
                return "Value must be between {$min} and {$max}";
            }
            return true;
        };
    }

    /**
     * Validate value matches regex pattern.
     *
     * @param string $pattern Regex pattern (with delimiters)
     * @param string $description Optional human-readable description
     * @return callable Validator function
     */
    public static function regex(string $pattern, string $description = ''): callable
    {
        return function (mixed $value) use ($pattern, $description): bool|string {
            if (!preg_match($pattern, (string) $value)) {
                $msg = $description ?: "Value must match pattern {$pattern}";
                return $msg;
            }
            return true;
        };
    }

    /**
     * Validate file exists and optionally is readable.
     *
     * @param bool $mustExist Whether file must exist
     * @param bool $mustBeReadable Whether file must be readable
     * @return callable Validator function
     */
    public static function file(bool $mustExist = true, bool $mustBeReadable = false): callable
    {
        return function (mixed $value) use ($mustExist, $mustBeReadable): bool|string {
            $path = (string) $value;

            if ($mustExist && !file_exists($path)) {
                return "File not found: {$path}";
            }

            if ($mustBeReadable && !is_readable($path)) {
                return "File not readable: {$path}";
            }

            return true;
        };
    }

    /**
     * Validate directory exists and optionally is writable.
     *
     * @param bool $mustExist Whether directory must exist
     * @param bool $mustBeWritable Whether directory must be writable
     * @return callable Validator function
     */
    public static function directory(bool $mustExist = true, bool $mustBeWritable = false): callable
    {
        return function (mixed $value) use ($mustExist, $mustBeWritable): bool|string {
            $path = (string) $value;

            if ($mustExist && !is_dir($path)) {
                return "Directory not found: {$path}";
            }

            if ($mustBeWritable && !is_writable($path)) {
                return "Directory not writable: {$path}";
            }

            return true;
        };
    }

    /**
     * Validate value is one of allowed choices.
     *
     * @param array<mixed> $allowed List of allowed values
     * @return callable Validator function
     */
    public static function choice(array $allowed): callable
    {
        return function (mixed $value) use ($allowed): bool|string {
            if (!in_array($value, $allowed, strict: true)) {
                $list = implode(', ', array_map(fn($v) => "'{$v}'", $allowed));
                return "Value must be one of: {$list}";
            }
            return true;
        };
    }

    /**
     * Validate string minimum length.
     *
     * @param int $length Minimum length
     * @return callable Validator function
     */
    public static function minLength(int $length): callable
    {
        return function (mixed $value) use ($length): bool|string {
            if (strlen((string) $value) < $length) {
                return "Value must be at least {$length} characters";
            }
            return true;
        };
    }

    /**
     * Validate string maximum length.
     *
     * @param int $length Maximum length
     * @return callable Validator function
     */
    public static function maxLength(int $length): callable
    {
        return function (mixed $value) use ($length): bool|string {
            if (strlen((string) $value) > $length) {
                return "Value must be at most {$length} characters";
            }
            return true;
        };
    }

    /**
     * Validate email format.
     *
     * @return callable Validator function
     */
    public static function email(): callable
    {
        return function (mixed $value): bool|string {
            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                return "Invalid email address";
            }
            return true;
        };
    }

    /**
     * Validate URL format.
     *
     * @return callable Validator function
     */
    public static function url(): callable
    {
        return function (mixed $value): bool|string {
            if (!filter_var($value, FILTER_VALIDATE_URL)) {
                return "Invalid URL";
            }
            return true;
        };
    }

    /**
     * Combine multiple validators (all must pass).
     *
     * @param callable ...$validators Validators to combine
     * @return callable Combined validator function
     */
    public static function all(callable ...$validators): callable
    {
        return function (mixed $value) use ($validators): bool|string {
            foreach ($validators as $validator) {
                $result = $validator($value);
                if ($result !== true) {
                    return $result;
                }
            }
            return true;
        };
    }

    /**
     * Combine multiple validators (any can pass).
     *
     * @param callable ...$validators Validators to combine
     * @return callable Combined validator function
     */
    public static function any(callable ...$validators): callable
    {
        return function (mixed $value) use ($validators): bool|string {
            $errors = [];
            foreach ($validators as $validator) {
                $result = $validator($value);
                if ($result === true) {
                    return true;
                }
                $errors[] = $result;
            }
            return "All validations failed: " . implode('; ', $errors);
        };
    }
}
