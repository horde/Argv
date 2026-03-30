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

namespace Horde\Argv\Modern\Exception;

use Horde\Argv\Modern\Enum\OptionType;
use Throwable;
use Exception;

/**
 * Value validation failed exception.
 *
 * Thrown when an option value fails type validation or custom validation.
 * Contains context about what was expected and what was provided.
 *
 * @category Horde
 * @package  Argv
 */
class ValueValidationException extends Exception
{
    /**
     * Constructor.
     *
     * @param string $optionName Option that failed validation
     * @param mixed $providedValue The value that was provided
     * @param OptionType $expectedType The expected type
     * @param string $reason Human-readable reason for failure
     * @param int $code Exception code
     * @param Throwable|null $previous Previous exception
     */
    public function __construct(
        private readonly string $optionName,
        private readonly mixed $providedValue,
        private readonly OptionType $expectedType,
        private readonly string $reason = '',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        $msg = $optionName !== ''
            ? "Option '{$optionName}': {$reason}"
            : $reason;

        parent::__construct($msg, $code, $previous);
    }

    /**
     * Get the option name that failed.
     *
     * @return string
     */
    public function getOptionName(): string
    {
        return $this->optionName;
    }

    /**
     * Get the value that was provided.
     *
     * @return mixed
     */
    public function getProvidedValue(): mixed
    {
        return $this->providedValue;
    }

    /**
     * Get the expected type.
     *
     * @return OptionType
     */
    public function getExpectedType(): OptionType
    {
        return $this->expectedType;
    }

    /**
     * Get context information for debugging.
     *
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return [
            'option' => $this->optionName,
            'provided' => $this->providedValue,
            'expected_type' => $this->expectedType->value,
            'reason' => $this->reason,
        ];
    }
}
