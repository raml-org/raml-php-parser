<?php

namespace Raml\Exception\BadParameter;

use RuntimeException;
use Raml\Exception\ExceptionInterface;
use Raml\Exception\BadParameterExceptionInterface;

class InvalidProtocolException extends RuntimeException implements ExceptionInterface, BadParameterExceptionInterface
{
    public function __construct($protocol)
    {
        parent::__construct(sprintf('"%s" is not a valid protocol.', $protocol));
    }
}
