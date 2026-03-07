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
use Exception;

/**
 * Required value missing exception.
 *
 * Thrown when an option that requires a value is provided without one.
 * For example: "--port" without a following port number.
 *
 * @category Horde
 * @package  Argv
 */
class MissingValueException extends Exception
{
    /**
     * Constructor.
     *
     * @param string $optionName Option that is missing its value
     * @param int $code Exception code
     * @param Throwable|null $previous Previous exception
     */
    public function __construct(
        private readonly string $optionName,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct(
            "Option '{$optionName}' requires a value",
            $code,
            $previous
        );
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
        return [];
    }
}
