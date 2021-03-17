<?php

namespace Horde\Argv;
use Horde_Argv_Parser;

/**
 * @author     Chuck Hagenbuch <chuck@horde.org>
 * @author     Mike Naberezny <mike@maintainable.com>
 * @license    http://www.horde.org/licenses/bsd BSD
 * @category   Horde
 * @package    Argv
 * @subpackage UnitTests
 */

class InterceptingParser extends Horde_Argv_Parser
{
    public function parserExit($status = 0, $msg = null)
    {
        throw new InterceptedException(null, $status, $msg);
    }

    public function parserError($msg)
    {
        throw new InterceptedException($msg);
    }

}
