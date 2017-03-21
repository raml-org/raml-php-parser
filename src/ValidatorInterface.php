<?php

namespace Raml;

use Raml\Types\TypeValidationError;

interface ValidatorInterface
{
    /**
     * @param $value
     */
    public function validate($value);

    /**
     * @return TypeValidationError[]
     */
    public function getErrors();

    /**
     * @return boolean
     */
    public function isValid();
}
