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

class VersionTest extends TestCase
{
    public function setUp(): void
    {
        if (!isset($_SERVER['argv'])) {
            $_SERVER['argv'] = ['test'];
        }
    }

    public function tearDown(): void
    {
        unset($_SERVER['argv']);
    }

    public function testVersion()
    {
        $this->parser = new InterceptingParser([
            'usage'   => Horde_Argv_Option::SUPPRESS_USAGE,
            'version' => "%prog 0.1"]);
        $saveArgv = $_SERVER['argv'];
        try {
            $_SERVER['argv'][0] = __DIR__ . '/foo/bar';
            $this->assertOutput(["--version"], "bar 0.1\n");
        } catch (Exception $e) {
            $_SERVER['argv'] = $saveArgv;
            throw $e;
        }

        $_SERVER['argv'] = $saveArgv;
    }

    public function testNoVersion()
    {
        $this->parser = new InterceptingParser(['usage' => Horde_Argv_Option::SUPPRESS_USAGE]);
        $this->assertParseFail(["--version"], "no such option: --version");
    }
}
