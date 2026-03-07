<?php

namespace Horde\Argv;

use Horde_Argv_IndentedHelpFormatter;

/**
 * @author     Chuck Hagenbuch <chuck@horde.org>
 * @author     Mike Naberezny <mike@maintainable.com>
 * @license    http://www.horde.org/licenses/bsd BSD
 * @category   Horde
 * @package    Argv
 * @subpackage UnitTests
 * @coversNothing
 */

class ConflictResolveTest extends ConflictTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->parser->setConflictHandler('resolve');
        $this->parser->addOption('-v', '--version', ['action' => 'callback',
            'callback' => [$this, 'showVersion'],
            'help' => 'show version']);
    }

    public function testConflictResolve()
    {
        $vOpt = $this->parser->getOption('-v');
        $verboseOpt = $this->parser->getOption('--verbose');
        $versionOpt = $this->parser->getOption('--version');

        $this->assertSame($vOpt, $versionOpt);
        $this->assertNotSame($vOpt, $verboseOpt);

        $this->assertEquals(['--version'], $vOpt->longOpts);
        $this->assertEquals(['-v'], $versionOpt->shortOpts);
        $this->assertEquals(['--version'], $versionOpt->longOpts);
        $this->assertEquals([], $verboseOpt->shortOpts);
        $this->assertEquals(['--verbose'], $verboseOpt->longOpts);
    }

    public function testConflictResolveHelp()
    {
        $output = "Options:\n"
                . "  --verbose      increment verbosity\n"
                . "  -h, --help     show this help message and exit\n"
                . "  -v, --version  show version\n";

        $this->assertOutput(['-h'], $output);
    }

    public function testConflictResolveShortOpt()
    {
        $this->assertParseOk(
            ['-v'],
            ['verbose' => null, 'showVersion' => 1],
            []
        );
    }

    public function testConflictResolveLongOpt()
    {
        $this->assertParseOk(
            ['--verbose'],
            ['verbose' => 1],
            []
        );
    }

    public function testConflictResolveLongOpts()
    {
        $this->assertParseOk(
            ['--verbose', '--version'],
            ['verbose' => 1, 'showVersion' => 1],
            []
        );
    }
}
