<?php

namespace Raml\Types;

use DateTime;
use Raml\Type;

/**
 * DatetimeType type class
 */
class DatetimeType extends Type
{
    /**
     * @var string
     */
    const DEFAULT_FORMAT = DATE_RFC3339;

    /**
     * DateTime format to use
     *
     * @var string
     */
    private $format;

    /**
     * Create a new DatetimeType from an array of data
     *
     * @param string $name
     * @return DatetimeType
     */
    public static function createFromArray($name, array $data = [])
    {
        $type = parent::createFromArray($name, $data);
        \assert($type instanceof self);

        foreach ($data as $key => $value) {
            if ($key === 'format') {
                $type->setFormat($value);
            }
        }

        return $type;
    }

    /**
     * Get the value of Format
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Set the value of Format
     *
     * @param string $format
     *
     * @return self
     */
    public function setFormat($format)
    {
        $this->format = $format;

        return $this;
    }

    public function validate($value)
    {
        parent::validate($value);

        $format = $this->format ?: self::DEFAULT_FORMAT;
        $d = \DateTimeImmutable::createFromFormat($format, $value);

        if (!$d || $d->format($format) !== $value) {
            $this->errors[] = TypeValidationError::unexpectedValueType(
                $this->getName(),
                'datetime with format ' . $this->format,
                $value
            );
        }
    }
}
