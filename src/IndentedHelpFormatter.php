<?php
declare(strict_types=1);
/**
 * Copyright 2010-2017 Horde LLC (http://www.horde.org/)
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
 * Format help with indented section bodies.
 *
 * @category  Horde
 * @package   Argv
 * @author    Chuck Hagenbuch <chuck@horde.org>
 * @author    Mike Naberezny <mike@maintainable.com>
 * @copyright 2010-2017 Horde LLC
 * @license   http://www.horde.org/licenses/bsd BSD
 */
class IndentedHelpFormatter extends HelpFormatter
{
    public function __construct(
        $indent_increment = 2, $max_help_position = 24, $width = null,
        $short_first = true, $color = null
    )
    {
        parent::__construct(
            $indent_increment, $max_help_position, $width, $short_first, $color
        );
    }

    public function formatUsage($usage)
    {
        return sprintf(
            $this->highlightHeading(Translation::t("Usage:"))
                . " %s\n",
            $usage
        );
    }

    public function formatHeading($heading)
    {
        return $this->highlightHeading(sprintf(
            '%' . $this->current_indent . "s%s:\n", '', $heading
        ));
    }

}
