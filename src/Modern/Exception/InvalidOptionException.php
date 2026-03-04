<?php

declare(strict_types=1);

/**
 * Copyright 2024-2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @author   Ralf Lang <lang@b1-systems.de>
 * @category Horde
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Argv
 */

namespace Horde\Argv\Modern\Exception;

use Throwable;

/**
 * Invalid option configuration exception.
 *
 * Thrown when option configuration is invalid (e.g., missing required fields,
 * invalid format, conflicting settings).
 *
 * @category Horde
 * @package  Argv
 */
class InvalidOptionException extends \Exception
{
    /**
     * Constructor.
     *
     * @param string $optionName Option with invalid configuration
     * @param string $reason Human-readable reason for invalidity
     * @param array<string, mixed> $context Additional context for debugging
     * @param int $code Exception code
     * @param Throwable|null $previous Previous exception
     */
    public function __construct(
        private readonly string $optionName,
        private readonly string $reason,
        private readonly array $context = [],
        int $code = 0,
        ?Throwable $previous = null
    ) {
        $msg = $optionName !== ''
            ? "Invalid option '{$optionName}': {$reason}"
            : "Invalid option configuration: {$reason}";

        parent::__construct($msg, $code, $previous);
    }

    /**
     * Get the option name.
     *
     * @return string
     */
    public function getOptionName(): string
    {
        return $this->optionName;
    }

    /**
     * Get context information for debugging.
     *
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }
}
