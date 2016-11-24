<?php

namespace Raml\Exception\BadParameter;

use Raml\Exception\BadParameterExceptionInterface;
use Raml\Exception\ExceptionInterface;
use RuntimeException;

class InvalidBaseUrlException  extends RuntimeException implements ExceptionInterface, BadParameterExceptionInterface
{
    public function __construct($baseUrl)
    {
        parent::__construct(sprintf('"%s" is not a valid url.', $baseUrl));
    }
}
