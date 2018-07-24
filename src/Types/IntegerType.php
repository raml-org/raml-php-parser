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
        assert($type instanceof self);
        if (!$type->getFormat()) {
            $type->setFormat('int');
        }

        return $type;
    }
}
