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
 * Raised if an Option instance is created with invalid or
 * inconsistent arguments.
 *
 * @category  Horde
 * @package   Argv
 * @author    Chuck Hagenbuch <chuck@horde.org>
 * @author    Mike Naberezny <mike@maintainable.com>
 * @copyright 2010-2017 Horde LLC
 * @license   http://www.horde.org/licenses/bsd BSD
 */
class OptionException extends Exception
{
    public function __construct($msg, $option = null)
    {
        $this->optionId = (string)$option;
        if ($this->optionId) {
            parent::__construct(sprintf('option %s: %s', $this->optionId, $msg));
        } else {
            parent::__construct($msg);
        }
    }

}
