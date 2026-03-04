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

/**
 * Exception for unknown context (subcommand) names.
 *
 * Thrown when a positional argument doesn't match any registered context.
 *
 * @category Horde
 * @package  Argv
 */
class UnknownContextException extends \Exception
{
    /**
     * Constructor.
     *
     * @param string $contextName Unknown context name
     * @param array<string> $availableContexts Available context names
     */
    public function __construct(
        public readonly string $contextName,
        public readonly array $availableContexts = []
    ) {
        $message = "Unknown context: '{$contextName}'";

        if (count($availableContexts) > 0) {
            $message .= "\nAvailable contexts: " . implode(', ', $availableContexts);
        }

        parent::__construct($message);
    }
}
