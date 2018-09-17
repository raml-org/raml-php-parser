<?php

namespace Raml\Types;

use DateTime;
use Raml\Type;

/**
 * @author Melvin Loos <m.loos@infopact.nl>
 */
class DatetimeOnlyType extends Type
{
    const FORMAT = "Y-m-d\TH:i:s";

    /**
    * Create a new DateTimeOnlyType from an array of data
    *
    * @param string    $name
    * @param array     $data
    *
    * @return self
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
            $this->errors[] = TypeValidationError::unexpectedValueType($this->getName(), 'datetime-only', $value);
        }
    }
}
