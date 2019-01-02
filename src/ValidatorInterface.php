<?php

namespace Raml;

use Raml\Types\TypeValidationError;

interface ValidatorInterface
{
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
