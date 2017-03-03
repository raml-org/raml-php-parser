<?php

namespace Raml\Exception;

use RuntimeException;

/**
 * Thrown when a required property field of type was not found
 */
class MissingRequiredPropertyException extends RuntimeException implements ExceptionInterface
{
}
