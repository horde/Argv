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

class ChoiceTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->parser = new InterceptingParser(['usage' => Horde_Argv_Option::SUPPRESS_USAGE]);
        $this->parser->addOption('-c', ['action' => 'store', 'type' => 'choice',
            'dest' => 'choice', 'choices' => ['one', 'two', 'three']]);
    }

    public function testValidChoice()
    {
        $this->assertParseOk(
            ['-c', 'one', 'xyz'],
            ['choice' => 'one'],
            ['xyz']
        );
    }

    public function testInvalidChoice()
    {
        $this->assertParseFail(
            ['-c', 'four', 'abc'],
            "option -c: invalid choice: 'four' "
                               . "(choose from 'one', 'two', 'three')"
        );
    }

    public function testAddChoiceOption()
    {
        $this->parser->addOption('-d', '--default', ['choices' => ['four', 'five', 'six']]);
        $opt = $this->parser->getOption('-d');
        $this->assertEquals('choice', $opt->type);
        $this->assertEquals('store', $opt->action);
    }
}
