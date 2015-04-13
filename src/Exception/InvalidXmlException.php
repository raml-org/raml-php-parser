<?php

namespace Raml\Exception;

use RuntimeException;

class InvalidXmlException extends RuntimeException implements ExceptionInterface
{
    protected $errors;

    public function __construct(array $errors)
    {
        $this->errors = $errors;

        parent::__construct('Invalid Xml.');
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
