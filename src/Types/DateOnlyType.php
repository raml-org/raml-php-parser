<?php

namespace Raml\Types;

use Raml\Type;

/**
 * DateOnlyType class
 *
 * @author Melvin Loos <m.loos@infopact.nl>
 */
class DateOnlyType extends Type
{
    /**
     * @var string
     */
    public const FORMAT = 'Y-m-d';

    /**
    * Create a new DateOnlyType from an array of data
    *
    * @param string    $name
    *
    * @return DateOnlyType
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
            $this->errors[] = TypeValidationError::unexpectedValueType($this->getName(), 'date-only', $value);
        }
    }
}
