<?php

namespace Horde\Argv;

use Horde_Argv_Parser;
use Horde_Argv_Option;
use Horde_Argv_OptionGroup;

/**
 * @author     Chuck Hagenbuch <chuck@horde.org>
 * @author     Mike Naberezny <mike@maintainable.com>
 * @license    http://www.horde.org/licenses/bsd BSD
 * @category   Horde
 * @package    Argv
 * @subpackage UnitTests
 * @coversNothing
 */

class OptionGroupTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->parser = new Horde_Argv_Parser(['usage' => Horde_Argv_Option::SUPPRESS_USAGE]);
    }

    public function testOptionGroupCreateInstance()
    {
        $group = new Horde_Argv_OptionGroup($this->parser, "Spam");
        $this->parser->addOptionGroup($group);
        $group->addOption("--spam", ['action' => "store_true",
            'help' => "spam spam spam spam"]);
        $this->assertParseOK(["--spam"], ['spam' => true], []);
    }

    public function testAddGroupNoGroup()
    {
        $this->expectException('InvalidArgumentException');
        $this->assertTypeError(
            [$this->parser, 'addOptionGroup'],
            "not an OptionGroup instance: NULL",
            [null]
        );
    }

    public function testAddGroupInvalidArguments()
    {
        $this->expectException('InvalidArgumentException');
        $this->assertTypeError(
            [$this->parser, 'addOptionGroup'],
            "invalid arguments",
            null
        );
    }

    public function testAddGroupWrongParser()
    {
        $this->expectException('InvalidArgumentException');
        $group = new Horde_Argv_OptionGroup($this->parser, "Spam");
        $group->parser = new Horde_Argv_Parser();
        $this->assertRaises(
            [$this->parser, 'addOptionGroup'],
            [$group],
            'InvalidArgumentException',
            "invalid OptionGroup (wrong parser)"
        );
    }

    public function testGroupManipulate()
    {
        $group = $this->parser->addOptionGroup(
            "Group 2",
            ['description' => "Some more options"]
        );
        $group->setTitle("Bacon");
        $group->addOption("--bacon", ['type' => "int"]);
        $this->assertSame($group, $this->parser->getOptionGroup("--bacon"));
    }

}
