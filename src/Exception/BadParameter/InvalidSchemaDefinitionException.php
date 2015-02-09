<?php

namespace Raml\Exception\BadParameter;

use RuntimeException;
use Raml\Exception\ExceptionInterface;
use Raml\Exception\BadParameterExceptionInterface;

class InvalidSchemaDefinitionException extends RuntimeException implements ExceptionInterface, BadParameterExceptionInterface
{
    public function __construct()
    {
        parent::__construct('Not a valid schema, must be string or instance of SchemaDefinitionInterface');
    }
}
