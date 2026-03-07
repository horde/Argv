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

class MatchAbbrevTest extends TestCase
{
    public function testMatchAbbrev()
    {
        $this->assertEquals(
            Horde_Argv_Parser::matchAbbrev(
                "--f",
                ["--foz" => null,
                    "--foo" => null,
                    "--fie" => null,
                    "--f"   => null]
            ),
            '--f'
        );
    }

    public function testMatchAbbrevError()
    {
        $s = '--f';
        $wordmap = ["--foz" => null, "--foo" => null, "--fie" => null];

        $this->expectException('Horde_Argv_AmbiguousOptionException');

        Horde_Argv_Parser::matchAbbrev($s, $wordmap);
    }
}
