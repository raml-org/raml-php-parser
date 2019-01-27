<?php

namespace Raml\Exception\BadParameter;

use Raml\Exception\BadParameterExceptionInterface;
use Raml\Exception\ExceptionInterface;

class InvalidSchemaDefinitionException extends \RuntimeException implements ExceptionInterface, BadParameterExceptionInterface
{
    public function __construct()
    {
        parent::__construct('Not a valid schema, must be string or instance of SchemaDefinitionInterface.');
    }
}
