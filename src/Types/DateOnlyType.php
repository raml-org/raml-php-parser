<?php

namespace Raml\Types;

use DateTime;
use Raml\Type;

/**
 * DateOnlyType class
 *
 * @author Melvin Loos <m.loos@infopact.nl>
 */
class DateOnlyType extends Type
{
    const FORMAT = 'Y-m-d';

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
        assert($type instanceof self);

        return $type;
    }

    public function validate($value)
    {
        parent::validate($value);

        $d = DateTime::createFromFormat(self::FORMAT, $value);

        if (!$d || $d->format(self::FORMAT) !== $value) {
            $this->errors[] = TypeValidationError::unexpectedValueType($this->getName(), 'date-only', $value);
        }
    }
}
