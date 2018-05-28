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

    public static function stringPatternMismatch($property, $pattern, $value)
    {
        return new self($property, sprintf(
            'String "%s" did not match pattern /%s/',
            $value,
            $pattern
        ));
    }

    public static function xmlValidationFailed($message)
    {
        return new self($message, 'xml validation');
    }

    public static function jsonValidationFailed($message)
    {
        return new self($message, 'json validation');
    }

    public static function missingRequiredProperty($property)
    {
        return new self($property, sprintf('Missing required property'));
    }

    public static function unexpectedProperty($property)
    {
        return new self($property, sprintf('Unexpected property'));
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
     * @param $property
     * @param $possibleValues
     * @param $actualValue
     * @return TypeValidationError
     */
    public static function unexpectedValue($property, $possibleValues, $actualValue)
    {
        return new self($property, sprintf(
            'Expected any of [%s], got (%s) "%s"',
            implode($possibleValues, ', '),
            gettype($actualValue),
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
     * @param $constraint
     * @param $actualValue
     * @return TypeValidationError
     */
    public static function unexpectedArrayValueType($property, $constraint, $actualValue)
    {
        return new self($property, sprintf(
            'Expected array element type %s, got (%s) "%s"',
            $constraint,
            gettype($actualValue),
            $actualValue
        ));
    }

    /**
     * @param $property
     * @param $minLength
     * @param $actualValue
     * @return TypeValidationError
     */
    public static function stringLengthExceedsMinimum($property, $minLength, $actualValue)
    {
        return new self($property, sprintf(
            'Minimum allowed length: %d, got %d',
            $minLength,
            strlen($actualValue)
        ));
    }

    /**
     * @param $property
     * @param $maxLength
     * @param $actualValue
     * @return TypeValidationError
     */
    public static function stringLengthExceedsMaximum($property, $maxLength, $actualValue)
    {
        return new self($property, sprintf(
            'Maximum allowed length: %d, got %d',
            $maxLength,
            strlen($actualValue)
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
     * @param $maxValue
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

    /**
     * @param string $property
     * @param $min $expected
     * @param $max $expected
     * @param int $actual
     * @return TypeValidationError
     */
    public static function arraySizeValidationFailed($property, $min, $max, $actual)
    {
        return new self($property, sprintf('Allowed array size: between %s and %s, got %s', $min, $max, $actual));
    }

    /**
     * @param string $property
     * @param array $errorsGroupedByTypes
     * @return TypeValidationError
     */
    public static function unionTypeValidationFailed($property, array $errorsGroupedByTypes)
    {
        $errors = [];
        foreach ($errorsGroupedByTypes as $type => $typeErrors) {
            $message = array_reduce($typeErrors, function ($acc, TypeValidationError $error) {
                $acc .= (string) $error . ', ';

                return $acc;
            }, "$type (");


            $errors[] = substr($message, 0, strlen($message) - 2) . ')';
        }

        return new self(
            $property,
            sprintf('Value did not pass validation against any type: %s', implode(', ', $errors))
        );
    }
}
