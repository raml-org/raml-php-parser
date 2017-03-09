<?php

namespace Raml\Exception;

use LibXMLError;
use RuntimeException;

class InvalidXmlException extends RuntimeException implements ExceptionInterface
{
    /**
     * @var LibXMLError[]
     */
    protected $errors;

    public function __construct(array $errors)
    {
        $this->errors = $errors;

        parent::__construct('Invalid Xml');
    }

    /**
     * @return string
     */
    public function getErrorsAsString()
    {
        $errors = [];
        foreach ($this->errors as $error) {
            $errors .= sprintf('%s (%s)', $error->message, $error->code);
        }

        return implode('; ', $errors);
    }
}
