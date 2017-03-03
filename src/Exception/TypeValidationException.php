<?php

namespace Raml\Exception;

use RuntimeException;

class TypeValidationException extends RuntimeException implements ExceptionInterface
{
    public static function propertyNotFound($missingPropertyName)
    {
        return new self(sprintf('Missing required property %s', $missingPropertyName));
    }

    public static function unexpectedValueType($expectedType, $actualValue)
    {
        return new self(sprintf(
            'Value expected to be %s, got (%s) "%s"',
            $expectedType,
            gettype($actualValue),
            $actualValue
        ));
    }

    public static function missingRequiredProperty($propertyName, $propertyType)
    {
        return new self(sprintf(
            'Required property (%s) "%s" not found',
            $propertyType,
            $propertyName
        ));
    }
}
