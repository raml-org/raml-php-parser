<?php

namespace Raml\Type;

use Raml\Type;
use Raml\Exception\InvalidTypeException;

/**
 * BooleanType class
 *
 * @author Melvin Loos <m.loos@infopact.nl>
 */
class BooleanType extends Type
{
    /**
    * Create a new BooleanType from an array of data
    *
    * @param string    $name
    * @param array     $data
    *
    * @return BooleanType
    */
    public static function createFromArray($name, array $data = [])
    {
        $type = parent::createFromArray($name, $data);

        return $type;
    }

    public function validate($value)
    {
        if (!is_bool($value)) {
            throw new InvalidTypeException(['Value is not a boolean.']);
        }
        return true;
    }
}
