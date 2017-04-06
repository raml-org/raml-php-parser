<?php

namespace Raml\Type;

use Raml\Type;
use Raml\Exception\InvalidTypeException;

/**
 * NullType class
 *
 * @author Melvin Loos <m.loos@infopact.nl>
 */
class NullType extends Type
{
    /**
    * Create a new NullType from an array of data
    *
    * @param string    $name
    * @param array     $data
    *
    * @return NullType
    */
    public static function createFromArray($name, array $data = [])
    {
        $type = parent::createFromArray($name, $data);

        return $type;
    }

    public function validate($value)
    {
        if (is_null($value) === false) {
            throw new InvalidTypeException(['Value is not null.']);
        }
        return true;
    }
}
