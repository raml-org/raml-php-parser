<?php

namespace Raml\Types;

use DateTime;
use Raml\Type;

/**
 * DateTimeType type class
 */
class DateTimeType extends Type
{
    /**
     * DateTime format to use
     *
     * @var string
     **/
    private $format;

    /**
     * Create a new DateTimeType from an array of data
     *
     * @param string    $name
     * @param array     $data
     *
     * @return DateTimeType
     */
    public static function createFromArray($name, array $data = [])
    {
        $type = parent::createFromArray($name, $data);

        foreach ($data as $key => $value) {
            switch ($key) {
                case 'format':
                    $type->setFormat($value);
                    break;
            }
        }

        return $type;
    }

    /**
     * Get the value of Format
     *
     * @return mixed
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Set the value of Format
     *
     * @param mixed $format
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

        $format = $this->format ?: DATE_RFC3339;
        $d = DateTime::createFromFormat($format, $value);

        if ($d && $d->format($format) !== $value) {
            $this->errors[] = TypeValidationError::unexpectedValueType($this->getName(), 'datetime', $value);
        }
    }
}
