<?php

namespace Raml\Exception;

use RuntimeException;

class InvalidJsonException extends RuntimeException
{
    protected $errors;

    public function __construct(array $errors)
    {
        $this->errors = $errors;

        parent::__construct('Invalid JSON.');
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
