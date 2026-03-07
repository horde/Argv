<?php

namespace Horde\Argv;

use Horde_Argv_Option;

/**
 * @author     Chuck Hagenbuch <chuck@horde.org>
 * @author     Mike Naberezny <mike@maintainable.com>
 * @license    http://www.horde.org/licenses/bsd BSD
 * @category   Horde
 * @package    Argv
 * @subpackage UnitTests
 * @coversNothing
 */

class CallbackVarArgsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $options = [
            $this->makeOption('-a', ['type' => 'int', 'nargs' => 2, 'dest' => 'a']),
            $this->makeOption('-b', ['action' => 'store_true', 'dest' => 'b']),
            $this->makeOption('-c', '--callback', ['action' => 'callback', 'callback' => [$this, 'variableArgs'], 'dest' => 'c']),
        ];
        $this->parser = new InterceptingParser(['usage' => Horde_Argv_Option::SUPPRESS_USAGE,
            'optionList' => $options]);
    }

    public function variableArgs($option, $opt, $value, $parser)
    {
        $this->assertNull($value);
        $done = 0;
        $value = [];
        $rargs = & $parser->rargs;
        while ($rargs) {
            $arg = $rargs[0];
            if ((substr($arg, 0, 2) == '--' && strlen($arg) > 2)
                || (substr($arg, 0, 1) == '-' && strlen($arg) > 1 && substr($arg, 1, 1) != '-')) {
                break;
            } else {
                $value[] = $arg;
                array_shift($rargs);
            }
        }
        $parser->values->{$option->dest} = $value;
    }

    public function testVariableArgs()
    {
        $this->assertParseOK(
            ['-a3', '-5', '--callback', 'foo', 'bar'],
            ['a' => [3, -5], 'b' => null, 'c' => ['foo', 'bar']],
            []
        );
    }

    public function testConsumeSeparatorStopAtOption()
    {
        $this->assertParseOK(
            ['-c', '37', '--', 'xxx', '-b', 'hello'],
            ['a' => null, 'b' => true, 'c' => ['37', '--', 'xxx']],
            ['hello']
        );
    }

    public function testPositionalArgAndVariableArgs()
    {
        $this->assertParseOK(
            ['hello', '-c', 'foo', '-', 'bar'],
            ['a' => null, 'b' => null, 'c' => ['foo', '-', 'bar']],
            ['hello']
        );
    }

    public function testStopAtOption()
    {
        $this->assertParseOK(
            ['-c', 'foo', '-b'],
            ['a' => null, 'b' => true, 'c' => ['foo']],
            []
        );
    }

    public function testStopAtInvalidOption()
    {
        $this->assertParseFail(['-c', '3', '-5', '-a'], 'no such option: -5');
    }

}
