<?php

namespace Horde\Argv;
use \Horde_Argv_Parser;

/**
 * @author     Chuck Hagenbuch <chuck@horde.org>
 * @author     Mike Naberezny <mike@maintainable.com>
 * @license    http://www.horde.org/licenses/bsd BSD
 * @category   Horde
 * @package    Argv
 * @subpackage UnitTests
 */

/**
 * Conflicting default values: the last one should win.
 */
class ConflictingDefaultsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $options = array(
            $this->makeOption('-v', array('action' => 'store_true', 'dest' => 'verbose', 'default' => 1))
        );

        $this->parser = new Horde_Argv_Parser(array('optionList' => $options));
    }

    public function testConflictDefault()
    {
        $this->parser->addOption('-q', array('action' => 'store_false', 'dest' => 'verbose',
                                             'default' => 0));

        $this->assertParseOk(array(), array('verbose' => 0), array());
    }

    public function testConflictDefaultNone()
    {
        $this->parser->addOption('-q', array('action' => 'store_false', 'dest' => 'verbose',
                                             'default' => null));

        $this->assertParseOk(array(), array('verbose' => null), array());
    }
}
