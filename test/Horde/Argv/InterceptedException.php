<?php
/**
 * @author     Chuck Hagenbuch <chuck@horde.org>
 * @author     Mike Naberezny <mike@maintainable.com>
 * @license    http://www.horde.org/licenses/bsd BSD
 * @category   Horde
 * @package    Argv
 * @subpackage UnitTests
 */

namespace Horde\Argv;
use Exception;

class InterceptedException extends Exception
{
    public $error_message;
    public $exit_message;
    public $exit_status;

    public function __construct($error_message = null, $exit_status = null, $exit_message = null)
    {
        $this->error_message = $error_message;
        $this->exit_status = $exit_status;
        $this->exit_message = $exit_message;
    }

    public function __toString():string
    {
        if ($this->error_message)
            return $this->error_message;
        if ($this->exit_message)
            return $this->exit_message;
        return "intercepted error";
    }

}
