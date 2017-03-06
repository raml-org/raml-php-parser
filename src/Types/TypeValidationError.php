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
}
