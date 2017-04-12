<?php

namespace Raml\Type;

use Raml\Type;
use Raml\Exception\InvalidTypeException;

/**
 * DateTimeOnlyType class
 *
 * @author Melvin Loos <m.loos@infopact.nl>
 */
class DateTimeOnlyType extends Type
{
    /**
    * Create a new DateTimeOnlyType from an array of data
    *
    * @param string    $name
    * @param array     $data
    *
    * @return DateTimeOnlyType
    */
    public static function createFromArray($name, array $data = [])
    {
        $type = parent::createFromArray($name, $data);

        return $type;
    }

    public function validate($value)
    {
        $d = DateTime::createFromFormat(DATE_RFC3339, $value);
        if (($d && $d->format(DATE_RFC3339) === $value) === false) {
            throw new InvalidTypeException(['property' => $this->name, 'constraint' => 'Value is not a datetime-only.']);
        }
        return true;
    }
}
