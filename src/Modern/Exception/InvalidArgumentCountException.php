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

use Exception;

/**
 * Exception for invalid argument counts in contexts.
 *
 * Thrown when a context receives wrong number of positional arguments.
 *
 * @category Horde
 * @package  Argv
 */
class InvalidArgumentCountException extends Exception
{
    /**
     * Constructor.
     *
     * @param string $contextName Context name
     * @param int $actualCount Actual argument count
     * @param int $minArgs Minimum required arguments
     * @param int $maxArgs Maximum allowed arguments
     */
    public function __construct(
        public readonly string $contextName,
        public readonly int $actualCount,
        public readonly int $minArgs,
        public readonly int $maxArgs
    ) {
        if ($minArgs === $maxArgs) {
            $message = sprintf(
                "Context '%s' requires exactly %d argument%s, got %d",
                $contextName,
                $minArgs,
                $minArgs === 1 ? '' : 's',
                $actualCount
            );
        } elseif ($actualCount < $minArgs) {
            $message = sprintf(
                "Context '%s' requires at least %d argument%s, got %d",
                $contextName,
                $minArgs,
                $minArgs === 1 ? '' : 's',
                $actualCount
            );
        } else {
            $message = sprintf(
                "Context '%s' accepts at most %d argument%s, got %d",
                $contextName,
                $maxArgs,
                $maxArgs === 1 ? '' : 's',
                $actualCount
            );
        }

        parent::__construct($message);
    }
}
