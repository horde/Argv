<?php
namespace Horde\Argv\Parser;

/**
 * Generic interface of an option
 */
interface Option
{
    /**
     * Return a list of all names the option has
     */
    public function getNames(): iterable;
}