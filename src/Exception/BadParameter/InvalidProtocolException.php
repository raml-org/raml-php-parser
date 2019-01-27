<?php

namespace Raml\Exception\BadParameter;

use Raml\Exception\BadParameterExceptionInterface;
use Raml\Exception\ExceptionInterface;

class InvalidProtocolException extends \RuntimeException implements ExceptionInterface, BadParameterExceptionInterface
{
    /**
     * @param string $protocol
     */
    public function __construct($protocol)
    {
        parent::__construct(\sprintf('"%s" is not a valid protocol.', $protocol));
    }
}
