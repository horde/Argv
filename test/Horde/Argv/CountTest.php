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

class CountTest extends TestCase
{
    public $vOpt;
    public function setUp(): void
    {
        parent::setUp();
        $this->parser = new InterceptingParser(['usage' => Horde_Argv_Option::SUPPRESS_USAGE]);
        $this->vOpt = $this->makeOption('-v', ['action' => 'count', 'dest' => 'verbose']);
        $this->parser->addOption($this->vOpt);
        $this->parser->addOption('--verbose', ['type' => 'int', 'dest' => 'verbose']);
        $this->parser->addOption(
            '-q',
            '--quiet',
            ['action' => 'store_const', 'dest' => 'verbose', 'const' => 0]
        );
    }

    public function testEmpty()
    {
        $this->assertParseOk([], ['verbose' => null], []);
    }

    public function testCountOne()
    {
        $this->assertParseOk(['-v'], ['verbose' => 1], []);
    }

    public function testCountThree()
    {
        $this->assertParseOk(['-vvv'], ['verbose' => 3], []);
    }

    public function testCountThreeApart()
    {
        $this->assertParseOk(['-v', '-v', '-v'], ['verbose' => 3], []);
    }

    public function testCountOverrideAmount()
    {
        $this->assertParseOk(['-vvv', '--verbose=2'], ['verbose' => 2], []);
    }

    public function testCountOverrideQuiet()
    {
        $this->assertParseOk(['-vvv', '--verbose=2', '-q'], ['verbose' => 0], []);
    }

    public function testCountOverriding()
    {
        $this->assertParseOk(
            ['-vvv', '--verbose=2', '-q', '-v'],
            ['verbose' => 1],
            []
        );
    }

    public function testCountInterspersedArgs()
    {
        $this->assertParseOk(
            ['--quiet', '3', '-v'],
            ['verbose' => 1],
            ['3']
        );
    }

    public function testCountNoInterspersedArgs()
    {
        $this->parser->disableInterspersedArgs();
        $this->assertParseOk(
            ['--quiet', '3', '-v'],
            ['verbose' => 0],
            ['3', '-v']
        );
    }

    public function testCountNoSuchOption()
    {
        $this->assertParseFail(['-q3', '-v'], 'no such option: -3');
    }

    public function testCountOptionNoValue()
    {
        $this->assertParseFail(
            ['--quiet=3', 'v'],
            '--quiet option does not take a value'
        );
    }

    public function testCountWithDefault()
    {
        $this->parser->setDefault('verbose', 0);
        $this->assertParseOk([], ['verbose' => 0], []);
    }

    public function testCountOverridingDefault()
    {
        $this->parser->setDefault('verbose', 0);
        $this->assertParseOk(
            ['-vvv', '--verbose=2', '-q', '-v'],
            ['verbose' => 1],
            []
        );
    }
}
