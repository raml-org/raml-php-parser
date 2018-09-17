<?php

namespace Raml;

use Raml\Types\TypeValidationError;

interface ValidatorInterface
{
    /**
     * @param mixed $value
     */
    public function validate($value);

    /**
     * @return TypeValidationError[]
     */
    public function getErrors();

    /**
     * @return bool
     */
    public function isValid();
}
