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
 * Conflicting options exception.
 *
 * Thrown when mutually exclusive options are both provided.
 * For example, if --local and --remote conflict and both are given.
 *
 * @category Horde
 * @package  Argv
 */
class ConflictingOptionException extends \Exception
{
    /**
     * Constructor.
     *
     * @param string $optionName First conflicting option
     * @param string $conflictsWith Second conflicting option
     * @param int $code Exception code
     * @param Throwable|null $previous Previous exception
     */
    public function __construct(
        private readonly string $optionName,
        private readonly string $conflictsWith,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct(
            "Option '{$optionName}' conflicts with '{$conflictsWith}'",
            $code,
            $previous
        );
    }

    /**
     * Get the first option name.
     *
     * @return string
     */
    public function getOptionName(): string
    {
        return $this->optionName;
    }

    /**
     * Get the conflicting option name.
     *
     * @return string
     */
    public function getConflictsWith(): string
    {
        return $this->conflictsWith;
    }

    /**
     * Get context information for debugging.
     *
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return ['conflicts_with' => $this->conflictsWith];
    }
}
