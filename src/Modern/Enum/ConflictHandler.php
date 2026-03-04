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

namespace Horde\Argv\Modern\Enum;

/**
 * Conflict resolution strategies when options collide.
 *
 * When adding an option that conflicts with an existing option
 * (same short or long name), this enum defines how to handle it.
 *
 * @category Horde
 * @package  Argv
 */
enum ConflictHandler: string
{
    /**
     * Throw exception on conflict (default, safest)
     */
    case Error = 'error';

    /**
     * Auto-resolve by removing old option (allows overriding)
     */
    case Resolve = 'resolve';
}
