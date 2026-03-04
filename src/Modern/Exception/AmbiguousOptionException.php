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
 * Ambiguous partial option match exception.
 *
 * Thrown when a partial long option matches multiple possibilities.
 * For example, "--ver" might match both "--version" and "--verbose".
 *
 * @category Horde
 * @package  Argv
 */
class AmbiguousOptionException extends \Exception
{
    /**
     * Constructor.
     *
     * @param string $optionName Partial option that was ambiguous
     * @param array<int, string> $possibilities All matching options
     * @param int $code Exception code
     * @param Throwable|null $previous Previous exception
     */
    public function __construct(
        private readonly string $optionName,
        private readonly array $possibilities,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        $list = implode(', ', $possibilities);
        parent::__construct(
            "Ambiguous option '{$optionName}' could match: {$list}",
            $code,
            $previous
        );
    }

    /**
     * Get the ambiguous option name.
     *
     * @return string
     */
    public function getOptionName(): string
    {
        return $this->optionName;
    }

    /**
     * Get all possible matches.
     *
     * @return array<int, string>
     */
    public function getPossibilities(): array
    {
        return $this->possibilities;
    }

    /**
     * Get context information for debugging.
     *
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return ['possibilities' => $this->possibilities];
    }
}
