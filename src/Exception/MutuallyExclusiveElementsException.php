<?php

namespace Raml\Exception;

class MutuallyExclusiveElementsException extends RamlParserException
{
    public function __construct()
    {
        parent::__construct('API definition can only contain one of "types" or "schemas" element.');
    }
}
