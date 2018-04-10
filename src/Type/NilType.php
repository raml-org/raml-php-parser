<?php

namespace Raml\Type;

use Raml\Type;
use Raml\Exception\InvalidTypeException;

/**
 * NilType class
 *
 * @author Melvin Loos <m.loos@infopact.nl>
 */
class NilType extends Type
{
    const TYPE_NAME = 'nil';
    
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
            throw new InvalidTypeException(['property' => $this->name, 'constraint' => 'Value is not null.']);
        }
        return true;
    }
}
