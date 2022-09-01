<?php
declare(strict_types=1);
/**
 * Copyright 2010-2022 Horde LLC (http://www.horde.org/)
 *
 * This package is ported from Python's Optik (http://optik.sourceforge.net/).
 *
 * See the enclosed file LICENSE for license information (BSD). If you
 * did not receive this file, see http://www.horde.org/licenses/bsd.
 *
 * @author   Chuck Hagenbuch <chuck@horde.org>
 * @author   Mike Naberezny <mike@maintainable.com>
 * @license  http://www.horde.org/licenses/bsd BSD
 * @category Horde
 * @package  Argv
 */
namespace Horde\Argv;
/**
 * Raised if an ambiguous option is seen on the command line.
 *
 * @category  Horde
 * @package   Argv
 * @author    Chuck Hagenbuch <chuck@horde.org>
 * @author    Mike Naberezny <mike@maintainable.com>
 * @copyright 2010-2022 Horde LLC
 * @license   http://www.horde.org/licenses/bsd BSD
 */
class AmbiguousOptionException extends BadOptionException
{
    public function __construct($opt_str, $possibilities)
    {
        // Have to skip the BadOptionException constructor or the string gets double-prefixed.
        OptionException::__construct(sprintf('ambiguous option: %s (%s?)', $opt_str, implode(', ', $possibilities)));
    }

}
