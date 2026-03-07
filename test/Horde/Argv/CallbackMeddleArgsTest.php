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

class CallbackMeddleArgsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $options = [];
        for ($i = -1; $i > -6; $i--) {
            $options[] = $this->makeOption((string) $i, ['action' => 'callback',
                'callback' => [$this, 'process_n'],
                'dest' => 'things']);
        }
        $this->parser = new Horde_Argv_Parser(['optionList' => $options]);
    }

    /**
     * Callback that meddles in rargs, largs
     */
    public function process_n($option, $opt, $value, $parser)
    {
        // option is -3, -5, etc.
        $nargs = (int) substr($opt, 1);
        $rargs = & $parser->rargs;
        if (count($rargs) < $nargs) {
            $this->fail(sprintf("Expected %d arguments for %s option.", $nargs, $opt));
        }

        $parser->values->{$option->dest}[] = array_splice($rargs, 0, $nargs);
        $parser->largs[] = $nargs;
    }

    public function testCallbackMeddleArgs()
    {
        $this->assertParseOK(
            ["-1", "foo", "-3", "bar", "baz", "qux"],
            ['things' => [['foo'], ['bar', 'baz', 'qux']]],
            [1, 3]
        );
    }

    public function testCallbackMeddleArgsSeparator()
    {
        $this->assertParseOK(
            ["-2", "foo", "--"],
            ['things' => [['foo', '--']]],
            [2]
        );
    }

}
