<?php

namespace Raml\Types;

class TypeValidationError
{
    /**
     * @var string
     */
    private $property;

    /**
     * @var string
     */
    private $constraint;

    public function __construct($property, $constraint)
    {
        $this->property = $property;
        $this->constraint = $constraint;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('%s (%s)', $this->property, $this->constraint);
    }

    public static function missingRequiredProperty($property)
    {
        return new self($property, sprintf('Missing required property'));
    }

    public static function isNotMultipleOf($property, $multiplicator, $actualValue)
    {
        return new self($property, sprintf(
            'Minimum allowed value: %s, got %s',
            $multiplicator,
            $actualValue
        ));
    }

    /**
     * @param $constraint
     * @param $actualValue
     * @return TypeValidationError
     */
    public static function unexpectedValueType($property, $constraint, $actualValue)
    {
        return new self($property, sprintf(
            'Expected %s, got (%s) "%s"',
            $constraint,
            gettype($actualValue),
            $actualValue
        ));
    }

    /**
     * @param $property
     * @param $minValue
     * @param $actualValue
     * @return TypeValidationError
     */
    public static function valueExceedsMinimum($property, $minValue, $actualValue)
    {
        return new self($property, sprintf(
            'Minimum allowed value: %s, got %s',
            $minValue,
            $actualValue
        ));
    }

    /**
     * @param $property
     * @param $minValue
     * @param $actualValue
     * @return TypeValidationError
     */
    public static function valueExceedsMaximum($property, $maxValue, $actualValue)
    {
        return new self($property, sprintf(
            'Maximum allowed value: %s, got %s',
            $maxValue,
            $actualValue
        ));
    }
}
