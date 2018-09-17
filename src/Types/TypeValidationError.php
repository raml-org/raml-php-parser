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

    /**
     * @param string $property
     * @param string $pattern
     * @param string $value
     * @return self
     */
    public static function stringPatternMismatch($property, $pattern, $value)
    {
        return new self($property, sprintf(
            'String "%s" did not match pattern /%s/',
            $value,
            $pattern
        ));
    }

    /**
     * @param string $message
     * @return self
     */
    public static function xmlValidationFailed($message)
    {
        return new self($message, 'xml validation');
    }

    /**
     * @param string $message
     * @return self
     */
    public static function jsonValidationFailed($message)
    {
        return new self($message, 'json validation');
    }

    /**
     * @param string $property
     * @return self
     */
    public static function missingRequiredProperty($property)
    {
        return new self($property, sprintf('Missing required property'));
    }

    /**
     * @param string $property
     * @return self
     */
    public static function unexpectedProperty($property)
    {
        return new self($property, sprintf('Unexpected property'));
    }

    /**
     * @param string $property
     * @param int|float $multiplier
     * @param int|float $actualValue
     * @return self
     */
    public static function isNotMultipleOf($property, $multiplier, $actualValue)
    {
        return new self($property, sprintf(
            '%s is not multiple of %ss',
            $actualValue,
            $multiplier
        ));
    }

    /**
     * @param string $property
     * @param string[] $possibleValues
     * @param mixed $actualValue
     * @return self
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
     * @param string $property
     * @param mixed $constraint
     * @param mixed $actualValue
     * @return self
     */
    public static function unexpectedValueType($property, $constraint, $actualValue)
    {
        $value = is_array($actualValue) ? json_encode($actualValue) : (string) $actualValue;

        return new self($property, sprintf(
            'Expected %s, got (%s) "%s"',
            $constraint,
            gettype($actualValue),
            $value
        ));
    }

    /**
     * @param string $property
     * @param mixed $constraint
     * @param mixed $actualValue
     * @return self
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
     * @param string $property
     * @param int $minLength
     * @param string $actualValue
     * @return self
     */
    public static function stringLengthExceedsMinimum($property, $minLength, $actualValue)
    {
        return new self($property, sprintf(
            'Minimum allowed length: %d, got %d',
            $minLength,
            mb_strlen($actualValue)
        ));
    }

    /**
     * @param string $property
     * @param int $maxLength
     * @param string $actualValue
     * @return self
     */
    public static function stringLengthExceedsMaximum($property, $maxLength, $actualValue)
    {
        return new self($property, sprintf(
            'Maximum allowed length: %d, got %d',
            $maxLength,
            mb_strlen($actualValue)
        ));
    }

    /**
     * @param string $property
     * @param mixed $minValue
     * @param mixed $actualValue
     * @return self
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
     * @param string $property
     * @param mixed $maxValue
     * @param mixed $actualValue
     * @return self
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
     * @param int $min
     * @param int $max
     * @param int $actualSize
     * @return self
     */
    public static function arraySizeValidationFailed($property, $min, $max, $actualSize)
    {
        return new self($property, sprintf('Allowed array size: between %s and %s, got %s', $min, $max, $actualSize));
    }

    /**
     * @param string $property
     * @param array $errorsGroupedByTypes
     * @return self
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
