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

namespace Horde\Argv\Modern\Config;

/**
 * Immutable option group configuration.
 *
 * Groups organize related options in help output for better readability.
 *
 * Following Principle #7: Immutable Throughout
 *
 * @category Horde
 * @package  Argv
 */
readonly class OptionGroupConfig
{
    /**
     * Constructor.
     *
     * @param string $title Group title shown in help
     * @param string $description Group description (optional)
     * @param array<OptionConfig> $options Options in this group
     */
    public function __construct(
        public string $title,
        public string $description = '',
        public array $options = [],
    ) {
    }
}
