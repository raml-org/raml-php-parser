<?php

namespace Raml\Types;

use DateTime;
use Raml\Type;

/**
 * DateTimeOnlyType class
 *
 * @author Melvin Loos <m.loos@infopact.nl>
 */
class DatetimeOnlyType extends Type
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
        parent::validate($value);

        $d = DateTime::createFromFormat("Y-m-d\TH:i:s", $value);

        if (!$d || $d->format("Y-m-d\TH:i:s") !== $value) {
            $this->errors[] = TypeValidationError::unexpectedValueType($this->getName(), 'datetime-only', $value);
        }
    }
}
