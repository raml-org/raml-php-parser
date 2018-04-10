<?php

namespace Raml\Type;

use Raml\Type;
use Raml\Exception\InvalidTypeException;

/**
 * TimeOnlyType class
 */
class TimeOnlyType extends Type
{
    const TYPE_NAME = 'time-only';
    
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

        return $type;
    }

    public function validate($value)
    {
        $format = 'HH:II:SS';
        $d = DateTime::createFromFormat($format, $value);
        if (($d && $d->format($format) === $value) !== false) {
            throw new InvalidTypeException(['property' => $this->name, 'constraint' => sprintf('Value is not conform format: %s.', $format)]);
        }
    }
}
