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

/**
 * Conflicting default values: the last one should win.
 * @coversNothing
 */
class ConflictingDefaultsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $options = [
            $this->makeOption('-v', ['action' => 'store_true', 'dest' => 'verbose', 'default' => 1]),
        ];

        $this->parser = new Horde_Argv_Parser(['optionList' => $options]);
    }

    public function testConflictDefault()
    {
        $this->parser->addOption('-q', ['action' => 'store_false', 'dest' => 'verbose',
            'default' => 0]);

        $this->assertParseOk([], ['verbose' => 0], []);
    }

    public function testConflictDefaultNone()
    {
        $this->parser->addOption('-q', ['action' => 'store_false', 'dest' => 'verbose',
            'default' => null]);

        $this->assertParseOk([], ['verbose' => null], []);
    }
}
