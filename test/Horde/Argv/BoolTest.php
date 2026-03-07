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

class BoolTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $options = [
            $this->makeOption(
                '-v',
                '--verbose',
                ['action' => 'store_true', 'dest' => 'verbose', 'default' => '']
            ),
            $this->makeOption(
                '-q',
                '--quiet',
                ['action' => 'store_false', 'dest' => 'verbose']
            ),
        ];

        $this->parser = new Horde_Argv_Parser(['optionList' => $options]);
    }

    public function testBoolDefault()
    {
        $this->assertParseOk(
            [],
            ['verbose' => ''],
            []
        );
    }

    public function testBoolFalse()
    {
        [$options, $args] = $this->assertParseOk(
            ['-q'],
            ['verbose' => false],
            []
        );

        $this->assertSame(false, $options->verbose);
    }

    public function testBoolTrue()
    {
        [$options, $args] = $this->assertParseOk(
            ['-v'],
            ['verbose' => true],
            []
        );
        $this->assertSame(true, $options->verbose);
    }

    public function testBoolFlickerOnAndOff()
    {
        $this->assertParseOk(
            ['-qvq', '-q', '-v'],
            ['verbose' => true],
            []
        );
    }

}
