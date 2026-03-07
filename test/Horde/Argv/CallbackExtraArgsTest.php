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

class CallbackExtraArgsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $options = [
            $this->makeOption('-p', '--point', ['action' => 'callback',
                'callback' => [$this, 'processTuple'],
                'callbackArgs' => [3, 'int'], 'type' => 'string',
                'dest' => 'points', 'default' => []]),
        ];
        $this->parser = new Horde_Argv_Parser(['optionList' => $options]);
    }

    public function processTuple($option, $opt, $value, $parser, $args)
    {
        [$len, $type] = $args;

        $this->assertEquals(3, $len);
        $this->assertEquals('int', $type);

        if ($opt == '-p') {
            $this->assertEquals('1,2,3', $value);
        } elseif ($option == '--point') {
            $this->assertEquals('4,5,6', $value);
        }

        $values = explode(',', $value);
        foreach ($values as &$value) {
            settype($value, $type);
        }

        $parser->values->{$option->dest}[] = $values;
    }

    public function testCallbackExtraArgs()
    {
        $this->assertParseOk(
            ["-p1,2,3", "--point", "4,5,6"],
            ['points' => [[1,2,3], [4,5,6]]],
            []
        );
    }

}
