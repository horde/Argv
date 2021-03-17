<?php
/**
 * @author     Chuck Hagenbuch <chuck@horde.org>
 * @author     Mike Naberezny <mike@maintainable.com>
 * @license    http://www.horde.org/licenses/bsd BSD
 * @category   Horde
 * @package    Argv
 * @subpackage UnitTests
 */
namespace Horde\Argv;
use \Horde_Argv_Parser;
use \Horde_Argv_Option;
use \Horde_Argv_IndentedHelpFormatter;
use \Horde_Cli_Color;

class ConflictTestCase extends TestCase
{
    public function setUp(): void
    {
        $options = array(new Horde_Argv_Option('-v', '--verbose', array(
            'action' => 'count',
            'dest' => 'verbose',
            'help' => 'increment verbosity'))
        );

        $this->parser = new InterceptingParser(array(
            'usage' => Horde_Argv_Option::SUPPRESS_USAGE,
            'optionList' => $options,
            'formatter' => new Horde_Argv_IndentedHelpFormatter(
                2, 24, null, true,
                new Horde_Cli_Color(Horde_Cli_Color::FORMAT_NONE)
            )
        ));
    }

    public function showVersion($option, $opt, $value, $parser)
    {
        $this->parser->values->showVersion = 1;
    }

}
