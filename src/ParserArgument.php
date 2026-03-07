<?php

namespace Horde\Argv\Parser;

/**
 * Generic interface of command line arguments
 *
 * @category Horde
 * @package  Argv
 * @author   Ralf Lang <ralf.lang@ralf-lang.de>
 * @license  http://www.horde.org/licenses/bsd BSD
 *
 * @internal
 * @deprecated This interface is under development and not ready for production use.
 *             The API is incomplete and may change without notice. Do not use in production code.
 */
interface ParserArgument
{
    public function getNames();

}
