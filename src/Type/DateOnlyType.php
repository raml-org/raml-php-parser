<?php

namespace Raml\Type;

use DateTime;
use Raml\Type;
use Raml\Exception\InvalidTypeException;

/**
 * DateOnlyType class
 * 
 * @author Melvin Loos <m.loos@infopact.nl>
 */
class DateOnlyType extends Type
{
    /**
    * Create a new DateOnlyType from an array of data
    *
    * @param string    $name
    * @param array     $data
    *
    * @return DateOnlyType
    */
    public static function createFromArray($name, array $data = [])
    {
        $type = parent::createFromArray($name, $data);

        return $type;
    }

    public function validate($value)
    {
        if (!is_string($value)) {
            throw new InvalidTypeException(['property' => $this->name, 'constraint' => 'Value is not a date-only string.']);
        }
        $format = 'Y-m-d';
        $d = DateTime::createFromFormat($format, $value);
        if (($d && $d->format($format) === $value) === false) {
            throw new InvalidTypeException(['property' => $this->name, 'constraint' => sprintf('Value is not conform format: %s.', $format)]);
        }
        return true;
    }
}
