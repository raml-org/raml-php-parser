<?php

namespace Raml\Exception;

use RuntimeException;

class InvalidJsonException extends RuntimeException implements ExceptionInterface
{
    protected $errorCode;

    public function __construct($errorCode)
    {
        $this->errorCode = $errorCode;

        parent::__construct('Invalid JSON.');
    }

    public function getErrorCode()
    {
        return $this->errorCode;
    }
}
