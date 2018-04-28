<?php

namespace Raml\Exception;

use RuntimeException;

class InvalidSchemaFormatException extends RuntimeException implements ExceptionInterface
{
    public function __construct()
    {
        parent::__construct('Invalid schema format. It must be valid JSON or XML');
    }
}
