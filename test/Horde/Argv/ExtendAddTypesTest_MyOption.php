<?php


class ExtendAddTypesTest_MyOption extends Horde_Argv_Option
{
    public $TYPES = array('string', 'int', 'long', 'float', 'complex', 'choice', 'file');

    public $TYPE_CHECKER = array("int"    => 'checkBuiltin',
                     "long"   => 'checkBuiltin',
                     "float"  => 'checkBuiltin',
                     "complex"=> 'checkBuiltin',
                     "choice" => 'checkChoice',
                     'file' => 'checkFile',
    );

    public function checkFile($opt, $value)
    {
        if (!file_exists($value)) {
            throw new Horde_Argv_OptionValueException(sprintf("%s: file does not exist", $value));
        } elseif (!is_file($value)) {
            throw new Horde_Argv_OptionValueException(sprintf("%s: not a regular file", $value));
        }
        return $value;
    }

}

