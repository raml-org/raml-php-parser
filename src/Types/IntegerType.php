<?php

namespace Raml\Types;

/**
 * IntegerType class
 * 
 * @author Melvin Loos <m.loos@infopact.nl>
 */
class IntegerType extends NumberType
{
    /**
     * A numeric instance is valid against "multipleOf" if the result of dividing the instance by this keyword's value is an integer.
     *
     * @var int
     */
    protected $multipleOf = null;

    /**
    * Create a new IntegerType from an array of data
    *
    * @param string    $name
    * @param array     $data
    *
    * @return IntegerType
    */
    public static function createFromArray($name, array $data = [])
    {
        $type = parent::createFromArray($name, $data);
        if (!$type->getFormat()) {
            $type->setFormat('int');
        }

        return $type;
    }
}
