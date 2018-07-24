<?php

namespace Raml\Types;

use DateTime;
use Raml\Type;

/**
 * TimeOnlyType class
 */
class TimeOnlyType extends Type
{
    /**
     * Create a new TimeOnlyType from an array of data
     *
     * @param string    $name
     * @param array     $data
     *
     * @return TimeOnlyType
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

        $d = DateTime::createFromFormat('HH:II:SS', $value);

        if ($d && $d->format('HH:II:SS') !== $value) {
            $this->errors[] = TypeValidationError::unexpectedValueType($this->getName(), 'HH:II:SS', $value);
        }
    }
}
