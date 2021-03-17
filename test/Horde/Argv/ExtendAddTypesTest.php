<?php

namespace Horde\Argv;
use \Horde_Argv_Option;

/**
 * @author     Chuck Hagenbuch <chuck@horde.org>
 * @author     Mike Naberezny <mike@maintainable.com>
 * @license    http://www.horde.org/licenses/bsd BSD
 * @category   Horde
 * @package    Argv
 * @subpackage UnitTests
 */

class ExtendAddTypesTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        if (class_exists('Horde_Argv_ExtendAddTypesTest_MyOption')) {
            $this->parser = new InterceptingParser(array('usage' => Horde_Argv_Option::SUPPRESS_USAGE,
                                                                    'optionClass' => 'Horde_Argv_ExtendAddTypesTest_MyOption'));
            $this->parser->addOption("-a", null, array('type' => "string", 'dest' => "a"));
            $this->parser->addOption("-f", "--file", array('type' => "file", 'dest' => "file"));
        }

        /* @todo make more system independent */
        $this->testPath = tempnam('/tmp', 'horde_argv');
    }

    public function tearDown(): void
    {
        if (!is_link($this->testPath) && is_dir($this->testPath)) {
            rmdir($this->testPath);
        } elseif (is_file($this->testPath)) {
            unlink($this->testPath);
        }
    }

    public function testFiletypeOk()
    {
        if (class_exists('Horde_Argv_ExtendAddTypesTest_MyOption')) {
            touch($this->testPath);
            $this->assertParseOK(array("--file", $this->testPath, "-afoo"),
                                array('file' => $this->testPath, 'a' => 'foo'),
                                array());
        } else {
            $this->markTestSkipped('Class Horde_Argv_ExtendAddTypesTest_MyOption doesnt exist.');
        }

    }

    public function testFiletypeNoexist()
    {
        unlink($this->testPath);
        $this->markTestIncomplete();
    }

    public function testFiletypeNotfile()
    {
        $this->expectException('ReflectionException');
        unlink($this->testPath);
        mkdir($this->testPath);
        $this->assertParseFail(array("--file", $this->testPath, "-afoo"),
                               sprintf("%s: not a regular file", $this->testPath));
    }

}
