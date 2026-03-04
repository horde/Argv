<?php
namespace Horde\Argv\Parser;

/**
 * The ParserBuilder implements the fluent builder pattern.
 *
 * It is technically a usable, mutable parser
 * Eject to an ImmutableParser to seal it against modification
 *
 * @category Horde
 * @package  Argv
 * @author   Ralf Lang <ralf.lang@ralf-lang.de>
 * @license  http://www.horde.org/licenses/bsd BSD
 *
 * @internal
 * @deprecated This class is under development and not ready for production use.
 *             The API is incomplete and may change without notice. Do not use in production code.
 *
 * @todo Fix missing Parser interface reference
 */
class ParserBuilder implements Parser
{
    public function withArgument(ParserArgument $argument)
    {

    }
}