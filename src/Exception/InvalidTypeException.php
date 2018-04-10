<?php

namespace Raml\Exception;

use RuntimeException;

class InvalidTypeException extends RuntimeException implements ExceptionInterface
{
    /**
     * Validation errors
     *
     * @var array
     **/
    protected $errors = [];

    public function __construct(array $error, $previous = null)
    {
        // check if errors is correctly structured
        if (!isset($error['property']) || !isset($error['constraint'])) {
            throw new RuntimeException('Errors parameter is missing required elements "property" and/or "constraint.');
        }
        if ($previous !== null && $previous instanceof Self) {
            $this->errors = $previous->getErrors();
        }
        $this->errors[] = $error;

        parent::__construct('Type does not validate.', 500, $previous);
    }

    /**
     * Returns the collected validation errors
     *
     * @return array Returns array of arrays [['property' => 'propertyname', 'constraint' => 'constraint description']].
     */
    public function getErrors()
    {
        return $this->errors;
    }

    public function setErrors(array $errors) 
    {
        $this->errors = $errors;
    }
}
