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

namespace Horde\Argv\Modern\Config;

use Horde\Argv\Modern\Enum\ConflictHandler;

/**
 * Immutable parser configuration.
 *
 * Following Principle #7: Immutable Throughout
 * All config objects are readonly. Use builders to create them.
 *
 * @category Horde
 * @package  Argv
 */
readonly class ParserConfig
{
    /**
     * Constructor.
     *
     * @param string $usage Usage string (e.g., '%prog [options] <file>')
     * @param string $description Description shown in help before options
     * @param string|null $version Version string for --version
     * @param string|null $epilog Text shown in help after options
     * @param string|null $prog Program name (defaults to script name)
     * @param bool $allowInterspersedArgs Allow options mixed with arguments
     * @param bool $allowUnknownArgs Allow unknown options (don't error)
     * @param bool $ignoreUnknownArgs Ignore unknown options (don't include in result)
     * @param bool $addHelpOption Automatically add --help option
     * @param ConflictHandler $conflictHandler How to handle option conflicts
     */
    public function __construct(
        public string $usage = '',
        public string $description = '',
        public ?string $version = null,
        public ?string $epilog = null,
        public ?string $prog = null,
        public bool $allowInterspersedArgs = true,
        public bool $allowUnknownArgs = false,
        public bool $ignoreUnknownArgs = false,
        public bool $addHelpOption = true,
        public ConflictHandler $conflictHandler = ConflictHandler::Error,
    ) {}

    /**
     * Create a modified copy with one property changed.
     *
     * Used internally by builders for immutable updates.
     *
     * @param string $property Property name to change
     * @param mixed $value New value
     * @return self New instance with property changed
     */
    public function with(string $property, mixed $value): self
    {
        return new self(
            usage: $property === 'usage' ? $value : $this->usage,
            description: $property === 'description' ? $value : $this->description,
            version: $property === 'version' ? $value : $this->version,
            epilog: $property === 'epilog' ? $value : $this->epilog,
            prog: $property === 'prog' ? $value : $this->prog,
            allowInterspersedArgs: $property === 'allowInterspersedArgs' ? $value : $this->allowInterspersedArgs,
            allowUnknownArgs: $property === 'allowUnknownArgs' ? $value : $this->allowUnknownArgs,
            ignoreUnknownArgs: $property === 'ignoreUnknownArgs' ? $value : $this->ignoreUnknownArgs,
            addHelpOption: $property === 'addHelpOption' ? $value : $this->addHelpOption,
            conflictHandler: $property === 'conflictHandler' ? $value : $this->conflictHandler,
        );
    }
}
