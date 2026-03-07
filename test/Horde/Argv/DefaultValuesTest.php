<?php

namespace Horde\Argv;

use Horde_Argv_Option;
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

class DefaultValuesTest extends TestCase
{
    public $expected;

    public function setUp(): void
    {
        parent::setUp();
        $this->parser = new Horde_Argv_Parser();
        $this->parser->addOption('-v', '--verbose', ['default' => true]);
        $this->parser->addOption('-q', '--quiet', ['dest' => 'verbose']);
        $this->parser->addOption('-n', ['type' => 'int', 'default' => 37]);
        $this->parser->addOption('-m', ['type' => 'int']);
        $this->parser->addOption('-s', ['default' => 'foo']);
        $this->parser->addOption('-t');
        $this->parser->addOption('-u', ['default' => null]);

        $this->expected = ['verbose' => true,
            'n' => 37,
            'm' => null,
            's' => 'foo',
            't' => null,
            'u' => null];
    }

    public function testBasicDefault()
    {
        $this->assertEquals($this->expected, iterator_to_array($this->parser->getDefaultValues()));
    }

    public function testMixedDefaultsPost()
    {
        $this->parser->setDefaults(['n' => 42, 'm' => -100]);
        $this->expected = array_merge($this->expected, ['n' => 42, 'm' => -100]);
        $this->assertEquals($this->expected, iterator_to_array($this->parser->getDefaultValues()));
    }

    public function testMixedDefaultsPre()
    {
        $this->parser->setDefaults(['x' => 'barf', 'y' => 'blah']);
        $this->parser->addOption('-x', ['default' => 'frob']);
        $this->parser->addOption('-y');

        $this->expected = array_merge($this->expected, ['x' => 'frob', 'y' => 'blah']);
        $this->assertEquals($this->expected, iterator_to_array($this->parser->getDefaultValues()));

        $this->parser->removeOption('-y');
        $this->parser->addOption('-y', ['default' => null]);
        $this->expected = array_merge($this->expected, ['y' => null]);
        $this->assertEquals($this->expected, iterator_to_array($this->parser->getDefaultValues()));
    }

    public function testProcessDefault()
    {
        $this->expectException('ReflectionException');

        $this->parser->optionClass = 'Horde_Argv_DurationOption';
        $this->parser->addOption('-d', ['type' => 'duration', 'default' => 300]);
        $this->parser->addOption('-e', ['type' => 'duration', 'default' => '6m']);
        $this->parser->setDefaults(['n' => '42']);

        $this->expected = array_merge($this->expected, ['d' => 300, 'e' => 360, 'n' => '42']);
    }
}

class Horde_Argv_DurationOption extends Horde_Argv_Option
{
    public $TYPES = ['string', 'int', 'long', 'float', 'complex', 'choice', 'duration'];

    public $TYPE_CHECKER = ['int'    => 'checkBuiltin',
        'long'   => 'checkBuiltin',
        'float'  => 'checkBuiltin',
        'complex' => 'checkBuiltin',
        'choice' => 'checkChoice',
        'duration' => 'checkDuration',
    ];

    public function checkDuration($opt, $value)
    {
        // Custom type for testing processing of default values.
        $time_units = ['s' => 1, 'm' => 60, 'h' => 60 * 60, 'd' => 60 * 60 * 24];

        $last = substr($value, -1);
        if (is_numeric($last)) {
            return (int) $value;
        } elseif (isset($time_units[$last])) {
            return (int) substr($value, 0, -1) * $time_units[$last];
        } else {
            throw new Horde_Argv_OptionValueException(sprintf(
                'option %s: invalid duration: %s',
                $opt,
                $value
            ));
        }
    }

}
