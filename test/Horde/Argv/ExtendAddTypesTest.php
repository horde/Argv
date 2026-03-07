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

class ExtendAddTypesTest extends TestCase
{
    private string $testPath;

    public function setUp(): void
    {
        parent::setUp();
        if (class_exists('Horde\\Argv\\ExtendAddTypesTest\\MyOption')) {
            $this->parser = new InterceptingParser(['usage' => Horde_Argv_Option::SUPPRESS_USAGE,
                'optionClass' => 'Horde\\Argv\\ExtendAddTypesTest\\MyOption']);
            $this->parser->addOption("-a", null, ['type' => "string", 'dest' => "a"]);
            $this->parser->addOption("-f", "--file", ['type' => "file", 'dest' => "file"]);
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
        if (class_exists('Horde\\Argv\\ExtendAddTypesTest\\MyOption')) {
            touch($this->testPath);
            $this->assertParseOK(
                ["--file", $this->testPath, "-afoo"],
                ['file' => $this->testPath, 'a' => 'foo'],
                []
            );
        } else {
            $this->markTestSkipped('Class Horde_Argv_ExtendAddTypesTest_MyOption doesnt exist.');
        }

    }

    public function testFiletypeNoexist()
    {
        if (class_exists('Horde\\Argv\\ExtendAddTypesTest\\MyOption')) {
            unlink($this->testPath);
            $this->assertParseFail(
                ["--file", $this->testPath, "-afoo"],
                sprintf("%s: file does not exist", $this->testPath)
            );
        } else {
            $this->markTestSkipped('Class Horde_Argv_ExtendAddTypesTest_MyOption doesnt exist.');
        }
    }

    public function testFiletypeNotfile()
    {
        if (class_exists('Horde\\Argv\\ExtendAddTypesTest\\MyOption')) {
            unlink($this->testPath);
            mkdir($this->testPath);
            $this->assertParseFail(
                ["--file", $this->testPath, "-afoo"],
                sprintf("%s: not a regular file", $this->testPath)
            );
        } else {
            $this->markTestSkipped('Class Horde_Argv_ExtendAddTypesTest_MyOption doesnt exist.');
        }
    }
}
