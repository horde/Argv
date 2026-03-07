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

class ParseNumTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->parser = new InterceptingParser();
        $this->parser->addOption('-n', ['type' => 'int']);
        $this->parser->addOption('-l', ['type' => 'long']);
    }

    public function testParseNumFail()
    {
        $this->assertFalse(Horde_Argv_Option::parseNumber(''));
        $this->assertFalse(Horde_Argv_Option::parseNumber("0xOoops"));
    }

    public function testParseNumOk()
    {
        $this->assertSame(
            0,
            Horde_Argv_Option::parseNumber('0')
        );
        $this->assertSame(
            16,
            Horde_Argv_Option::parseNumber('0x10')
        );
        $this->assertSame(
            10,
            Horde_Argv_Option::parseNumber('0XA')
        );
        $this->assertSame(
            8,
            Horde_Argv_Option::parseNumber('010')
        );
        $this->assertSame(
            3,
            Horde_Argv_Option::parseNumber('0b11')
        );
        $this->assertSame(
            0,
            Horde_Argv_Option::parseNumber('0b')
        );
    }

    public function testNumericOptions()
    {
        $this->assertParseOk(
            ["-n", "42", "-l", "0x20"],
            ["n" => 42, "l" => 0x20],
            []
        );

        $this->assertParseOk(
            ["-n", "0b0101", "-l010"],
            ["n" => 5, "l" => 8],
            []
        );

        $this->assertParseFail(
            ["-n008"],
            "option -n: invalid integer value: '008'"
        );

        $this->assertParseFail(
            ["-l0b0123"],
            "option -l: invalid long integer value: '0b0123'"
        );

        $this->assertParseFail(["-l", "0x12x"],
            "option -l: invalid long integer value: '0x12x'");
    }
}
