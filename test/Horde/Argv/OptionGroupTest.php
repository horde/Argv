<?php

namespace Horde\Argv;
use \Horde_Argv_Parser;
use \Horde_Argv_Option;
use \Horde_Argv_OptionGroup;

/**
 * @author     Chuck Hagenbuch <chuck@horde.org>
 * @author     Mike Naberezny <mike@maintainable.com>
 * @license    http://www.horde.org/licenses/bsd BSD
 * @category   Horde
 * @package    Argv
 * @subpackage UnitTests
 */

class OptionGroupTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->parser = new Horde_Argv_Parser(array('usage' => Horde_Argv_Option::SUPPRESS_USAGE));
    }

    public function testOptionGroupCreateInstance()
    {
        $group = new Horde_Argv_OptionGroup($this->parser, "Spam");
        $this->parser->addOptionGroup($group);
        $group->addOption("--spam", array('action' => "store_true",
                                          'help' => "spam spam spam spam"));
        $this->assertParseOK(array("--spam"), array('spam' => true), array());
    }

    public function testAddGroupNoGroup()
    {
        $this->expectException('InvalidArgumentException');
        $this->assertTypeError(array($this->parser, 'addOptionGroup'),
                               "not an OptionGroup instance: NULL", array(null));
    }

    public function testAddGroupInvalidArguments()
    {
        $this->expectException('InvalidArgumentException');
        $this->assertTypeError(array($this->parser, 'addOptionGroup'),
                               "invalid arguments", null);
    }

    public function testAddGroupWrongParser()
    {
        $this->expectException('InvalidArgumentException');
        $group = new Horde_Argv_OptionGroup($this->parser, "Spam");
        $group->parser = new Horde_Argv_Parser();
        $this->assertRaises(array($this->parser, 'addOptionGroup'), array($group),
                            'InvalidArgumentException', "invalid OptionGroup (wrong parser)");
    }

    public function testGroupManipulate()
    {
        $group = $this->parser->addOptionGroup("Group 2",
                                               array('description' => "Some more options"));
        $group->setTitle("Bacon");
        $group->addOption("--bacon", array('type' => "int"));
        $this->assertSame($group, $this->parser->getOptionGroup("--bacon"));
    }

}
