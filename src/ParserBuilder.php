<?php
namespace Horde\Argv\Parser;

/**
 * The ParserBuilder implements the fluent builder pattern.
 *
 * It is technically a usable, mutable parser
 * Eject to an ImmutableParser to seal it against modification
 *
 */
class ParserBuilder implements Parser
{
    public function withArgument(ParserArgument $argument)
    {

    }
}