<?php

namespace Raml\Exception;

use RuntimeException;

class InvalidSchemaException extends RuntimeException implements ExceptionInterface
{
    protected $errors;

    public function __construct(array $errors)
    {
        $this->errors = $errors;

        parent::__construct('Invalid Schema.');
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
