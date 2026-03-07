<?php

namespace Horde\Argv;

use Horde_Argv_Option;
use Horde_Argv_Parser;

/**
 * @author     Chuck Hagenbuch <chuck@horde.org>
 * @author     Mike Naberezny <mike@maintainable.com>
 * @license    http://www.horde.org/licenses/bsd BSD
 * @category   Horde
 * @package    Argv
 * @subpackage UnitTests
 * @coversNothing
 */

class ExtendAddActionsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $options = [new Horde_Argv_ExtendAddActionsTest_MyOption("-a", "--apple", [
            'action' => "extend", 'type' => "string", 'dest' => "apple"])];
        $this->parser = new Horde_Argv_Parser(['optionList' => $options]);
    }

    public function testExtendAddAction()
    {
        $this->assertParseOK(
            ["-afoo,bar", "--apple=blah"],
            ['apple' => ["foo", "bar", "blah"]],
            []
        );
    }

    public function testExtendAddActionNormal()
    {
        $this->assertParseOK(
            ["-a", "foo", "-abar", "--apple=x,y"],
            ['apple' => ["foo", "bar", "x", "y"]],
            []
        );
    }

}

class Horde_Argv_ExtendAddActionsTest_MyOption extends Horde_Argv_Option
{
    public $ACTIONS = ["store",
        "store_const",
        "store_true",
        "store_false",
        "append",
        "append_const",
        "count",
        "callback",
        "help",
        "version",
        "extend",
    ];

    public $STORE_ACTIONS = ["store",
        "store_const",
        "store_true",
        "store_false",
        "append",
        "append_const",
        "count",
        "extend",
    ];

    public $TYPED_ACTIONS = ["store",
        "append",
        "callback",
        "extend",
    ];

    public function takeAction($action, $dest, $opt, $value, $values, $parser)
    {
        if ($action == "extend") {
            $lvalue = explode(',', $value);
            $values->$dest = array_merge($values->ensureValue($dest, []), $lvalue);
        } else {
            parent::takeAction($action, $dest, $opt, $parser, $value, $values);
        }
    }

}
