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
 * @coversNothing
 */

class CallbackManyArgsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $options = [
            $this->makeOption('-a', '--apple', ['action' => 'callback', 'nargs' => 2,
                'callback' => [$this, 'processMany'], 'type' => 'string']),
            $this->makeOption('-b', '--bob', ['action' => 'callback', 'nargs' => 3,
                'callback' => [$this, 'processMany'], 'type' => 'int']),
        ];

        $this->parser = new Horde_Argv_Parser(['optionList' => $options]);
    }

    public function processMany($option, $opt, $value, $parser_)
    {
        if ($opt == '-a') {
            $this->assertEquals(['foo', 'bar'], $value);
        } elseif ($opt == '--apple') {
            $this->assertEquals(['ding', 'dong'], $value);
        } elseif ($opt == '-b') {
            $this->assertEquals([1, 2, 3], $value);
        } elseif ($option == '--bob') {
            $this->assertEquals([-666, 42, 0], $value);
        }
    }

    public function testManyArgs()
    {
        $this->assertParseOk(
            ["-a", "foo", "bar", "--apple", "ding", "dong",
                "-b", "1", "2", "3", "--bob", "-666", "42",
                "0"],
            ['apple' => null, 'bob' => null],
            []
        );
    }
}
