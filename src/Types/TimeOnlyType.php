<?php

namespace Raml\Types;

use Raml\Type;

/**
 * TimeOnlyType class
 */
class TimeOnlyType extends Type
{
    /**
     * @var string
     */
    public const FORMAT = 'H:i:s';

    /**
     * Create a new TimeOnlyType from an array of data
     *
     * @param string $name
     * @return TimeOnlyType
     */
    public static function createFromArray($name, array $data = [])
    {
        $type = parent::createFromArray($name, $data);
        \assert($type instanceof self);

        return $type;
    }

    public function validate($value): void
    {
        parent::validate($value);

        $d = \DateTimeImmutable::createFromFormat(self::FORMAT, $value);

        if (!$d || $d->format(self::FORMAT) !== $value) {
            $this->errors[] = TypeValidationError::unexpectedValueType($this->getName(), 'time-only', $value);
        }
    }
}
