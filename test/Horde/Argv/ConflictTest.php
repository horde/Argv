<?php

namespace Horde\Argv;
use \Horde_Argv_OptionGroup;

/**
 * @author     Chuck Hagenbuch <chuck@horde.org>
 * @author     Mike Naberezny <mike@maintainable.com>
 * @license    http://www.horde.org/licenses/bsd BSD
 * @category   Horde
 * @package    Argv
 * @subpackage UnitTests
 */

class ConflictTest extends ConflictTestCase
{
    public function assertConflictError($func)
    {
        try {
            call_user_func($func, '-v', '--version', array(
                'action'   => 'callback',
                'callback' => array($this, 'showVersion'),
                'help'     => 'show version'));
            $this->fail();
        } catch (Horde_Argv_OptionConflictException $e) {
            $this->assertEquals("option -v/--version: conflicting option string(s): -v",
                                $e->getMessage());
            $this->assertEquals('-v/--version', $e->optionId);
        }
    }

    public function testConflictError()
    {
        $this->expectException('Horde_Argv_OptionConflictException');
        $this->assertConflictError(array($this->parser, 'addOption'));
    }

    public function testConflictErrorGroup()
    {
        $this->expectException('Horde_Argv_OptionConflictException');
        $group = new Horde_Argv_OptionGroup($this->parser, 'Group 1');
        $this->assertConflictError(array($group, 'addOption'));
    }

    public function testNoSuchConflictHandler()
    {
        $this->expectException('InvalidArgumentException');
        $this->assertRaises(array($this->parser, 'setConflictHandler'), array('foo'), 'InvalidArgumentException', "invalid conflictHandler 'foo'");
    }

}
