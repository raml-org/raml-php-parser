<?php

namespace Raml\Exception;

use RuntimeException;

class InvalidXmlException extends RuntimeException implements ExceptionInterface
{
    protected $errorCode;

    public function __construct($errorCode)
    {
        $this->errorCode = $errorCode;

        parent::__construct('Invalid XML.');
    }

    public function getErrorCode()
    {
        return $this->errorCode;
    }
}
